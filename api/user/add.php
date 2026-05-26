<?php
require_once __DIR__ . "/../config/db.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$name     = trim($data['name'] ?? '');
$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$role     = trim($data['role'] ?? '');
$spec_id  = $data['specialization_id'] ?? null;

if ($name === '' || $email === '' || $password === '' || $role === '') {
    echo json_encode(["success" => false, "error" => "All required fields must be filled"]);
    exit();
}

if (!in_array($role, ['admin', 'employee'])) {
    echo json_encode(["success" => false, "error" => "Invalid role"]);
    exit();
}

if ($role === 'admin') {
    $spec_id = null;
}

if ($role === 'employee' && empty($spec_id)) {
    echo json_encode(["success" => false, "error" => "Employee must have a specialization"]);
    exit();
}

$check = $dbc->prepare("SELECT staff_id FROM staff_users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Email already exists"]);
    $check->close();
    exit();
}

$check->close();

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $dbc->prepare("
    INSERT INTO staff_users (name, email, password, role, specialization_id)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("ssssi", $name, $email, $hashedPassword, $role, $spec_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "User created successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => $stmt->error
    ]);
}

$stmt->close();