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

// GET ONE FRIEND BY ID AND PURCHSASES hISTORY
if ($method === 'GET' && !empty($_GET["id"])) {
    $id = validateID();

    $sql = "SELECT 
    u.id AS user_id,
    u.name AS user_name,
    u.email AS user_email,
    u.phone AS user_phone,
    u.address AS user_address,
    t.purchse_id AS ticket_user_id,
    t.tickets_movie,
    t.tickets_amount,
    t.tickets_date,
    t.tickets_location,
    t.tickets_order,
    t.tickets_time,
    t.tickets_seats
    FROM `user-profil` AS u
    LEFT JOIN tickets_purchase AS t
        ON u.id = t.purchse_id
    WHERE u.id = :id;";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        http_response_code(404);
        echo json_encode(["message" => "Not found"]);
        exit;
    }

    // Build result
    $user = [
        "id" => $rows[0]['user_id'],
        "name" => $rows[0]['user_name'],
        "email" => $rows[0]['user_email'],
        "phone" => $rows[0]['user_phone'],
        "address" => $rows[0]['user_address'],
        "purchases" => []
    ];

    foreach ($rows as $row) {
        if (!empty($row['ticket_user_id'])) {
            $user['purchases'][] = [
                "tickets_movie" => $row['tickets_movie'],
                "tickets_amount" => $row['tickets_amount'],
                "tickets_location" => $row['tickets_location'],
                "tickets_date"   => $row['tickets_date'],
                "tickets_time"   => $row['tickets_time'],
                "tickets_seats"  => $row['tickets_seats'],
                "tickets_order"  => $row['tickets_order']
            ];
        }
    }

    $user["links"] = [
        "self" => "http://localhost/biograf-backend/index.php?id=" . $user["id"],
        "all" => "http://localhost/biograf-backend/index.php",
        "delete" => "http://localhost/biograf-backend/index.php?id=" . $user["id"]
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($user);
    exit;
}



//POST THE PURCHASE FOR USER
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $purchse_id = $input['purchse_id'] ?? null;
    $tickets_movie = $input['tickets_movie'] ?? null;
    $tickets_amount = $input['tickets_amount'] ?? null;
    $tickets_date = $input['tickets_date'] ?? null;
    $tickets_time = $input['tickets_time'] ?? null;
    $tickets_seats = $input['tickets_seats'] ?? null;
    $tickets_location = $input['tickets_location'] ?? null;
    $tickets_order = $input['tickets_order'] ?? null;

    if (!$purchse_id || !$tickets_movie || !$tickets_amount || !$tickets_date || !$tickets_location 
                     || !$tickets_order || !$tickets_time || !$tickets_seats) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO tickets_purchase (purchse_id, tickets_movie, tickets_amount, tickets_date, tickets_time, tickets_seats, tickets_location, tickets_order) 
    VALUES (:purchse_id, :tickets_movie, :tickets_amount, :tickets_date, :tickets_time, :tickets_seats, :tickets_location, :tickets_order)");
    $stmt->bindParam(':purchse_id', $purchse_id);
    $stmt->bindParam(':tickets_movie', $tickets_movie);
    $stmt->bindParam(':tickets_amount', $tickets_amount);
    $stmt->bindParam(':tickets_date', $tickets_date);
    $stmt->bindParam(':tickets_time', $tickets_time);
    $stmt->bindParam(':tickets_seats', $tickets_seats);
    $stmt->bindParam(':tickets_location', $tickets_location);
    $stmt->bindParam(':tickets_order', $tickets_order);

    try {
        $stmt->execute();
        echo json_encode(["message" => "Purchase recorded successfully"]);
    } catch (PDOException $error) {
        http_response_code(500);
        echo json_encode(["error" => $error->getMessage()]);
    }
}
