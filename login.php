<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$email = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo "<script>
        alert('Missing credentials');
        window.location.href='index.php';
    </script>";
    exit();
}

session_unset();
session_regenerate_id(true);

$staff_url = "http://localhost/api/auth/staff_login.php";

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

$response = file_get_contents($staff_url, false, stream_context_create($options));
$result = json_decode($response, true);

if ($result && $result["status"] === "success") {

    $_SESSION['Aid']   = $result["data"]["id"];
    $_SESSION['Aname'] = $result["data"]["email"];
    $_SESSION['role']  = $result["role"];

    header("Location: home.php");
    exit();
}

$participant_url = "http://localhost/api/auth/participant_login.php";

$response2 = file_get_contents($participant_url, false, stream_context_create($options));
$result2 = json_decode($response2, true);

if ($result2 && $result2["status"] === "success") {

    $_SESSION['Pid']   = $result2["data"]["id"];
    $_SESSION['Pname'] = $result2["data"]["name"];
    $_SESSION['role']  = "participant";

    header("Location: components/public/public_event.php");
    exit();
}

echo "<script>
    alert('Invalid Credentials');
    window.location.href='index.php';
</script>";
exit();
?>