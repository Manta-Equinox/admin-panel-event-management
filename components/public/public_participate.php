<?php
session_start();
require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

date_default_timezone_set("Asia/Manila");

$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$participant_id = (int) $_SESSION['Pid'];

if (!$event_id) {
    header("Location: public_event.php");
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
    die("Event not found.");
}

if ($event['status'] !== 'approved') {
    echo "<script>
        alert('Event is not open for registration.');
        window.location.href='public_event.php';
    </script>";
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
    header("Location: public_event.php?msg=already_registered");
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
    $count = $countStmt->get_result()->fetch_assoc()['total'];

    if ($count >= $event['capacity']) {
        echo "<script>
            alert('Event is already full.');
            window.location.href='public_event.php';
        </script>";
        exit();
    }
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

$expires_at = date("Y-m-d H:i:s", strtotime("+2 days"));

$q = $dbc->prepare("
    INSERT INTO qr_tokens (event_id, participant_id, token, qr_code, expires_at)
    VALUES (?, ?, ?, ?, ?)
");

$q->bind_param("iisss", $event_id, $participant_id, $token, $qr_code, $expires_at);

if (!$q->execute()) {
    die("QR generation failed: " . $q->error);
}

header("Location: public_event_details.php?id=$event_id");
exit();
?>