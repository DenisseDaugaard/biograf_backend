<?php
require __DIR__ . '/db.php';
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];

function validateID() {
    global $conn;

    // Try GET first
    if (!empty($_GET["id"])) {
        $id = $_GET["id"];
    } else {
        // Then try DELETE body
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data["id"])) {
            http_response_code(400);
            echo json_encode(["message" => "ID missing"]);
            exit;
        }

        $id = $data["id"];
    }

    // Validate numeric
    if (!is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["message" => "ID is malformed"]);
        exit;
    }

    $id = intval($id, 10);

    // Ensure ID exists in DB
    $stmt = $conn->prepare("SELECT * FROM `user-profil` WHERE id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        echo json_encode(["message" => "ID not found"]);
        exit;
    }

    return $id;
}


// DELETE USER
if ($method === 'DELETE') {
   $id = validateID(); // this checks everything and exits if invalid

    $stmt = $conn->prepare("DELETE FROM `user-profil` WHERE id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    http_response_code(200);
    echo json_encode([
        "message" => "User deleted successfully",
        "id" => $id
    ]);
    exit;
}
