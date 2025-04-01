<?php

require_once __DIR__ . '/class/database.php';

$db = new Database();

$conn = $db->getConnection();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($conn) {
    echo "Connection successful";
} else {
    echo "Connection failed";
}

$db->closeConnection($conn);
?>