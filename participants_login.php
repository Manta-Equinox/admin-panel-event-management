<?php
session_start();

if (isset($_SESSION["Pid"])) {
    header('location: components/public/public_event.php');
    exit();
}

$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Participant Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
    * {
        padding: 0;
        margin: 0;
        color: #1a1f36;
        box-sizing: border-box;
        word-wrap: break-word;
        font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Ubuntu, sans-serif;
    }

    body {
        min-height: 100%;
        background-color: #ffffff;
    }

    a {
        color: #5469d4;
        text-decoration: none;
    }

    .login-root {
        display: flex;
        width: 100%;
        min-height: 100vh;
        justify-content: center;
        align-items: center;
    }

    .formbg {
        width: 100%;
        max-width: 448px;
        background: white;
        border-radius: 4px;
        box-shadow: rgba(60, 66, 87, 0.12) 0px 7px 14px;
        padding: 40px;
    }

    h1 {
        text-align: center;
        margin-bottom: 10px;
    }

    span {
        display: block;
        font-size: 18px;
        margin-bottom: 20px;
        text-align: center;
    }

    label {
        font-size: 14px;
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
    }

    input {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
        margin-bottom: 15px;
        font-size: 14px;
    }

    input[type="submit"] {
        background: #5469d4;
        color: white;
        border: none;
        font-weight: bold;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background: #3f53b5;
    }

    .btn-link {
        display: block;
        text-align: center;
        margin-top: 12px;
        font-weight: 600;
        color: #5469d4;
    }

    .error {
        color: red;
        font-size: 13px;
        text-align: center;
        margin-bottom: 10px;
    }
    </style>
</head>

<body>

<div class="login-root">

    <div class="formbg">

        <h1>Enigma</h1>
        <span>Participant Login</span>

        <?php if ($error === 'invalid'): ?>
            <div class="error">Invalid credentials</div>
        <?php elseif ($error === 'empty'): ?>
            <div class="error">Please fill all fields</div>
        <?php endif; ?>

        <form method="POST" action="participants_login_process.php">

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <input type="submit" value="Login">

        </form>

        <a class="btn-link" href="signup.php">
            Create Participant Account
        </a>

        <a class="btn-link" href="index.php">
            Back to Admin Login
        </a>

    </div>

</div>

</body>
</html>