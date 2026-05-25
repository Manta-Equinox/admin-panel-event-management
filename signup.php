<?php
session_start();

if (isset($_SESSION["Pid"])) {
    header("Location: components/public/public_event.php");
    exit();
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: linear-gradient(135deg, #5469d4, #6c7ae0);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            background: white;
            width: 100%;
            max-width: 420px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
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
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #5469d4;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background: #3f53b5;
        }

        .msg {
            text-align: center;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
        }

        .footer a {
            color: #5469d4;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Create Account</h2>

    <?php if ($error): ?>
        <div class="msg error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="msg success">Account created successfully. You can login now.</div>
    <?php endif; ?>

    <form method="POST" action="participant_signup_handler.php">

        <input type="text" name="name" placeholder="Full Name" required>

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" placeholder="Password" required>

        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <button type="submit">Sign Up</button>

    </form>

    <div class="footer">
        Already have an account? <a href="index.php">Login</a>
    </div>

</div>

</body>
</html>