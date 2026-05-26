<?php
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Missing credentials"
    ]);
    exit();
}

$sql = "SELECT participant_id, name, email, password
        FROM participants
        WHERE email = ?
        LIMIT 1";

$stmt = $dbc->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid credentials"
    ]);
    exit();
}

echo json_encode([
    "status" => "success",
    "data" => [
        "id" => $user['participant_id'],
        "name" => $user['name'],
        "email" => $user['email']
    ]
]);

$stmt->close();
$dbc->close();
?>