<?php
session_start();
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['Pid']) || ($_SESSION['role'] ?? '') !== 'participant') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit();
}

$event_id = (int)($_POST['event_id'] ?? 0);
$participant_id = (int)$_SESSION['Pid'];

if ($event_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid event ID"
    ]);
    exit();
}

$stmt = $dbc->prepare("
    SELECT event_id, event_type, status, capacity
    FROM events
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();

$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo json_encode([
        "success" => false,
        "message" => "Event not found"
    ]);
    exit();
}

if ($event['status'] !== 'approved') {
    echo json_encode([
        "success" => false,
        "message" => "Event is not available"
    ]);
    exit();
}

$check = $dbc->prepare("
    SELECT 1
    FROM event_participants
    WHERE event_id = ? AND participant_id = ?
");
$check->bind_param("ii", $event_id, $participant_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Already joined"
    ]);
    exit();
}

if (!empty($event['capacity'])) {

    $countStmt = $dbc->prepare("
        SELECT COUNT(*) AS total
        FROM event_participants
        WHERE event_id = ?
    ");
    $countStmt->bind_param("i", $event_id);
    $countStmt->execute();

    $count = (int)$countStmt->get_result()->fetch_assoc()['total'];

    if ($count >= (int)$event['capacity']) {
        echo json_encode([
            "success" => false,
            "message" => "Event is full"
        ]);
        exit();
    }
}

$insert = $dbc->prepare("
    INSERT INTO event_participants (event_id, participant_id, status)
    VALUES (?, ?, 'registered')
");

$insert->bind_param("ii", $event_id, $participant_id);

if ($insert->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Joined successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to join"
    ]);
}