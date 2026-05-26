<?php
session_start();
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['Pid'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit();
}

$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$participant_id = (int)$_SESSION['Pid'];

if (!$event_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid event"
    ]);
    exit();
}

$stmt = $dbc->prepare("
    SELECT event_id, status, capacity
    FROM events
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo json_encode([
        "status" => "error",
        "message" => "Event not found"
    ]);
    exit();
}

if ($event['status'] !== 'approved') {
    echo json_encode([
        "status" => "error",
        "message" => "Event not open for registration"
    ]);
    exit();
}

$check = $dbc->prepare("
    SELECT 1
    FROM event_participants
    WHERE event_id = ? AND participant_id = ?
    LIMIT 1
");
$check->bind_param("ii", $event_id, $participant_id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Already registered"
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
            "status" => "error",
            "message" => "Event is full"
        ]);
        exit();
    }
}

$insert = $dbc->prepare("
    INSERT INTO event_participants (event_id, participant_id)
    VALUES (?, ?)
");

$insert->bind_param("ii", $event_id, $participant_id);

if (!$insert->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Registration failed"
    ]);
    exit();
}

$token = bin2hex(random_bytes(16));
$qr_code = "http://localhost/enigma/api/qr/view.php?token=" . $token;
$expires_at = date("Y-m-d H:i:s", strtotime("+2 days"));

$q = $dbc->prepare("
    INSERT INTO qr_tokens (event_id, participant_id, token, qr_code, expires_at)
    VALUES (?, ?, ?, ?, ?)
");

$q->bind_param("iisss", $event_id, $participant_id, $token, $qr_code, $expires_at);

if (!$q->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "QR generation failed"
    ]);
    exit();
}

echo json_encode([
    "status" => "success",
    "message" => "Successfully registered",
    "qr" => $qr_code
]);