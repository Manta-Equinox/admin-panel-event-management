<?php
session_start();

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    header("Location: participants_login.php?error=empty");
    exit();
}

/* CALL API */
$url = "http://localhost/api/auth/participant_login.php";

$data = http_build_query([
    "email" => $email,
    "password" => $password
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

    $_SESSION["Pid"] = $result["data"]["id"];
    $_SESSION["Pname"] = $result["data"]["name"];
    $_SESSION["role"] = "participant";

    header("Location: components/public/public_event.php");
    exit();
}

header("Location: participants_login.php?error=invalid");
exit();