<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    echo "Unauthorized Access";
    exit();
}

require_once "../../db_connect.php";

$id = $_GET['id'];

$DelSql = "DELETE FROM `events` WHERE eid=$id";
$res = mysqli_query($conn, $DelSql);

if ($res) {
    header('location: ../../events.php');
    exit();
} else {
    echo "Failed to delete: " . mysqli_error($conn);
}
?>