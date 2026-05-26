<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

$event_id = (int)($_GET['event_id'] ?? 0);
$participant_id = (int)$_SESSION['Pid'];

if ($event_id <= 0) {
    header("Location: ../../ui/public/public_event.php");
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
    header("Location: ../../ui/public/public_event.php?msg=not_allowed");
    exit();
}

$check = $dbc->prepare("
    SELECT 1 FROM event_participants
    WHERE event_id = ? AND participant_id = ?
    LIMIT 1
");
$check->bind_param("ii", $event_id, $participant_id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    header("Location: ../../ui/public/public_event_details.php?id=$event_id");
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
    $count = (int)$countStmt->get_result()->fetch_assoc()['total'];

    if ($count >= (int)$event['capacity']) {
        header("Location: ../../ui/public/public_event.php?msg=full");
        exit();
    }
}

$insert = $dbc->prepare("
    INSERT INTO event_participants (event_id, participant_id)
    VALUES (?, ?)
");
$insert->bind_param("ii", $event_id, $participant_id);
$insert->execute();

$qrText = "event_id={$event_id}&participant_id={$participant_id}";

$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" .
         urlencode($qrText);


header("Location: ../../ui/public/public_event_details.php?id=$event_id");
exit();