<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../events.php");
    exit();
}

$event_id = (int)($_GET['id'] ?? 0);
if ($event_id <= 0) {
    die("Invalid event ID");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

<h3>Assign Staff to Event</h3>

<p><b>Event:</b> <span id="eventTitle">Loading...</span></p>

<?php $event_id_js = json_encode($event_id); ?>

<div class="mb-3">
    <label>Select Section (Filter Staff)</label>
    <select id="sectionFilter" class="form-control">
        <option value="">-- Show All --</option>
        <option value="event_manager">Event Manager</option>
        <option value="registration">Registration</option>
        <option value="security">Security</option>
        <option value="decoration">Decoration</option>
        <option value="logistics">Logistics</option>
        <option value="technical">Technical Support</option>
    </select>
</div>

<div class="mb-3">
    <label>Select Employee</label>
    <select id="staffSelect" class="form-control">
        <option>Loading...</option>
    </select>
</div>

<div class="mb-3">
    <label>Section (Assignment)</label>
    <select id="sectionSelect" class="form-control">
        <option value="event_manager">Event Manager</option>
        <option value="registration">Registration</option>
        <option value="security">Security</option>
        <option value="decoration">Decoration</option>
        <option value="logistics">Logistics</option>
        <option value="technical">Technical Support</option>
    </select>
</div>

<button class="btn btn-primary" onclick="assignStaff()">Assign</button>
<a href="../../events.php" class="btn btn-secondary">Back</a>

<hr class="mt-5">

<h4>Assigned Staff</h4>

<table class="table table-striped mt-3">
    <thead>
        <tr>
            <th>Section</th>
            <th>Staff</th>
            <th>Specialization</th>
            <th>Assigned At</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody id="assignedTable">
        <tr><td colspan="5">Loading...</td></tr>
    </tbody>
</table>

<script>
const EVENT_ID = <?= $event_id_js ?>;

async function loadEvent() {
    const res = await fetch(`../../api/events/assign_staff.php?action=get_event&event_id=${EVENT_ID}`);
    const data = await res.json();

    document.getElementById("eventTitle").textContent =
        data.success ? data.event.title : "Not found";
}

async function loadStaff(section = "") {
    const res = await fetch(
        `../../api/events/assign_staff.php?action=get_staff&event_id=${EVENT_ID}&section=${section}`
    );

    const data = await res.json();

    const select = document.getElementById("staffSelect");
    select.innerHTML = "";

    if (!data.success || data.staff.length === 0) {
        select.innerHTML = `<option>No staff available</option>`;
        return;
    }

    data.staff.forEach(s => {
        const opt = document.createElement("option");
        opt.value = s.staff_id;
        opt.textContent = `${s.name} - ${s.specialization ?? 'No Specialization'}`;
        select.appendChild(opt);
    });
}

async function loadAssigned() {
    const res = await fetch(`../../api/events/assign_staff.php?action=get_assigned&event_id=${EVENT_ID}`);
    const data = await res.json();

    const tbody = document.getElementById("assignedTable");
    tbody.innerHTML = "";

    if (!data.success || data.assigned.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5">No assignments yet</td></tr>`;
        return;
    }

    data.assigned.forEach(a => {
        tbody.innerHTML += `
            <tr>
                <td><span class="badge bg-primary">${a.section}</span></td>
                <td>${a.name}</td>
                <td>${a.specialization ?? 'None'}</td>
                <td>${a.assigned_at}</td>
                <td>
                    <button class="btn btn-danger btn-sm"
                        onclick="unassign(${a.assignment_id})">
                        Unassign
                    </button>
                </td>
            </tr>
        `;
    });
}


async function assignStaff() {
    const staff_id = document.getElementById("staffSelect").value;
    const section = document.getElementById("sectionSelect").value;

    const formData = new FormData();
    formData.append("action", "assign");
    formData.append("event_id", EVENT_ID);
    formData.append("staff_id", staff_id);
    formData.append("section", section);

    const res = await fetch("../../api/events/assign_staff.php", {
        method: "POST",
        body: formData
    });

    const data = await res.json();
    alert(data.message);

    if (data.success) {
        loadAssigned();
    }
}

async function unassign(id) {
    const formData = new FormData();
    formData.append("action", "unassign");
    formData.append("event_id", EVENT_ID);
    formData.append("assignment_id", id);

    const res = await fetch("../../api/events/assign_staff.php", {
        method: "POST",
        body: formData
    });

    const data = await res.json();
    alert(data.message);

    if (data.success) {
        loadAssigned();
    }
}

document.getElementById("sectionFilter").addEventListener("change", function () {
    loadStaff(this.value);
});

loadEvent();
loadStaff();
loadAssigned();

</script>

</body>
</html>