<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Internal Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">🏢 Internal Portal</span>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <?php echo $user['name']; ?> 
                    <span class="badge bg-primary"><?php echo $user['role']; ?></span>
                </span>
                <a href="<?php echo BASE_URL; ?>auth/logout" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <h1>Welcome, <?php echo $user['name']; ?>! 🎉</h1>
                <p class="lead">You're successfully logged in via Google OAuth!</p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">✅ Login System Working!</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th>Name:</th>
                                <td><?php echo $user['name']; ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo $user['email']; ?></td>
                            </tr>
                            <tr>
                                <th>Role:</th>
                                <td><span class="badge bg-primary"><?php echo $user['role']; ?></span></td>
                            </tr>
                            <tr>
                                <th>Campus ID:</th>
                                <td><?php echo $user['campus_id']; ?></td>
                            </tr>
                            <tr>
                                <th>Database Status:</th>
                                <td><?php echo $dbStatus; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <h5>🚀 Next Steps:</h5>
                    <ul class="mb-0">
                        <li>✅ Google OAuth authentication working</li>
                        <li>✅ User stored in database</li>
                        <li>✅ Session handling active</li>
                        <li>✅ Role detection working</li>
                        <li>🔜 Build Ticketing System</li>
                        <li>🔜 Build Assets Management</li>
                        <li>🔜 Build REST APIs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>