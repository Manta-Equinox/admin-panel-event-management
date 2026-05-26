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
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Missing credentials"
    ]);
    exit();
}

if (!isset($dbc)) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit();
}

$sql = "SELECT staff_id, email, password, role 
        FROM staff_users 
        WHERE email = ? 
        LIMIT 1";

$stmt = $dbc->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database prepare failed"
    ]);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
    exit();
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid credentials"
    ]);
    exit();
}

echo json_encode([
    "status" => "success",
    "role" => $user['role'],
    "data" => [
        "id" => $user['staff_id'],
        "email" => $user['email']
    ]
]);

$stmt->close();
$dbc->close();
?>