<?php
require_once "../config/db.php";
header("Content-Type: application/json");

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Missing credentials"
    ]);
    exit;
}

$stmt = $dbc->prepare("
    SELECT participant_id, name, email, password
    FROM participants
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: prepare failed"
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {

    if (password_verify($password, $user['password'])) {

        echo json_encode([
            "status" => "success",
            "role" => "participant",
            "data" => [
                "id" => $user['participant_id'],
                "name" => $user['name'],
                "email" => $user['email']
            ]
        ]);
        exit;
    }
}

echo json_encode([
    "status" => "error",
    "message" => "Invalid participant credentials"
]);