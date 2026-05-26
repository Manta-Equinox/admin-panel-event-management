<?php
session_start();
require_once __DIR__ . "/../../api/config/db.php";

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

$check = $dbc->prepare("SELECT event_id FROM events WHERE event_id = ? LIMIT 1");

if (!$check) {
    die("Database error: " . $dbc->error);
}

$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $check->close();
    die("Event not found.");
}

$check->close();

$stmt = $dbc->prepare("DELETE FROM events WHERE event_id = ?");

if (!$stmt) {
    die("Database error: " . $dbc->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: ../../events.php?msg=deleted");
    exit();

} else {

    $error = $stmt->error;
    $stmt->close();

    die("Failed to delete event: " . $error);
}