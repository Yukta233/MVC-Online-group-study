<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StudyGroup;
use App\Models\ChatMessage;
use App\Models\Resource;
use App\Models\StudyNote;
use App\Models\StudySession;
use App\Models\StudyTask;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Mock Users
        $alex = User::create([
            'name' => 'Alex Johnson',
            'email' => 'alex@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $elena = User::create([
            'name' => 'Elena Rostova',
            'email' => 'elena@example.com',
            'password' => Hash::make('password'),
            'role' => 'moderator',
        ]);

        $marcus = User::create([
            'name' => 'Marcus Aurelius',
            'email' => 'marcus@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // 2. Create Study Groups
        $group1 = StudyGroup::create([
            'name' => 'Algorithms & Data Structures',
            'description' => 'A dedicated room for deep-diving into graphs, trees, sorting algorithms, dynamic programming, and solving complex algorithmic challenges together.',
            'subject' => 'Computer Science',
            'access_code' => 'ADS-2026',
            'owner_id' => $alex->id,
        ]);

        $group2 = StudyGroup::create([
            'name' => 'Organic Chemistry Study Circle',
            'description' => 'Focusing on reaction mechanisms, synthesis pathways, stereochemistry, and spectroscopy. Let us master functional groups together!',
            'subject' => 'Chemistry',
            'access_code' => 'CHEM-4U',
            'owner_id' => $elena->id,
        ]);

        // 3. Attach Members to Groups
        $group1->members()->attach([$alex->id, $elena->id, $marcus->id]);
        $group2->members()->attach([$alex->id, $elena->id, $marcus->id]);

        // 4. Create Chat Messages for Group 1
        ChatMessage::create([
            'study_group_id' => $group1->id,
            'user_id' => $alex->id,
            'message' => 'Hey everyone! Welcome to the Algorithms workspace. Let\'s tackle graph traversals and search algorithms today.',
        ]);

        ChatMessage::create([
            'study_group_id' => $group1->id,
            'user_id' => $elena->id,
            'message' => 'Hi Alex! That sounds perfect. I\'ve been struggling with Dijkstra\'s shortest path algorithm lately.',
        ]);

        ChatMessage::create([
            'study_group_id' => $group1->id,
            'user_id' => $marcus->id,
            'message' => 'Count me in! I found a great visualizer online that really explains tree rotations and BFS/DFS beautifully. I\'ll share the link in the resources tab!',
        ]);

        ChatMessage::create([
            'study_group_id' => $group1->id,
            'user_id' => $alex->id,
            'message' => 'Awesome Marcus! Let\'s also schedule a live coding study call on Google Meet later this afternoon.',
        ]);

        // Chat Messages for Group 2
        ChatMessage::create([
            'study_group_id' => $group2->id,
            'user_id' => $elena->id,
            'message' => 'Welcome to the Organic Chem group! We have an exam coming up on Nucleophilic Substitution (SN1 vs SN2). Let\'s share resources here.',
        ]);

        ChatMessage::create([
            'study_group_id' => $group2->id,
            'user_id' => $marcus->id,
            'message' => 'Thanks Elena! Organic synthesis mechanisms are challenging. I will add some study guides on nucleophile strength.',
        ]);

        // 5. Create Resource Shares
        Resource::create([
            'study_group_id' => $group1->id,
            'user_id' => $marcus->id,
            'title' => 'VisuAlgo - Data Structure Visualizer',
            'url' => 'https://visualgo.net',
            'resource_type' => 'link',
            'description' => 'Interactive visualizations for sorting algorithms, trees, graph searching, and heap operations.',
        ]);

        Resource::create([
            'study_group_id' => $group1->id,
            'user_id' => $alex->id,
            'title' => 'CLRS Introduction to Algorithms Companion',
            'url' => 'https://mitpress.mit.edu/9780262046305/introduction-to-algorithms/',
            'resource_type' => 'link',
            'description' => 'Official MIT press companion site for the 4th edition CLRS textbook containing animations and pseudocode.',
        ]);

        Resource::create([
            'study_group_id' => $group2->id,
            'user_id' => $elena->id,
            'title' => 'Mastering SN1 vs SN2 Mechanisms Guide',
            'url' => 'https://www.masterorganicchemistry.com/2012/08/08/comparing-the-sn1-and-sn2-reactions/',
            'resource_type' => 'link',
            'description' => 'A complete comparative breakdown chart detailing solvent, substrate, nucleophile, and stereochemical impacts on SN1 and SN2 pathways.',
        ]);

        // 6. Create Group Notes
        StudyNote::create([
            'study_group_id' => $group1->id,
            'content' => "# Graph Algorithms & Shortest Paths Study Guide\n\n## 1. Breadth-First Search (BFS)\n- **Data Structure**: Queue (FIFO)\n- **Time Complexity**: O(V + E)\n- **Use Case**: Finds the shortest path in an unweighted graph.\n\n## 2. Depth-First Search (DFS)\n- **Data Structure**: Stack (LIFO / Recursion)\n- **Time Complexity**: O(V + E)\n- **Use Case**: Topological sort, cycle detection, pathfinding.\n\n## 3. Dijkstra's Algorithm\n- **Data Structure**: Min-Priority Queue / Binary Heap\n- **Time Complexity**: O((V + E) log V)\n- **Condition**: Only works on graphs with non-negative edge weights.\n- **Steps**:\n  1. Set distance to source = 0, and all other vertices = infinity.\n  2. Insert source into priority queue.\n  3. Pop node `u` with minimum distance. For each neighbor `v` of `u`, relax the edge if `dist[u] + weight(u,v) < dist[v]`.\n  4. Repeat until priority queue is empty.",
            'last_edited_by' => $alex->id,
        ]);

        StudyNote::create([
            'study_group_id' => $group2->id,
            'content' => "# Organic Chemistry: Core Mechanisms Summary\n\n## SN1 vs SN2 Comparison Sheet\n\n### SN2 Reaction (Substitution Nucleophilic Bimolecular)\n- **Kinetics**: 2nd order (Rate = k[Substrate][Nucleophile])\n- **Mechanism**: Single step (concerted back-side attack)\n- **Stereochemistry**: Inversion of configuration (Walden inversion)\n- **Substrate Preference**: Methyl > Primary > Secondary >> Tertiary (hindered)\n- **Solvent**: Polar Aprotic (e.g., DMSO, Acetone)\n\n### SN1 Reaction (Substitution Nucleophilic Unimolecular)\n- **Kinetics**: 1st order (Rate = k[Substrate])\n- **Mechanism**: Two steps (carbocation intermediate formation)\n- **Stereochemistry**: Racemization (mix of inversion and retention)\n- **Substrate Preference**: Tertiary > Secondary >> Primary (due to carbocation stability)\n- **Solvent**: Polar Protic (e.g., Water, Alcohols)",
            'last_edited_by' => $elena->id,
        ]);

        // 7. Create Study Sessions
        StudySession::create([
            'study_group_id' => $group1->id,
            'title' => 'Graph Traversals & Dijkstra Coding Session',
            'description' => 'Let us work through implementing Dijkstra\'s algorithm in Python and Java, and trace vertex relaxation on a whiteboard.',
            'scheduled_at' => now()->addHours(2),
            'duration_minutes' => 90,
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
        ]);

        StudySession::create([
            'study_group_id' => $group1->id,
            'title' => 'Dynamic Programming (DP) Core Concepts',
            'description' => 'Demystifying Memoization vs Tabulation using Fibonacci and Knapsack problems. Bring your questions!',
            'scheduled_at' => now()->addDays(2)->setHour(14)->setMinute(0)->setSecond(0),
            'duration_minutes' => 60,
            'meeting_link' => 'https://meet.google.com/xyz-uvwx-123',
        ]);

        StudySession::create([
            'study_group_id' => $group2->id,
            'title' => 'Reaction Mechanisms Prep Session',
            'description' => 'Let\'s review carbocation rearrangements and elimination reactions (E1/E2).',
            'scheduled_at' => now()->addDays(1)->setHour(10)->setMinute(0)->setSecond(0),
            'duration_minutes' => 120,
            'meeting_link' => 'https://meet.google.com/pqr-stuv-456',
        ]);

        // 8. Create Kanban Tasks
        StudyTask::create([
            'study_group_id' => $group1->id,
            'title' => 'Review Binary Search Tree (BST) operations',
            'description' => 'Make sure everyone understands Insertion, Search, and deletion algorithm in BST.',
            'status' => 'completed',
            'assignee_id' => $elena->id,
        ]);

        StudyTask::create([
            'study_group_id' => $group1->id,
            'title' => 'Solve 5 Graph Problems on LeetCode',
            'description' => 'Practice BFS/DFS problems (e.g., Number of Islands, Clone Graph).',
            'status' => 'in_progress',
            'assignee_id' => $alex->id,
        ]);

        StudyTask::create([
            'study_group_id' => $group1->id,
            'title' => 'Write pseudo-code for Bellman-Ford shortest path',
            'description' => 'Draft a clean summary explaining negative weight cycle detection using Bellman-Ford.',
            'status' => 'todo',
            'assignee_id' => $marcus->id,
        ]);

        StudyTask::create([
            'study_group_id' => $group2->id,
            'title' => 'Memorize nucleophile strength ordering list',
            'description' => 'Study differences in strong vs weak nucleophiles in protic vs aprotic solvents.',
            'status' => 'completed',
            'assignee_id' => $marcus->id,
        ]);

        StudyTask::create([
            'study_group_id' => $group2->id,
            'title' => 'Complete chapter 8 end-of-section exercises',
            'description' => 'Solve practice problems 8.1 to 8.24 regarding alcohol oxidation pathways.',
            'status' => 'in_progress',
            'assignee_id' => $elena->id,
        ]);
    }
}
