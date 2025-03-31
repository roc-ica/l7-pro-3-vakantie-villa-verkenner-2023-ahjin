<?php
session_start();
session_destroy();

header('Location: ../protected/login.php');
exit();

?> 