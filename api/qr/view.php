<?php
require_once __DIR__ . "/../config/db.php";

$token = $_GET['token'] ?? '';

if (!$token) {
    http_response_code(400);
    exit("Missing token");
}

/* Get QR record */
$stmt = $dbc->prepare("
    SELECT qr_code, expires_at
    FROM qr_tokens
    WHERE token = ?
    LIMIT 1
");

$stmt->bind_param("s", $token);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    http_response_code(404);
    exit("Invalid QR");
}

if (strtotime($data['expires_at']) < time()) {
    http_response_code(410);
    exit("QR expired");
}

$qrData = $data['qr_code'];

header("Content-Type: image/png");

readfile("https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrData));

exit();