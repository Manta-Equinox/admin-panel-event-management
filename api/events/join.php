<?php
require_once __DIR__ . "/../config/db.php";

session_start();
date_default_timezone_set("Asia/Manila");

header("Content-Type: application/json");

$event_id = (int)($_POST['event_id'] ?? 0);
$participant_id = (int)($_SESSION['Pid'] ?? 0);

if ($event_id <= 0 || $participant_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit();
}

$stmt = $dbc->prepare("SELECT status FROM events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event || $event['status'] !== 'approved') {
    echo json_encode(["status" => "error", "message" => "Event not available"]);
    exit();
}

$check = $dbc->prepare("
    SELECT 1 FROM event_participants
    WHERE event_id = ? AND participant_id = ?
");
$check->bind_param("ii", $event_id, $participant_id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode(["status" => "exists"]);
    exit();
}

$stmt = $dbc->prepare("
    INSERT INTO event_participants (event_id, participant_id)
    VALUES (?, ?)
");
$stmt->bind_param("ii", $event_id, $participant_id);
$stmt->execute();

$token = bin2hex(random_bytes(16));
$qr_code = "http://localhost/enigma/api/qr/view.php?token=" . $token;
$expires = date("Y-m-d H:i:s", strtotime("+7 days"));

$q = $dbc->prepare("
    INSERT INTO qr_tokens (event_id, participant_id, token, qr_code, expires_at)
    VALUES (?, ?, ?, ?, ?)
");
$q->bind_param("iisss", $event_id, $participant_id, $token, $qr_code, $expires);
$q->execute();

echo json_encode([
    "status" => "success",
    "qr" => $qr_code
]);