<?php
require_once __DIR__ . "/../config/db.php";

if (!isset($_GET['token'])) {
    http_response_code(400);
    die("Missing token");
}

$token = $_GET['token'];

$stmt = $dbc->prepare("
    SELECT event_id, participant_id, token
    FROM qr_tokens
    WHERE token = ?
    LIMIT 1
");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    die("Invalid token");
}

$row = $res->fetch_assoc();

$qrData = "token=" . $token;

$url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data="
    . urlencode($qrData);

$image = @file_get_contents($url);

if (!$image) {
    http_response_code(500);
    die("Failed to generate QR");
}

$filename = "event_" . $row['event_id'] . "_qr.png";

header("Content-Type: image/png");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Length: " . strlen($image));

echo $image;
exit();