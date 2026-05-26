<?php
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$event_id = $_POST['event_id'] ?? null;
$participant_id = $_POST['participant_id'] ?? null;

if (!$event_id || !$participant_id) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid QR format"
    ]);
    exit();
}

$check = $dbc->prepare("
    SELECT 1 FROM attendance_logs
    WHERE event_id = ? AND participant_id = ?
");
$check->bind_param("ii", $event_id, $participant_id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Already marked present"
    ]);
    exit();
}

$stmt = $dbc->prepare("
    INSERT INTO attendance_logs (event_id, participant_id, scan_time, status)
    VALUES (?, ?, NOW(), 'present')
");
$stmt->bind_param("ii", $event_id, $participant_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Attendance recorded"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to record attendance"
    ]);
}