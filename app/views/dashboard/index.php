<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /internal_portal/app/views/auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Internal Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .user-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .user-info h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            width: 150px;
        }
        .info-value {
            color: #333;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }
        .badge-admin {
            background: #dc3545;
            color: white;
        }
        .badge-staff {
            background: #28a745;
            color: white;
        }
        .badge-active {
            background: #28a745;
            color: white;
        }
        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-message">
            ✅ <strong>Success!</strong> You are now logged in via Google OAuth!
        </div>

        <h1>Welcome to Internal Portal</h1>
        <p class="subtitle">Ticketing & Assets Management System</p>

        <div class="user-info">
            <h2>Your Profile Information</h2>
            
            <?php if (!empty($_SESSION['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile" class="profile-pic">
            <?php endif; ?>

            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">User ID:</div>
                <div class="info-value"><?php echo htmlspecialchars($_SESSION['user_id']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Role:</div>
                <div class="info-value">
                    <span class="badge <?php echo $_SESSION['role'] === 'Admin' ? 'badge-admin' : 'badge-staff'; ?>">
                        <?php echo htmlspecialchars($_SESSION['role']); ?>
                    </span>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Campus ID:</div>
                <div class="info-value"><?php echo htmlspecialchars($_SESSION['campus_id']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="badge badge-active">Active</span>
                </div>
            </div>
        </div>

        <h2 style="margin-bottom: 15px;">Session Data (Debug)</h2>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto;">
<?php print_r($_SESSION); ?>
        </pre>

        <div style="margin-top: 30px;">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</body>
</html>