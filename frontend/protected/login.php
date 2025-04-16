<?php
// login.php - Admin Login Page
require_once __DIR__ . '/../../db/class/sessions.php';

// Redirect to admin panel if already logged in using SessionManager
if (SessionManager::validateAdminSession()) {
    header('Location: admin.php');
    exit;
}

$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'invalid') {
        $error_message = 'Ongeldige gebruikersnaam of wachtwoord.';
    } elseif ($_GET['error'] === 'dberror') {
        $error_message = 'Databasefout. Probeer het later opnieuw.';
    } elseif ($_GET['error'] === 'missing') {
        $error_message = 'Vul aub gebruikersnaam en wachtwoord in.';
    } elseif ($_GET['error'] === 'auth') {
        $error_message = 'U moet ingelogd zijn om die pagina te bekijken.';
    } elseif ($_GET['error'] === 'server_error') {
        $error_message = 'Er is een serverfout opgetreden. Probeer het opnieuw.';
    }
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Vakantie Villas</title>
    <link rel="stylesheet" href="styles/login.css"> <!-- Create this CSS file -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="../assets/img/logo.png" alt="Vakantie Villas Logo" class="login-logo">
            <h2>Admin Login</h2>
            <p>Log in om het interne systeem te beheren.</p>
            
            <?php if ($error_message): ?>
                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="username">Gebruikersnaam</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Wachtwoord</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Inloggen</button>
            </form>
            <p class="back-link"><a href="../pages/homepage.php">← Terug naar de website</a></p>
        </div>
    </div>
</body>
</html>