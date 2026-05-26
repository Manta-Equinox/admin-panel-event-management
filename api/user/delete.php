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

$id = $data['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo json_encode(["success" => false, "error" => "Invalid ID"]);
    exit();
}

if (isset($_SESSION['staff_id']) && $_SESSION['staff_id'] == $id) {
    echo json_encode(["success" => false, "error" => "You cannot delete your own account"]);
    exit();
}

$stmt = $dbc->prepare("DELETE FROM staff_users WHERE staff_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();