<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\StudyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceController extends Controller
{
    public function store(Request $request, $groupId)
    {
        $group = StudyGroup::findOrFail($groupId);

        // Ensure membership
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|url|required_without:file|max:500',
            'file' => 'nullable|file|max:20480', // limit to 20MB
            'resource_type' => 'required|in:link,document',
            'description' => 'nullable|string|max:1000',
        ]);

        $url = $request->url;
        $type = $request->resource_type;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $dirPath = public_path('uploads/resources');
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
            $fileName = 'resource_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dirPath, $fileName);
            $url = asset('uploads/resources/' . $fileName);
            $type = 'document';
        }

        Resource::create([
            'study_group_id' => $group->id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'url' => $url,
            'resource_type' => $type,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Resource shared successfully!');
    }

    public function destroy($id)
    {
        $resource = Resource::with('studyGroup')->findOrFail($id);

        // Ensure user is either the owner of the resource or the group
        if ($resource->user_id !== Auth::id() && $resource->studyGroup->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $resource->delete();

        return back()->with('success', 'Resource deleted successfully!');
    }

    public function storeSnapshot(Request $request, $groupId)
    {
        $group = StudyGroup::findOrFail($groupId);

        // Ensure membership
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|string', // base64 encoded image
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $imageData = $request->image;
            // Remove header: data:image/png;base64,
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // png, jpg, etc.

                if (!in_array($type, ['png', 'jpg', 'jpeg'])) {
                    throw new \Exception('Invalid image type');
                }

                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    throw new \Exception('Base64 decode failed');
                }
            } else {
                throw new \Exception('Did not match data URI with image data');
            }

            // Create directories if they don't exist
            $dirPath = public_path('uploads/whiteboards');
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0755, true);
            }

            $fileName = 'whiteboard_' . time() . '_' . uniqid() . '.' . $type;
            $filePath = $dirPath . '/' . $fileName;
            file_put_contents($filePath, $imageData);

            $fileUrl = asset('uploads/whiteboards/' . $fileName);

            $resource = Resource::create([
                'study_group_id' => $group->id,
                'user_id' => Auth::id(),
                'title' => $request->title,
                'url' => $fileUrl,
                'resource_type' => 'document',
                'description' => $request->description ?: 'Snapshot of study room whiteboard.',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Whiteboard snapshot saved to Resource Drive!',
                'resource' => $resource
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save snapshot: ' . $e->getMessage()
            ], 500);
        }
    }
}
