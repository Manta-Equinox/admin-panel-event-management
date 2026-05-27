<?php
header("Content-Type: application/json");

$base = "http://localhost/enigma/api";

$endpoints = [
    "auth" => [
        "login" => $base . "/auth.php?action=login",
        "register" => $base . "/auth.php?action=register"
    ],

    "events" => [
        "list" => $base . "/events.php?action=list",
        "create" => $base . "/events.php?action=create",
        "update" => $base . "/events.php?action=update",
        "delete" => $base . "/events.php?action=delete"
    ],

    "participants" => [
        "list" => $base . "/participants.php?action=list"
    ],

    "qr_tokens" => [
        "generate" => $base . "/qr_tokens.php?action=generate",
        "validate" => $base . "/qr_tokens.php?action=validate"
    ],

    "attendance" => [
        "log" => $base . "/attendance.php?action=log"
    ],

    "assignments" => [
        "assign_staff" => $base . "/assignments.php?action=assign",
        "list" => $base . "/assignments.php?action=list"
    ]
];

echo json_encode([
    "success" => true,
    "message" => "API Endpoints List",
    "endpoints" => $endpoints
], JSON_PRETTY_PRINT);