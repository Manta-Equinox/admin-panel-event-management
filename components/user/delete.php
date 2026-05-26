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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID");
}

$id = intval($_GET['id']);

if (isset($_SESSION['staff_id']) && $_SESSION['staff_id'] == $id) {
    die("You cannot delete your own account.");
}

$stmt = $dbc->prepare("DELETE FROM staff_users WHERE staff_id = ?");

if (!$stmt) {
    die("Prepare failed: " . $dbc->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../../user.php");
    exit();
} else {
    echo "Failed to delete user: " . $stmt->error;
}

$stmt->close();
?>