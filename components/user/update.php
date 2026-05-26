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

$stmt = $dbc->prepare("SELECT * FROM staff_users WHERE staff_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$r = $result->fetch_assoc();

if (!$r) {
    die("User not found.");
}

$specResult = $dbc->query("SELECT * FROM specializations");
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

<div class="container" style="padding-top: 120px; max-width: 700px;">

    <h4 class="fw-bold mb-3">Update User</h4>

    <div id="alertBox"></div>

    <form id="updateForm">

        <input type="hidden" name="id" value="<?= $id ?>">

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
const roleSelect = document.getElementById('roleSelect');
const specSelect = document.getElementById('specSelect');
const form = document.getElementById('updateForm');
const alertBox = document.getElementById('alertBox');

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

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = {
        id: form.id.value,
        name: form.name.value,
        email: form.email.value,
        role: form.role.value,
        specialization_id: form.specialization_id.value,
        password: form.password.value
    };

    try {
        const res = await fetch("../../api/user/update.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (result.success) {
            alertBox.innerHTML = `
                <div class="alert alert-success">
                    User updated successfully
                </div>
            `;

            setTimeout(() => {
                window.location.href = "../../user.php";
            }, 800);

        } else {
            alertBox.innerHTML = `
                <div class="alert alert-danger">
                    ${result.error}
                </div>
            `;
        }

    } catch (err) {
        alertBox.innerHTML = `
            <div class="alert alert-danger">
                Server error
            </div>
        `;
    }
});
</script>

</body>
</html>