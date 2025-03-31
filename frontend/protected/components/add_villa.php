<?php
include_once '../../db/class/database.php';
$conn = (new Database())->getConnection();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['straat'], $data['post_c'], $data['oppervlakte'], $data['prijs'])) {
    echo json_encode(["success" => false, "message" => "Ongeldige invoer"]);
    exit;
}

$query = "INSERT INTO villas (straat, post_c, oppervlakte, prijs) VALUES (:straat, :post_c, :oppervlakte, :prijs)";
$stmt = $conn->prepare($query);
$success = $stmt->execute([
    ':straat' => $data['straat'],
    ':post_c' => $data['post_c'],
    ':oppervlakte' => $data['oppervlakte'],
    ':prijs' => $data['prijs']
]);

if ($success) {
    echo json_encode(["success" => true, "id" => $conn->lastInsertId()]);
} else {
    echo json_encode(["success" => false, "message" => "Fout bij toevoegen van villa"]);
}
?>
