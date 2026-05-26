<?php
session_start();

require_once __DIR__ . "/../../api/config/db.php";

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

// fetch user
$stmt = $dbc->prepare("SELECT * FROM staff_users WHERE staff_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$r = $result->fetch_assoc();

if (!$r) {
    die("User not found.");
}

// fetch specializations
$specResult = $dbc->query("SELECT * FROM specializations");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = trim($_POST['role'] ?? '');
    $spec_id = $_POST['specialization_id'] ?? '';
    $password = trim($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $role === '') {
        $fmsg = "Please fill all required fields.";
    } else {

        // check duplicate email
        $check = $dbc->prepare("SELECT staff_id FROM staff_users WHERE email = ? AND staff_id != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $fmsg = "Email already exists.";
        } else {

            // role rules
            if ($role === 'admin') {
                $spec_id = null;
            }

            if ($role === 'employee' && $spec_id === '') {
                $fmsg = "Employees must select a specialization.";
            } else {

                // update WITH password
                if ($password !== '') {

                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $update = $dbc->prepare("
                        UPDATE staff_users
                        SET name = ?, email = ?, role = ?, specialization_id = ?, password = ?
                        WHERE staff_id = ?
                    ");

                    $update->bind_param(
                        "sssssi",
                        $name,
                        $email,
                        $role,
                        $spec_id,
                        $hashedPassword,
                        $id
                    );

                } else {

                    // update WITHOUT password
                    $update = $dbc->prepare("
                        UPDATE staff_users
                        SET name = ?, email = ?, role = ?, specialization_id = ?
                        WHERE staff_id = ?
                    ");

                    $update->bind_param(
                        "ssssi",
                        $name,
                        $email,
                        $role,
                        $spec_id,
                        $id
                    );
                }

                if ($update->execute()) {
                    header("Location: ../../user.php");
                    exit();
                } else {
                    $fmsg = "Failed to update user: " . $update->error;
                }

                $update->close();
            }
        }

        $check->close();
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

<div class="container" style="padding-top: 120px;">

    <h4 class="fw-bold mb-3">Update User</h4>

    <?php if (isset($fmsg)) { ?>
        <div class="alert alert-danger">
            <?= $fmsg ?>
        </div>
    <?php } ?>

    <form method="post">

        <div class="form-group mb-2">
            <label>Name</label>
            <input type="text" class="form-control" name="name"
                   value="<?= htmlspecialchars($r['name']) ?>" required>
        </div>

        <div class="form-group mb-2">
            <label>Email</label>
            <input type="email" class="form-control" name="email"
                   value="<?= htmlspecialchars($r['email']) ?>" required>
        </div>

        <div class="form-group mb-2">
            <label>Role</label>
            <select class="form-control" name="role" id="roleSelect" required>
                <option value="admin" <?= $r['role']=='admin'?'selected':'' ?>>Admin</option>
                <option value="employee" <?= $r['role']=='employee'?'selected':'' ?>>Employee</option>
            </select>
        </div>

        <div class="form-group mb-2">
            <label>Specialization</label>
            <select class="form-control" name="specialization_id" id="specSelect">

                <option value="">-- Select Specialization --</option>

                <?php while ($s = $specResult->fetch_assoc()) { ?>
                    <option value="<?= $s['id'] ?>"
                        <?= ($r['specialization_id'] == $s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php } ?>

            </select>
        </div>

        <div class="form-group mb-3">
            <label>New Password (optional)</label>
            <input type="password" class="form-control" name="password">
        </div>

        <button type="submit" class="btn btn-primary">
            Update User
        </button>

    </form>

</div>

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

    roleSelect.addEventListener('change', toggleSpec);
    toggleSpec();
});
</script>

</body>
</html>