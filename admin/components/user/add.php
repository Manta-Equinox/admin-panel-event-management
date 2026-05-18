<?php
session_start();

require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location: ../../home.php');
    exit();
}

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? '');
    $spec     = trim($_POST['specialization'] ?? '');

    if ($name === '' || $email === '' || $password === '' || $role === '') {
        $fmsg = "Please fill all required fields.";
    } else {

        if ($role === 'admin') {
            $spec = null;
        }

        if ($role === 'employee' && $spec === '') {
            $fmsg = "Employees must select a specialization.";
        } else {

            $stmt = $dbc->prepare("
                INSERT INTO staff_users (name, email, password, role, specialization)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->bind_param("sssss", $name, $email, $password, $role, $spec);

            if ($stmt->execute()) {
                header("Location: ../../user.php");
                exit();
            } else {
                $fmsg = "Insert failed: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <title>Enigma | Add Users</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../../home.css">
</head>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const roleSelect = document.querySelector('select[name="role"]');
    const specSelect = document.getElementById('specSelect');

    function toggleSpec() {
        if (roleSelect.value === 'admin') {
            specSelect.value = '';
            specSelect.disabled = true;
        } else {
            specSelect.disabled = false;
        }
    }

    roleSelect.addEventListener('change', toggleSpec);

    toggleSpec();
});
</script>
<body>

<?php include_once('../../templates/sidebar.php'); ?>

<section class="home-section">

<div class="container">

    <?php if (isset($fmsg)) { ?>
        <div class="alert alert-danger">
            <?php echo $fmsg; ?>
        </div>
    <?php } ?>

    <h2 style="padding-top: 120px; margin-left: 20px">
        Add New User
    </h2>

    <form method="post" style="margin-left: 20px">

        <div class="form-group">
            <label>Name</label>
            <input type="text" class="form-control" name="name" required />
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required />
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required />
        </div>

        <!-- ROLE -->
        <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-control" required>
                <option value="">-- Select Role --</option>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
            </select>
        </div>

        <!-- SPECIALIZATION -->
        <div class="form-group">
            <label>Specialization (Event Handling)</label>

            <select name="specialization" class="form-control" id="specSelect">
                <option value="">-- Select Specialization --</option>
                <option value="registration">Registration</option>
                <option value="qr_scanning">QR Scanning</option>
                <option value="event_management">Event Management</option>
                <option value="attendance">Attendance</option>
                <option value="decoration">Decoration</option>
            </select>
        </div>

        <br><br>
        <input type="submit" class="btn btn-primary" value="Add User" />

    </form>

</div>

<?php include_once('../../templates/footer.php'); ?>

</section>

</body>
</html>