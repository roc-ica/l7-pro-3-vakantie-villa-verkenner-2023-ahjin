<?php
require_once __DIR__ . '/../../db/class/sessions.php';

// Use SessionManager to destroy the session
SessionManager::destroySession();

// Redirect to the public homepage or login page after logout
header('Location: ../pages/homepage.php'); // Redirect to public homepage
// Or redirect to login: header('Location: login.php?logout=success');
exit;
?> 