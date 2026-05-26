<?php
session_start();

if (!isset($_SESSION['Pid'])) {
    header("Location: ../../index.php");
    exit();
}

$event_id = (int)($_POST['event_id'] ?? 0);

if ($event_id <= 0) {
    header("Location: public_event.php");
    exit();
}

header("Location: ../../api/events/join.php?event_id=" . $event_id);
exit();