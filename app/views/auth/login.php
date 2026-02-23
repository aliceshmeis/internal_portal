<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – LIU Internal Portal</title>
    <link rel="stylesheet" href="/internal_portal/public/css/auth-style-standalone.css">
</head>
<body>

    <!-- TOP BRAND BAR -->
    <div class="brand-bar">
        <div class="brand-bar-inner">
            <img src="/internal_portal/public/images/liulogo.png" alt="LIU Logo" class="brand-icon">
            <img src="/internal_portal/public/images/Logo-Text.png" alt="Lebanese International University" class="brand-logo">
            <div class="brand-divider"></div>
            <span class="brand-subtitle"></span>
        </div>
    </div>

    <!-- PAGE WRAPPER -->
    <div class="auth-page">

        <div class="auth-container">

            <!-- CARD HEADER -->
            <div class="card-header">
                <h1>Staff Access Portal</h1>
                <p>Sign in with your university credentials</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- EMAIL / PASSWORD FORM -->
            <form action="/internal_portal/app/views/auth/login-process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php
                    if (!isset($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                    echo $_SESSION['csrf_token'];
                ?>">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@liu.edu.lb" required autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-password-wrap">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="toggle-pw" onclick="togglePassword()" title="Show/hide password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Sign In</button>
            </form>

            <!-- DIVIDER -->
            <div class="divider"><span>or</span></div>

            <!-- GOOGLE LOGIN -->
            <a href="/internal_portal/app/views/auth/google-login.php" class="btn-google">
                <svg width="18" height="18" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continue with Google
            </a>

            <!-- FOOTER -->
            <p class="auth-footer">
                Having trouble? Contact <a href="mailto:itsupport@liu.edu.lb">IT Support</a>
            </p>

        </div>

        <!-- PAGE FOOTER -->
        <div class="page-footer">
            &copy; <?= date('Y') ?> Lebanese International University. All rights reserved.
        </div>

    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>