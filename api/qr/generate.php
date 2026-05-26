<?php
require_once __DIR__ . "/../config/db.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['Pid'])) {
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

$check = $dbc->prepare("
    SELECT qr_code, token
    FROM qr_tokens
    WHERE event_id = ? AND participant_id = ?
    LIMIT 1
");

$check->bind_param("ii", $event_id, $participant_id);
$check->execute();
$res = $check->get_result()->fetch_assoc();

if ($res) {
    echo json_encode([
        "success" => true,
        "qr" => $res['qr_code']
    ]);
    exit();
}

$token = bin2hex(random_bytes(16));

$qr_code = "http://localhost/enigma/api/qr/view.php?token=" . $token;

$stmt = $dbc->prepare("
    INSERT INTO qr_tokens (event_id, participant_id, token, qr_code)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param("iiss", $event_id, $participant_id, $token, $qr_code);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "qr" => $qr_code
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => $stmt->error
    ]);
}