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

$id = (int) $_GET['id'];

$stmt = $dbc->prepare("SELECT * FROM staff_users WHERE staff_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$r = $result->fetch_assoc();

if (!$r) {
    die("User not found.");
}

$specs = [];
$specQuery = mysqli_query($dbc, "SELECT * FROM specializations");
while ($s = mysqli_fetch_assoc($specQuery)) {
    $specs[] = $s;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = trim($_POST['role'] ?? '');
    $spec  = $_POST['specialization_id'] ?? null;

    if ($name === '' || $email === '' || $role === '') {
        $fmsg = "Please fill all required fields.";
    } else {

        if ($role === 'admin') {
            $spec = null;
        }

        if ($role === 'employee' && empty($spec)) {
            $fmsg = "Employees must select a specialization.";
        } else {

            $update = $dbc->prepare("
                UPDATE staff_users
                SET name = ?, email = ?, role = ?, specialization_id = ?
                WHERE staff_id = ?
            ");

            $update->bind_param(
                "sssii",
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
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../home.css">
</head>

<body>

<?php include_once('../../templates/sidebar.php'); ?>

<section class="home-section">

<div class="container" style="padding-top: 120px;">

    <?php if (isset($fmsg)) { ?>
        <div class="alert alert-danger"><?= $fmsg ?></div>
    <?php } ?>

    <h2>Update User</h2>

    <form method="post">

        <div class="mb-2">
            <label>Name</label>
            <input type="text" class="form-control" name="name"
                   value="<?= htmlspecialchars($r['name']) ?>" required>
        </div>

        <div class="mb-2">
            <label>Email</label>
            <input type="email" class="form-control" name="email"
                   value="<?= htmlspecialchars($r['email']) ?>" required>
        </div>

        <div class="mb-2">
            <label>Role</label>
            <select class="form-select" name="role" required>
                <option value="admin" <?= $r['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="employee" <?= $r['role'] == 'employee' ? 'selected' : '' ?>>Employee</option>
            </select>
        </div>

        <div class="mb-2">
            <label>Specialization</label>
            <select class="form-select" name="specialization_id">
                <option value="">-- Select --</option>

                <?php foreach ($specs as $s) { ?>
                    <option value="<?= $s['id'] ?>"
                        <?= $r['specialization_id'] == $s['id'] ? 'selected' : '' ?>>
                        <?= $s['name'] ?>
                    </option>
                <?php } ?>

            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">
            Update User
        </button>

    </form>
</div>

<?php require_once('../../templates/footer.php') ?>

</section>

</body>
</html>