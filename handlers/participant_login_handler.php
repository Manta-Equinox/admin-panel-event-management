<?php
session_start();
require_once __DIR__ . "/../api/config/db.php";

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: ../../ui/participants_login.php?error=empty");
    exit();
}

$stmt = $dbc->prepare("
    SELECT participant_id, name, email, password
    FROM participants
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    header("Location: ../../ui/participants_login.php?error=db");
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    header("Location: ../../ui/participants_login.php?error=invalid");
    exit();
}

$_SESSION["Pid"] = $user["participant_id"];
$_SESSION["Pname"] = $user["name"];
$_SESSION["role"] = "participant";

header("Location: ../../ui/public/public_event.php");
exit();