<?php
session_start();
require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['participant_id']) && !isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

if (!$event_id) {
    header("Location: public_event.php");
    exit();
}

$participant_id = $_SESSION['participant_id'] ?? null;
$name  = $_SESSION['name'] ?? null;
$email = $_SESSION['email'] ?? null;

if ($participant_id) {
    $check = $dbc->prepare("SELECT id FROM event_participants WHERE event_id = ? AND participant_id = ?");
    $check->bind_param("ii", $event_id, $participant_id);
} else {
    $check = $dbc->prepare("SELECT id FROM event_participants WHERE event_id = ? AND email = ?");
    $check->bind_param("is", $event_id, $email);
}

$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    header("Location: public_event.php?msg=already_registered");
    exit();
}

$check->close();

$stmt = $dbc->prepare("
    INSERT INTO event_participants (event_id, participant_id, name, email, status)
    VALUES (?, ?, ?, ?, 'registered')
");

$stmt->bind_param("iiss", $event_id, $participant_id, $name, $email);

if ($stmt->execute()) {
    header("Location: public_event.php?msg=registered");
    exit();
}

die("Failed: " . $stmt->error);
?>