<?php
// This would normally destroy the session, but since we don't have login logic yet,
// we'll just redirect to the admin page
header('Location: /frontend/protected/admin.php');
exit();
?> 