<?php
require_once __DIR__ . "/../config/db.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['Aname'])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit();
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

$sql = "
SELECT 
    al.log_id,
    al.event_id,
    al.participant_id,
    al.scanned_by,
    al.scan_time,

    e.title AS event_title,
    e.event_date,

    p.name AS participant_name,
    p.email AS participant_email,

    s.name AS scanner_name

FROM attendance_logs al

LEFT JOIN events e 
    ON al.event_id = e.event_id

LEFT JOIN participants p 
    ON al.participant_id = p.participant_id

LEFT JOIN staff_users s 
    ON al.scanned_by = s.staff_id
";

if ($event_id > 0) {
    $sql .= " WHERE al.event_id = $event_id ";
}

$sql .= " ORDER BY al.scan_time DESC";

$result = $dbc->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);