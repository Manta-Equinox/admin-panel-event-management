<?php
require_once "../config/db.php";
header("Content-Type: application/json");

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Missing fields"
    ]);
    exit;
}

if ($password !== $confirm) {
    echo json_encode([
        "status" => "error",
        "message" => "Passwords do not match"
    ]);
    exit;
}

$stmt = $dbc->prepare("
    SELECT participant_id
    FROM participants
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database prepare failed"
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email already registered"
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt2 = $dbc->prepare("
    INSERT INTO participants (name, email, password)
    VALUES (?, ?, ?)
");

if (!$stmt2) {
    echo json_encode([
        "status" => "error",
        "message" => "Database prepare failed"
    ]);
    exit;
}

$stmt2->bind_param("sss", $name, $email, $hashedPassword);

if ($stmt2->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Account created successfully"
    ]);
    exit;
}

echo json_encode([
    "status" => "error",
    "message" => "Failed to create account"
]);