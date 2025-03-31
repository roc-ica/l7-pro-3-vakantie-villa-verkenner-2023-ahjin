<?php
include_once '../../db/class/database.php';
$conn = (new Database())->getConnection();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Villa ID ontbreekt"]);
    exit;
}

$query = "DELETE FROM villas WHERE id = :id";
$stmt = $conn->prepare($query);
$success = $stmt->execute([':id' => $id]);

if ($success) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Fout bij verwijderen van villa"]);
}
?>
