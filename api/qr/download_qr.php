<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['Pid'])) {
    http_response_code(403);
    exit("Unauthorized");
}

$event_id = (int)($_GET['event_id'] ?? 0);
$participant_id = (int)$_SESSION['Pid'];

$stmt = $dbc->prepare("
    SELECT token
    FROM qr_tokens
    WHERE event_id = ? AND participant_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $event_id, $participant_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    exit("QR not found");
}

$text = "ENIGMA EVENT | Event $event_id | Participant $participant_id";

$url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($text);

header("Location: " . $url);
exit();