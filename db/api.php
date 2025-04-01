<?php

require_once __DIR__ . '/class/database.php';
require_once __DIR__ . '/class/serverlogger.php';
require_once __DIR__ . '/func/api_functions.php';

$apiHelper = new ApiHelper();

// Get the request method
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case "POST":
        // Handle creating a new property
        if (isset($_POST["action"]) && $_POST["action"] === "create") {
            createProperty();
        }
        break;
    case "GET":
        // Handle retrieving properties
        if (isset($_GET["id"])) {
            getProperty($_GET["id"]);
        } else {
            getAllProperties();
        }
        break;
    case "PUT":
        // Handle updating a property
        parse_str(file_get_contents("php://input"), $_PUT);
        updateProperty($_PUT);
        break;
    case "DELETE":
        // Handle deleting a property
        if (isset($_GET["id"])) {
            deleteProperty($_GET["id"]);
        }
        break;
    default:
        // Handle unsupported methods
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

function createProperty() {
    $setupProperty = [
        "name" => $_POST["name"],
        "straat" => $_POST["straat"],
        "postcode" => $_POST["post_c"],
        "kamers" => $_POST["kamers"],
        "badkamers" => $_POST["badkamers"],
        "prijs" => $_POST["prijs"],
        "oppervlakte" => $_POST["oppervlakte"],
    ];
}

function getProperty($id) {
    // Retrieve a single property by ID
    // Implement your logic here
}

function getAllProperties() {
    // Retrieve all properties
    // Implement your logic here
}

function updateProperty($data) {
    // Validate and process the update of a property
    // Implement your logic here
}

function deleteProperty($id) {
    // Validate and process the deletion of a property
    // Implement your logic here
}

function login($username, $password) {
    $db = new Database();
    $conn = $db->getConnection();
    $verifyUser = "SELECT FROM ADMIN WHERE username = :username";
    $stmt = $conn->prepare($verifyUser);
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        if (password_verify($password, $user["password"])) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

?>