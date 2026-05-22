<?php
session_start();

require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../home.php");
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid ID");
}

/* Optional safety: check event exists first */
$check = $dbc->prepare("SELECT event_id FROM events WHERE event_id = ?");
$check->bind_param("i", $id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    die("Event not found.");
}
$check->close();

/* Delete event */
$stmt = $dbc->prepare("DELETE FROM events WHERE event_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../../events.php?msg=deleted");
    exit();
} else {
    die("Failed to delete event: " . $stmt->error);
}

$stmt->close();
?>