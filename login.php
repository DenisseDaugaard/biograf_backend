<?php
require __DIR__ . '/db.php';
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$key = "SUPER_SECRET_KEY_CHANGE_THIS";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $email = $input['email'] ?? null;
    $password = $input['password'] ?? null;

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "Missing email or password"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM `user-profil` WHERE email =:email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid credentials"]);
        exit;
    }

    $payload = [
        "id" => $user['id'],
        "email" => $user['email'],
        "iat" => time(),
        "exp" => time() + 3600
    ];

    $token = JWT::encode($payload, $key, 'HS256');
    $userName =  $user["name"];
    $id =  $user["id"];

    echo json_encode([
        "message" => "Login successful",
        "token" => $token,
        "userName" => $userName,
        "id" => $id,
    ]);
}
