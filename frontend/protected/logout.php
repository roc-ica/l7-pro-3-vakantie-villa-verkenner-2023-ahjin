<?php
session_start();
session_destroy();

header('Location: /frontend/protected/login.php');
exit();

?> 