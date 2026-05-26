<?php
session_start();
require_once __DIR__ . "/../../api/config/db.php";

if (!isset($_SESSION['Pid']) || $_SESSION['role'] !== 'participant') {
    header("Location: ../../index.php");
    exit();
}

$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
$participant_id = (int) $_SESSION['Pid'];

if ($event_id <= 0) {
    header("Location: ../../events.php");
    exit();
}

$stmt = $dbc->prepare("
    SELECT event_id, event_type, status, capacity
    FROM events
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: ../../events.php");
    exit();
}


if ($event['status'] !== 'approved') {
    echo "<script>
        alert('Event is not available.');
        window.location.href='../../events.php';
    </script>";
    exit();
}

if (strtolower($event['event_type']) === 'private') {

    $check = $dbc->prepare("
        SELECT 1 FROM event_assignments
        WHERE event_id = ? AND staff_id = ?
        LIMIT 1
    ");


    $check->bind_param("ii", $event_id, $participant_id);
    $check->execute();

    if ($check->get_result()->num_rows === 0) {
        echo "<script>
            alert('You are not allowed to join this private event.');
            window.location.href='../../events.php';
        </script>";
        exit();
    }
}

$stmt = $dbc->prepare("
    SELECT 1 FROM event_participants
    WHERE event_id = ? AND participant_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $event_id, $participant_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    header("Location: ../../events.php");
    exit();
}

if (!empty($event['capacity'])) {

    $stmt = $dbc->prepare("
        SELECT COUNT(*) AS total
        FROM event_participants
        WHERE event_id = ?
    ");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    $count = $stmt->get_result()->fetch_assoc()['total'];

    if ($count >= $event['capacity']) {
        echo "<script>
            alert('Event is full.');
            window.location.href='../../events.php';
        </script>";
        exit();
    }
}

$stmt = $dbc->prepare("
    INSERT INTO event_participants (event_id, participant_id, status)
    VALUES (?, ?, 'registered')
");
$stmt->bind_param("ii", $event_id, $participant_id);
$stmt->execute();

header("Location: ../../events.php");
exit();
?>