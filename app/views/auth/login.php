<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Internal Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
        }
        .google-btn {
            background: white;
            border: 1px solid #ddd;
            padding: 12px 24px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s;
        }
        .google-btn:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transform: translateY(-2px);
        }
        .google-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h2 class="mb-2">🏢 Internal Portal</h2>
            <p class="text-muted">Ticketing & Assets Management</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="text-center">
            <p class="mb-3">Sign in with your Google account</p>
            
            <a href="<?php echo $googleLoginUrl; ?>" class="google-btn w-100">
                <img src="https://www.google.com/favicon.ico" alt="Google" class="google-icon">
                Sign in with Google
            </a>
        </div>

        <div class="text-center mt-4">
            <small class="text-muted">
                New users will be automatically registered<br>
                with Staff role by default
            </small>
        </div>
    </div>
</body>
</html>