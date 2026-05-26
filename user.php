<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header('location: index.php');
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location: home.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enigma | Users</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/home.css">
</head>

<body>

<?php include_once('./templates/sidebar.php'); ?>

<section class="home-section">

<div class="container" style="padding-top: 120px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 fw-bold">
            <i class='bx bx-user'></i> Users
        </h4>

        <a href="./ui/user/add.php" class="btn btn-primary">
            <i class='bx bx-user-plus'></i> Add user
        </a>
    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-striped table-hover align-middle w-100">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Specialization</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody id="userTable">
                <tr>
                    <td colspan="6" class="text-center py-4">
                        Loading users...
                    </td>
                </tr>
            </tbody>

        </table>

    </div>
</div>

</section>

<?php include_once('./templates/footer.php'); ?>

<script>
async function loadUsers() {
    try {
        const res = await fetch("./api/user/list.php");
        const data = await res.json();

        let html = "";

        if (!Array.isArray(data) || data.length === 0) {
            html = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        No users found
                    </td>
                </tr>
            `;
        } else {
            data.forEach(u => {
                html += `
                    <tr>
                        <td>${u.id}</td>
                        <td>${u.name}</td>
                        <td>${u.email}</td>
                        <td>${u.role}</td>
                        <td>${u.specialization ?? '-'}</td>
                        <td>
                            <a href="./ui/user/update.php?id=${u.id}"
                               class="btn btn-info btn-sm">
                                <i class='bx bx-edit'></i> Edit
                            </a>

                            <button class="btn btn-danger btn-sm"
                                onclick="deleteUser(${u.id})">
                                <i class='bx bx-trash'></i> Delete
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        document.getElementById("userTable").innerHTML = html;

    } catch (err) {
        document.getElementById("userTable").innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    Failed to load users
                </td>
            </tr>
        `;
    }
}

async function deleteUser(id) {
    if (!confirm("Delete this user?")) return;

    try {
        const res = await fetch("./api/user/delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id })
        });

        const result = await res.json();

        if (result.success) {
            loadUsers();
        } else {
            alert(result.error || "Delete failed");
        }

    } catch (err) {
        alert("Server error");
    }
}

loadUsers();
</script>

</body>
</html>