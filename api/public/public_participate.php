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
$participant_id = (int) $_SESSION['Pid'];

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

if (!$event || $event['status'] !== 'approved') {
    echo json_encode([
        "status" => "error",
        "message" => "Event not available"
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

$alreadyJoined = $check->get_result()->num_rows > 0;


if (!empty($event['capacity']) && !$alreadyJoined) {

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
            "message" => "Event full"
        ]);
        exit();
    }
}

if (!$alreadyJoined) {

    $insert = $dbc->prepare("
        INSERT INTO event_participants (event_id, participant_id)
        VALUES (?, ?)
    ");
    $insert->bind_param("ii", $event_id, $participant_id);
    $insert->execute();
}


$checkQr = $dbc->prepare("
    SELECT token
    FROM qr_tokens
    WHERE event_id = ? AND participant_id = ?
    LIMIT 1
");
$checkQr->bind_param("ii", $event_id, $participant_id);
$checkQr->execute();
$existing = $checkQr->get_result()->fetch_assoc();

if ($existing) {

    $token = $existing['token'];

} else {

    $token = bin2hex(random_bytes(16));
    $expires = date("Y-m-d H:i:s", strtotime("+2 days"));

    $insertQr = $dbc->prepare("
        INSERT INTO qr_tokens (event_id, participant_id, token, expires_at)
        VALUES (?, ?, ?, ?)
    ");
    $insertQr->bind_param("iiss", $event_id, $participant_id, $token, $expires);
    $insertQr->execute();
}

echo json_encode([
    "status" => "success",
    "message" => $alreadyJoined ? "Already joined" : "Joined successfully",
    "data" => [
        "token" => $token,
        "qr_url" => "http://" . $_SERVER['HTTP_HOST'] .
            "/enigma/api/qr/fetch.php?token=" . $token
    ]
]);