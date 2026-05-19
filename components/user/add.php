<?php
session_start();

require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location: ../../home.php');
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
    <title>Enigma | Add User</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../home.css">
</head>

<body>

<?php include_once('../../templates/sidebar.php'); ?>

<section class="home-section">

<div class="container" style="padding-top: 120px;">

    <div class="d-flex align-items-center mb-3">
        <h4 class="fw-bold">
            <i class='bx bx-user-plus'></i> Add New User
        </h4>
    </div>

    <?php if (isset($fmsg)) { ?>
        <div class="alert alert-danger">
            <?php echo $fmsg; ?>
        </div>
    <?php } ?>

    <form method="post">

        <div class="form-group mb-2">
            <label>Name</label>
            <input type="text" class="form-control" name="name" required />
        </div>

        <div class="form-group mb-2">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required />
        </div>

        <div class="form-group mb-2">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required />
        </div>

        <div class="form-group mb-2">
            <label>Role</label>
            <select name="role" class="form-control" required>
                <option value="">-- Select Role --</option>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Specialization</label>
            <select name="specialization" class="form-control" id="specSelect">
                <option value="">-- Select Specialization --</option>
                <option value="registration">Registration</option>
                <option value="qr_scanning">QR Scanning</option>
                <option value="event_management">Event Management</option>
                <option value="attendance">Attendance</option>
                <option value="decoration">Decoration</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class='bx bx-user-plus'></i> Add User
        </button>

    </form>

</div>

</section>

<?php include_once('../../templates/footer.php'); ?>

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

</body>
</html>