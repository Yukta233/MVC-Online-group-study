<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - CollabSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="auth-wrapper">
    
    <div class="auth-card glass-panel">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>CollabSphere</span>
            </div>
            <h2>Create Account</h2>
            <p>Join the student collaboration movement today</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <ul class="validation-errors">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Alex Johnson" value="{{ old('name') }}" required autofocus pattern="[A-Za-z\s]+" title="Name can only contain alphabetic characters and spaces (no numbers or special characters)">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="alex@example.com" value="{{ old('email') }}" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Min. 8 characters" required>
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Repeat password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="{{ route('login') }}">Sign In</a>
        </div>
    </div>

</body>
</html>
