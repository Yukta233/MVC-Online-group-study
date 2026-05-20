<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - CollabSphere</title>
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
            <h2>Welcome Back</h2>
            <p>Enter your credentials to access your study groups</p>
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

        <form action="{{ route('login') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </button>
        </form>
        
        <div class="auth-footer">
            Don't have an account? <a href="{{ route('register') }}">Sign Up Now</a>
            <div style="margin-top: 1.25rem; font-size: 0.78rem; color: var(--text-muted);">
                <strong>Demo Accounts:</strong><br>
                alex@example.com / password<br>
                elena@example.com / password
            </div>
        </div>
    </div>

</body>
</html>
