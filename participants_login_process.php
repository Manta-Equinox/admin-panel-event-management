<?php
session_start();
require_once __DIR__ . '/connect.php';

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    header("Location: participants_login.php?error=empty");
    exit();
}

$stmt = $dbc->prepare("
    SELECT participant_id, name, email, password 
    FROM participants 
    WHERE email = ? 
    LIMIT 1
");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    if (password_verify($password, $row['password'])) {

        session_unset();
        session_regenerate_id(true);

        $_SESSION['Pid'] = $row['participant_id'];
        $_SESSION['Pname'] = $row['name'];
        $_SESSION['role'] = 'participant';

        header("Location: components/public/public_event.php");
        exit();
    }
}

header("Location: participants_login.php?error=invalid");
exit();
?>