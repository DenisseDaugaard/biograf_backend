<?php
require __DIR__ . '/db.php';
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];

function validateID(){

    global $conn;
     if (empty($_GET["id"])) {
        http_response_code(400);
        exit;
    }

    $id = $_GET["id"];

    if(!is_numeric($id)){
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(["message" => "ID is malformed"]);
        exit;
    }

    $id = intval($id, 10);

    $stmt = $conn->prepare("SELECT * FROM `user-profil` WHERE id = :id");
    $stmt->bindParam(":id",$id, PDO::PARAM_INT);
    $stmt->execute();
   $results = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!is_array($results
    )){
        http_response_code(404);
         echo json_encode([
        "message" => "ID not found !",
        "id" => $id
    ]);
        exit;
    }

    return $id;
}

// CREATE AN ACCOUNT
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $input = $_POST + $input;


    $name = $input['name'] ?? null;
    $email = $input['email'] ?? null;
    $password = $input['password'] ?? null;
    $phone = $input['phone'] ?? null;
    $address = $input['address'] ?? null;

    if (!$name || !$email || !$password || !$phone || !$address) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO `user-profil` (name, email ,phone, address, password) VALUES (:name, :email, :phone, :address, :password)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':password', $hashedPassword);

    try {
        $stmt->execute();
        echo json_encode(["message" => "User created successfully"]);
    } catch (PDOException $error) {
        http_response_code(500);
        echo json_encode(["error" => $error->getMessage()]);
    }
}


// GET ALL USERS OR SINGLE USER BY ID
if ($method === 'GET'  && empty($_GET["id"])) {
    $stmt = $conn->query("SELECT * FROM `user-profil`");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

         // Add hypermedia controls 
    for($i = 0; $i < count($results); $i++){
        $results[$i]["url"] = "http://http://localhost/biograf-backend/index.php?id=" . $results[$i]["id"];
        unset($results[$i]["id"]);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["results" => $results]);
}




