<?php
session_start();

if (isset($_SESSION["Pid"])) {
    header("Location: ../components/public/public_event.php");
    exit();
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" href="http://cb.mitindia.edu/cb/workshopimages/cbi1.png">
    <title>Participant Login</title>
    <meta charset="UTF-8">
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
        min-height: 100vh;
        background: #ffffff;
    }

    a {
        color: #5469d4;
        text-decoration: none;
    }

    .login-root {
        display: flex;
        width: 100%;
        min-height: 100vh;
        overflow: hidden;
    }

    .loginbackground {
        position: fixed;
        inset: 0;
        z-index: 0;
        overflow: hidden;
    }

    .flex-flex { display: flex; }
    .flex-direction--column { flex-direction: column; }
    .flex-justifyContent--center { justify-content: center; }
    .align-center { align-items: center; }

    .loginbackground-gridContainer {
        display: grid;
        grid-template-columns: repeat(16, 86.6px);
        grid-template-rows: repeat(8, 64px);
        justify-content: center;
        margin: 0 -2%;
        transform: rotate(-12deg) skew(-12deg);
    }

    .box-root { box-sizing: border-box; }

    .box-background--white { background: #fff; }
    .box-background--blue { background: #5469d4; }
    .box-background--blue800 { background: #212d63; }
    .box-background--gray100 { background: #e3e8ee; }
    .box-background--cyan200 { background: #7fd3ed; }

    .box-divider--light-all-2 {
        box-shadow: inset 0 0 0 2px #e3e8ee;
    }

    .form-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        position: relative;
        z-index: 2;
    }

    .formbg {
        width: 100%;
        max-width: 448px;
        background: #fff;
        border-radius: 4px;
        box-shadow: rgba(60,66,87,0.12) 0px 7px 14px,
                    rgba(0,0,0,0.12) 0px 3px 6px;
        padding: 40px;
    }

    h1 {
        text-align: center;
        margin-bottom: 10px;
    }

    span {
        display: block;
        font-size: 20px;
        text-align: center;
        margin-bottom: 20px;
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
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 15px;
    }

    input[type="submit"] {
        background: #5469d4;
        color: #fff;
        font-weight: 600;
        border: none;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background: #3f53b5;
    }

    .error {
        color: red;
        text-align: center;
        margin-bottom: 10px;
        font-size: 13px;
    }
    </style>
</head>

<body>

<div class="login-root">

    <div class="loginbackground box-background--white">
        <div class="loginbackground-gridContainer">

            <div class="box-root flex-flex" style="grid-area: top / start / 8 / end;">
                <div class="box-root" style="background: linear-gradient(#fff,#f7fafc); flex-grow:1;"></div>
            </div>

            <div class="box-root flex-flex" style="grid-area: 4 / 2 / auto / 5;">
                <div class="box-root box-divider--light-all-2" style="flex-grow:1;"></div>
            </div>

            <div class="box-root flex-flex" style="grid-area: 6 / start / auto / 2;">
                <div class="box-root box-background--blue800" style="flex-grow:1;"></div>
            </div>

        </div>
    </div>

    <div class="form-wrapper">

        <div class="formbg">

            <h1>Enigma</h1>
            <span>Participant Login</span>

            <?php if ($error): ?>
                <div class="error">Invalid credentials</div>
            <?php endif; ?>

            <form method="POST" action="../api/auth/participant_login.php">

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <input type="submit" value="Login">

            </form>

            <div style="text-align:center; margin-top:10px;">
                <a href="participant_signup.php">Create account</a>
            </div>
            <div style="text-align:center; margin-top:10px;">
                <a href="../index.php" style="
                    display:inline-block;
                    padding:10px 16px;
                    border:1px solid #5469d4;
                    border-radius:4px;
                    color:#5469d4;
                    font-weight:600;
                    text-decoration:none;
                ">
                    Back to Admin Login
                </a>
            </div>

        </div>

    </div>

</div>

</body>
</html>