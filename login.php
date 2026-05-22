<?php
session_start();
require_once __DIR__ . '/connect.php';

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

$stmt = $dbc->prepare("
    SELECT staff_id, email, password, role 
    FROM staff_users 
    WHERE email = ? 
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $dbc->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {

    $row = $result->fetch_assoc();

    if (password_verify($password, $row['password'])) {

        $_SESSION['Aid']   = $row['staff_id'];
        $_SESSION['Aname'] = $row['email'];
        $_SESSION['role']  = $row['role'];

        header("Location: home.php");
        exit();
    }
}

$stmt2 = $dbc->prepare("
    SELECT participant_id, email, password 
    FROM participants 
    WHERE email = ? 
    LIMIT 1
");

if (!$stmt2) {
    die("Database error: " . $dbc->error);
}

$stmt2->bind_param("s", $email);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2 && $result2->num_rows === 1) {

    $row = $result2->fetch_assoc();

    if (password_verify($password, $row['password'])) {

        $_SESSION['Pid']   = $row['participant_id'];
        $_SESSION['Pname'] = $row['email'];
        $_SESSION['role']  = 'participant';

        header("Location: components/public/public_event.php");
        exit();
    }
}

echo "<script>
    alert('Invalid Credentials');
    window.location.href='index.php';
</script>";
exit();
?>