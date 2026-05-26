<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbc = new mysqli("db", "root", "root", "event_management_db");

if ($dbc->connect_error) {
    die("Connection failed: " . $dbc->connect_error);
}