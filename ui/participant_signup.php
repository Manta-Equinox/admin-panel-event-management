<?php
session_start();

if (isset($_SESSION["Pid"])) {
    header("Location: ../ui/public/public_event.php");
    exit();
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Participant Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffffff;
        }

        .card {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 6px;
            background: #fff;
            box-shadow: rgba(60, 66, 87, 0.12) 0px 7px 14px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background: #5469d4;
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #3f53b5;
        }

        .msg {
            text-align: center;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .error { color: red; }
        .success { color: green; }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
        }

        .footer a {
            color: #5469d4;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Participant Sign Up</h2>

    <?php if ($error): ?>
        <div class="msg error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success === 'registered'): ?>
        <div class="msg success">Account created successfully. Please login.</div>
    <?php endif; ?>

    <form method="POST" action="../api/auth/participant_signup.php">

        <input type="text" name="name" placeholder="Full Name" required>

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" placeholder="Password" required>

        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <button type="submit">Create Account</button>

    </form>

    <div class="footer">
        Already have an account?
        <a href="participants_login.php">Login</a>
    </div>

</div>

</body>
</html>