<?php
require_once __DIR__ . "/../config/db.php";

date_default_timezone_set("Asia/Manila");

header("Content-Type: application/json");

$now = new DateTime();

$query = "
    SELECT 
        e.*,
        (
            SELECT COUNT(*) 
            FROM event_participants ep 
            WHERE ep.event_id = e.event_id
        ) AS participant_count
    FROM events e
    ORDER BY e.event_id DESC
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

    $startTime = $row['start_time'] ?: '00:00:00';
    $endTime   = $row['end_time'] ?: '23:59:59';

    $start = new DateTime($row['event_date'] . ' ' . $startTime);
    $end   = new DateTime($row['event_date'] . ' ' . $endTime);

    if ($end <= $start) {
        $end->modify('+1 day');
    }

    if ($now < $start) {
        $state = "upcoming";
    } elseif ($now >= $start && $now <= $end) {
        $state = "ongoing";
    } else {
        $state = "ended";
    }

    $data[] = [
        "event_id" => (int)$row["event_id"],
        "title" => $row["title"],
        "description" => $row["description"],
        "event_type" => $row["event_type"],
        "location" => $row["location"],
        "event_date" => $row["event_date"],
        "start_time" => $row["start_time"],
        "end_time" => $row["end_time"],
        "status" => $row["status"],
        "event_state" => $state,
        "participant_count" => (int)$row["participant_count"]
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);