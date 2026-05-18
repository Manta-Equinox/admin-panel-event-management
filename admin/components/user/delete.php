<?php
session_start();

require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID");
}

$id = intval($_GET['id']);

$stmt = $dbc->prepare("DELETE FROM participants WHERE participant_id = ?");

if (!$stmt) {
    die("Prepare failed: " . $dbc->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../../user.php");
    exit();
} else {
    echo "Failed to delete record.";
}

$stmt->close();
?>