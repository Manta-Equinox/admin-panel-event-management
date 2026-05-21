<?php
session_start();
require_once __DIR__ . '/connect.php';

if (isset($_SESSION["Aname"])) {
    header("Location: home.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (
        empty($name) ||
        empty($email) ||
        empty($password) ||
        empty($confirmPassword)
    ) {

        $message = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Invalid email format.";

    } elseif ($password !== $confirmPassword) {

        $message = "Passwords do not match.";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";

    } else {

        $check = $dbc->prepare("
            SELECT staff_id 
            FROM staff_users 
            WHERE email = ?
        ");

        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            $message = "Email already exists.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $dbc->prepare("
                INSERT INTO staff_users
                (
                    name,
                    email,
                    password,
                    role,
                    specialization_id
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    'participant',
                    NULL
                )
            ");

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {

                echo "<script>
                    alert('Account created successfully!');
                    window.location.href='index.php';
                </script>";
                exit();

            } else {

                $message = "Failed to create account.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Sign Up</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
        }

        .signup-card {
            max-width: 500px;
            margin: 60px auto;
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            padding: 30px;
            background: white;
        }

        .btn-primary {
            background: #5469d4;
            border: none;
        }

        .btn-primary:hover {
            background: #4254b8;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="signup-card">

        <div class="top-bar mb-4">
            <h3>Create Participant Account</h3>

            <a href="index.php" class="btn btn-secondary btn-sm">
                ← Back
            </a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-warning">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">
                    Full Name
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    class="form-control"
                    required
                >
            </div>

            <button
                type="submit"
                class="btn btn-primary w-100"
            >
                Create Account
            </button>

        </form>

        <div class="text-center mt-3">
            Already have an account?
            <a href="index.php">
                Login here
            </a>
        </div>

    </div>

</div>

</body>
</html>