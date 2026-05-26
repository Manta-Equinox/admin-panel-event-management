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

$id       = $data['id'] ?? null;
$name     = trim($data['name'] ?? '');
$email    = trim($data['email'] ?? '');
$role     = trim($data['role'] ?? '');
$spec_id  = $data['specialization_id'] ?? null;
$password = trim($data['password'] ?? '');

if (!$id || $name === '' || $email === '' || $role === '') {
    echo json_encode(["success" => false, "error" => "Missing fields"]);
    exit();
}

if ($role === 'admin') {
    $spec_id = null;
}

if ($role === 'employee' && empty($spec_id)) {
    echo json_encode(["success" => false, "error" => "Employee must have specialization"]);
    exit();
}

$check = $dbc->prepare("SELECT staff_id FROM staff_users WHERE email = ? AND staff_id != ?");
$check->bind_param("si", $email, $id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Email already exists"]);
    exit();
}
$check->close();

if ($password !== '') {

    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $dbc->prepare("
        UPDATE staff_users
        SET name=?, email=?, role=?, specialization_id=?, password=?
        WHERE staff_id=?
    ");

    $stmt->bind_param("sssssi", $name, $email, $role, $spec_id, $hashed, $id);

} else {

    $stmt = $dbc->prepare("
        UPDATE staff_users
        SET name=?, email=?, role=?, specialization_id=?
        WHERE staff_id=?
    ");

    $stmt->bind_param("ssssi", $name, $email, $role, $spec_id, $id);
}

echo json_encode([
    "success" => $stmt->execute(),
    "error" => $stmt->error ?? null
]);

$stmt->close();