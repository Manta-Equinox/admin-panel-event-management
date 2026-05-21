<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    echo "Unauthorized Access";
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access Denied";
    exit();
}

require_once __DIR__ . "/../../connect.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid ID");
}


$stmt = $dbc->prepare("DELETE FROM events WHERE event_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../../events.php");
    exit();
} else {
    echo "Failed to delete: " . $stmt->error;
}

$stmt->close();
?>