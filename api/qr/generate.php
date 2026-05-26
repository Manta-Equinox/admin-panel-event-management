<?php
require_once __DIR__ . "/../config/db.php";
header("Content-Type: application/json");

$event_id = $_POST['event_id'] ?? null;
$participant_id = $_POST['participant_id'] ?? null;

if (!$event_id || !$participant_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing parameters"
    ]);
    exit;
}

$stmt = $dbc->prepare("
    SELECT token_id
    FROM qr_tokens
    WHERE event_id = ? AND participant_id = ?
");

$stmt->bind_param("ii", $event_id, $participant_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    echo json_encode([
        "status" => "success",
        "message" => "QR already exists"
    ]);
    exit;
}

$token = bin2hex(random_bytes(16));

$qrData = json_encode([
    "event_id" => $event_id,
    "participant_id" => $participant_id,
    "token" => $token
]);

$stmt = $dbc->prepare("
    INSERT INTO qr_tokens (event_id, participant_id, token, qr_code, expires_at)
    VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY))
");

$stmt->bind_param("iiss", $event_id, $participant_id, $token, $qrData);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "token" => $token,
        "qr_data" => $qrData
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to generate QR"
    ]);
}