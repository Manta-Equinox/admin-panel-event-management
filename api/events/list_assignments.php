<?php
require_once __DIR__ . "/../config/db.php";
header('Content-Type: application/json');

$event_id = (int)($_GET['event_id'] ?? 0);

if ($event_id <= 0) {
    echo json_encode([]);
    exit();
}

$stmt = $dbc->prepare("
    SELECT 
        ea.assignment_id,
        ea.section,
        ea.assigned_at,
        s.name,
        sp.name AS specialization
    FROM event_assignments ea
    JOIN staff_users s ON ea.staff_id = s.staff_id
    LEFT JOIN specializations sp ON s.specialization_id = sp.id
    WHERE ea.event_id = ?
    ORDER BY ea.assigned_at DESC
");

$stmt->bind_param("i", $event_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);