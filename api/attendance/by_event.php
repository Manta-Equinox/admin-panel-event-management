<?php
require_once __DIR__ . "/../config/db.php";
session_start();

header("Content-Type: application/json");

$event_id = (int)($_GET['event_id'] ?? 0);

if ($event_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid event"]);
    exit();
}

$stmt = $dbc->prepare("
    SELECT 
        a.log_id,
        a.scan_time,
        p.name AS participant_name,
        s.name AS scanned_by
    FROM attendance_logs a
    JOIN participants p ON a.participant_id = p.participant_id
    LEFT JOIN staff_users s ON a.scanned_by = s.staff_id
    WHERE a.event_id = ?
    ORDER BY a.scan_time DESC
");

$stmt->bind_param("i", $event_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);