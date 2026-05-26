<?php
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($event_id <= 0) {
    echo json_encode(["count" => 0]);
    exit();
}

$stmt = $dbc->prepare("
    SELECT COUNT(*) AS total
    FROM event_participants
    WHERE event_id = ?
");

$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    "count" => (int)$result['total']
]);