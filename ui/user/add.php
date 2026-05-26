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

$specResult = $dbc->query("SELECT * FROM specializations");
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

<div class="container" style="padding-top: 120px; max-width: 700px;">

    <h4 class="fw-bold mb-3">
        <i class='bx bx-user-plus'></i> Add New User
    </h4>

    <div id="alertBox"></div>

    <form id="userForm">

        <div class="form-group mb-2">
            <label>Name</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <div class="form-group mb-2">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>

        <div class="form-group mb-2">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <div class="form-group mb-2">
            <label>Role</label>
            <select name="role" class="form-control" id="roleSelect" required>
                <option value="">-- Select Role --</option>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Specialization</label>
            <select name="specialization_id" class="form-control" id="specSelect">
                <option value="">-- Select Specialization --</option>

                <?php while ($row = $specResult->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php } ?>

            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class='bx bx-user-plus'></i> Add User
        </button>

    </form>

</div>

</section>

<script>
const roleSelect = document.getElementById('roleSelect');
const specSelect = document.getElementById('specSelect');
const form = document.getElementById('userForm');
const alertBox = document.getElementById('alertBox');

// disable specialization for admin
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

// AJAX submit
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = {
        name: form.name.value,
        email: form.email.value,
        password: form.password.value,
        role: form.role.value,
        specialization_id: form.specialization_id.value
    };

    try {
        const res = await fetch("../../api/user/add.php", {
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
                    ${result.message}
                </div>
            `;

            form.reset();
            toggleSpec();

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
                Server error. Please try again.
            </div>
        `;
    }
});
</script>

</body>
</html>