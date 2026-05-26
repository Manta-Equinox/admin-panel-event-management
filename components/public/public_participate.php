<?php
session_start();
require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$participant_id = (int) $_SESSION['Pid'];

if (!$event_id) {
    header("Location: public_event.php");
    exit();
}

$check = $dbc->prepare("
    SELECT id 
    FROM event_participants 
    WHERE event_id = ? AND participant_id = ?
");
$check->bind_param("ii", $event_id, $participant_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    header("Location: public_event.php?msg=already_registered");
    exit();
}

$stmt = $dbc->prepare("
    INSERT INTO event_participants (event_id, participant_id)
    VALUES (?, ?)
");
$stmt->bind_param("ii", $event_id, $participant_id);

if (!$stmt->execute()) {
    die("Failed registration: " . $stmt->error);
}

$token = bin2hex(random_bytes(16));
$qr_code = "http://localhost/enigma/api/qr/view.php?token=" . $token;

$expires_at = date("Y-m-d H:i:s", strtotime("+7 days"));

$q = $dbc->prepare("
    INSERT INTO qr_tokens (event_id, participant_id, token, qr_code, expires_at)
    VALUES (?, ?, ?, ?, ?)
");
$q->bind_param("iisss", $event_id, $participant_id, $token, $qr_code, $expires_at);
$q->execute();

header("Location: public_event_details.php?id=$event_id");
exit();
?>