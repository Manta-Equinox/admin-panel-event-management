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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID");
}

$id = intval($_GET['id']);

if (!isset($dbc)) {
    die("Database connection failed.");
}

$stmt = $dbc->prepare("SELECT * FROM staff_users WHERE staff_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$r = $result->fetch_assoc();

if (!$r) {
    die("User not found.");
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = trim($_POST['role'] ?? '');
    $spec  = trim($_POST['specialization'] ?? '');

    if ($name === '' || $email === '' || $role === '') {
        $fmsg = "Please fill all required fields.";
    } else {


        if ($role === 'admin') {
            $spec = null;
        }

        if ($role === 'employee' && $spec === '') {
            $fmsg = "Employees must select a specialization.";
        } else {

            $update = $dbc->prepare("
                UPDATE staff_users
                SET name = ?,
                    email = ?,
                    role = ?,
                    specialization = ?
                WHERE staff_id = ?
            ");

            $update->bind_param(
                "ssssi",
                $name,
                $email,
                $role,
                $spec,
                $id
            );

            if ($update->execute()) {
                header("Location: ../../user.php");
                exit();
            } else {
                $fmsg = "Failed to update user: " . $update->error;
            }

            $update->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <title>Enigma | Update User</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../home.css">
</head>

<body>

<?php include_once('../../templates/sidebar.php'); ?>

<section class="home-section">

    <div class="container">

        <?php if (isset($fmsg)) { ?>
            <div class="alert alert-danger">
                <?= $fmsg ?>
            </div>
        <?php } ?>

        <h2 style="padding-top: 120px; margin-left: 0px">
            Update User
        </h2>

        <form method="post" style="margin-left: 5px">

            <div class="form-group">
                <label>Name</label>
                <input type="text"
                       class="form-control"
                       name="name"
                       value="<?= htmlspecialchars($r['name'] ?? '') ?>"
                       required />
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email"
                       class="form-control"
                       name="email"
                       value="<?= htmlspecialchars($r['email'] ?? '') ?>"
                       required />
            </div>

            <div class="form-group">
                <label>Role</label>
                <select class="form-control" name="role" id="roleSelect" required>
                    <option value="admin" <?= ($r['role'] == 'admin') ? 'selected' : '' ?>>
                        Admin
                    </option>
                    <option value="employee" <?= ($r['role'] == 'employee') ? 'selected' : '' ?>>
                        Employee
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>Specialization</label>

                <select class="form-control"
                        name="specialization"
                        id="specSelect">

                    <option value="">-- Select Specialization --</option>

                    <option value="registration"
                        <?= ($r['specialization'] == 'registration') ? 'selected' : '' ?>>
                        Registration
                    </option>

                    <option value="qr_scanning"
                        <?= ($r['specialization'] == 'qr_scanning') ? 'selected' : '' ?>>
                        QR Scanning
                    </option>

                    <option value="event_management"
                        <?= ($r['specialization'] == 'event_management') ? 'selected' : '' ?>>
                        Event Management
                    </option>

                    <option value="attendance"
                        <?= ($r['specialization'] == 'attendance') ? 'selected' : '' ?>>
                        Attendance
                    </option>

                    <option value="decoration"
                        <?= ($r['specialization'] == 'decoration') ? 'selected' : '' ?>>
                        Decoration
                    </option>
                </select>
            </div>

            <br><br>
            <input type="submit"
                   class="btn btn-primary"
                   value="Update User">

        </form>
    </div>

    <?php require_once('../../templates/footer.php') ?>

</section>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const roleSelect = document.getElementById('roleSelect');
    const specSelect = document.getElementById('specSelect');

    function toggleSpec() {
        if (roleSelect.value === 'admin') {
            specSelect.value = '';
            specSelect.disabled = true;
        } else {
            specSelect.disabled = false;
        }
    }

    toggleSpec();
    roleSelect.addEventListener('change', toggleSpec);
});
</script>

</body>
</html>