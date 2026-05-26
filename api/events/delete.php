<?php
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['Aname']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid event ID"
    ]);
    exit();
}

$check = $dbc->prepare("SELECT event_id FROM events WHERE event_id = ? LIMIT 1");
$check->bind_param("i", $id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Event not found"
    ]);
    exit();
}
$check->close();

$stmt = $dbc->prepare("DELETE FROM events WHERE event_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Event deleted successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to delete event"
    ]);
}

$stmt->close();