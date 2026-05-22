<?php

$host = "enigma_db";
$user = "root";
$pass = "root";
$db   = "enigma_event_db";

$dbc = new mysqli($host, $user, $pass, $db);

if ($dbc->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]));
}

header("Content-Type: application/json");
?>