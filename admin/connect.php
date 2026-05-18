<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost:3307";
$user = "root";
$pass = "";
$db   = "event_management_db";

$dbc = new mysqli($host, $user, $pass, $db);

if ($dbc->connect_error) {
    die("Connection failed: " . $dbc->connect_error);
}
?>