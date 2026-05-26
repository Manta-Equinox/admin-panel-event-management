<?php
require_once __DIR__ . "/../config/db.php";
header('Content-Type: application/json');

$assignment_id = (int)($_POST['assignment_id'] ?? 0);
$event_id      = (int)($_POST['event_id'] ?? 0);

if ($assignment_id <= 0 || $event_id <= 0) {
    echo json_encode(["success" => false, "msg" => "Invalid request"]);
    exit();
}

$stmt = $dbc->prepare("
    DELETE FROM event_assignments
    WHERE assignment_id = ? AND event_id = ?
");

$stmt->bind_param("ii", $assignment_id, $event_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "msg" => "Delete failed"]);
}