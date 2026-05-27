<?php
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$query = "
    SELECT
        al.log_id,
        e.title AS event_title,
        p.name AS participant_name,
        su.name AS scanned_by_name,
        DATE(al.scan_time) AS attendance_date,
        TIME(al.scan_time) AS attendance_time
    FROM attendance_logs al
    JOIN events e
        ON al.event_id = e.event_id
    JOIN participants p
        ON al.participant_id = p.participant_id
    LEFT JOIN staff_users su
        ON al.scanned_by = su.staff_id
    ORDER BY al.scan_time DESC
";

$result = mysqli_query($dbc, $query);

if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => mysqli_error($dbc),
        "data" => []
    ]);
    exit();
}

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        "log_id" => (int)$row["log_id"],
        "event" => $row["event_title"],
        "participant" => $row["participant_name"],
        "date" => $row["attendance_date"],
        "time" => $row["attendance_time"],
        "scanned_by" => $row["scanned_by_name"]
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);