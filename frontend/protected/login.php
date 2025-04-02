<?php
session_start();

if (isset($_SESSION['username'])) {
    header('Location: admin.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/styles.css">
    <title>Login - VillaVerkenner</title>
</head>
<body>
    <div class="login-container">
        <h2>Login to VillaVerkenner</h2>
        
        <?php
        // Process any error messages
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $errorMessage = '';
            
            switch ($error) {
                case 'invalid_request':
                    $errorMessage = 'Invalid request method.';
                    break;
                case 'missing_fields':
                    $errorMessage = 'Please fill in all required fields.';
                    break;
                case 'invalid_credentials':
                    $errorMessage = 'Invalid username or password.';
                    break;
                case 'db_connection':
                    $errorMessage = 'Database connection error.';
                    break;
                case 'server_error':
                    $errorMessage = 'An error occurred. Please try again later.';
                    break;
                default:
                    $errorMessage = 'An unknown error occurred.';
            }
            
            echo '<div class="error-message">' . htmlspecialchars($errorMessage) . '</div>';
        }
        ?>
        
        <form action="login_process.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>