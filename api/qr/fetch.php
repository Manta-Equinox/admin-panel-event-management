<?php
require_once __DIR__ . "/../config/db.php";

$token = $_GET['token'] ?? '';

if (!$token) {
    http_response_code(400);
    exit("Missing token");
}

$stmt = $dbc->prepare("
    SELECT event_id, participant_id
    FROM qr_tokens
    WHERE token = ?
    LIMIT 1
");

$stmt->bind_param("s", $token);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    http_response_code(404);
    exit("Invalid token");
}

$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data="
    . urlencode($token);

header("Content-Type: image/png");
readfile($qrUrl);
exit();