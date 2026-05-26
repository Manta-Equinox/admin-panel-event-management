<?php
session_start();
require_once __DIR__ . "/../api/config/db.php";

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    header("Location: ../../ui/participant_signup.php?error=empty");
    exit();
}

if ($password !== $confirm) {
    header("Location: ../../ui/participant_signup.php?error=nomatch");
    exit();
}

$stmt = $dbc->prepare("
    SELECT participant_id
    FROM participants
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $dbc->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    header("Location: ../../ui/participant_signup.php?error=exists");
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $dbc->prepare("
    INSERT INTO participants (name, email, password)
    VALUES (?, ?, ?)
");

if (!$stmt) {
    die("Database error: " . $dbc->error);
}

$stmt->bind_param("sss", $name, $email, $hashedPassword);

if ($stmt->execute()) {
    header("Location: ../../ui/participant_signup.php?success=registered");
    exit();
}

header("Location: ../../ui/participant_signup.php?error=failed");
exit();