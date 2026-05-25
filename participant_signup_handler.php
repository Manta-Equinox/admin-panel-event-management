<?php
session_start();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    header("Location: signup.php?error=Missing fields");
    exit();
}

$url = "http://localhost/api/auth/participant_signup.php";

$data = http_build_query([
    "name" => $name,
    "email" => $email,
    "password" => $password,
    "confirm_password" => $confirm
]);

$options = [
    "http" => [
        "header"  => "Content-type: application/x-www-form-urlencoded",
        "method"  => "POST",
        "content" => $data
    ]
];

$response = file_get_contents($url, false, stream_context_create($options));
$result = json_decode($response, true);

if ($result && $result["status"] === "success") {
    header("Location: participant_login.php?success=registered");
    exit();
}

header("Location: signup.php?error=" . urlencode($result["message"] ?? "Signup failed"));
exit();
?>