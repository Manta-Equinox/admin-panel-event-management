<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header('Location: index.php');
    exit();
}

$role = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enigma | Events</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/home.css">
</head>

<body>

<?php include_once('./templates/sidebar.php'); ?>

<section class="home-section">

<div class="container" style="padding-top: 120px;">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4 class="fw-bold mb-0">
            <i class='bx bx-calendar'></i> Events
        </h4>

        <?php if ($role === 'admin' || $role === 'employee') { ?>
            <a href="./components/event/add.php" class="btn btn-primary">
                <i class='bx bx-plus'></i> Add Event
            </a>
        <?php } ?>

    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">

            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Type</th>
                <th>Location</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>State</th>
                <th>Participants</th>
                <th>Action</th>
            </tr>
            </thead>

            <tbody id="eventsTable">
                <tr>
                    <td colspan="11" class="text-center text-muted">
                        Loading events...
                    </td>
                </tr>
            </tbody>

        </table>
    </div>

</div>

</section>

<script>
const ROLE = <?= json_encode($role) ?>;

async function loadEvents() {
    try {
        const res = await fetch('./api/events/list.php');
        const response = await res.json();

        const tbody = document.getElementById('eventsTable');
        tbody.innerHTML = '';

        const events = response.data || [];

        if (!events.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-muted">
                        No events found
                    </td>
                </tr>
            `;
            return;
        }

        events.forEach(event => {

            const time = formatTime(event.start_time, event.end_time);

            tbody.innerHTML += `
            <tr data-event="${event.event_id}">

                <td>${event.event_id}</td>
                <td>${escapeHtml(event.title)}</td>
                <td>${escapeHtml(event.description)}</td>

                <td>
                    <span class="badge bg-primary">
                        ${event.event_type}
                    </span>
                </td>

                <td>${escapeHtml(event.location)}</td>

                <td>${event.event_date}</td>

                <td>${time}</td>

                <td>${renderStatus(event.status)}</td>

                <td>${renderState(event.event_state)}</td>

                <td>
                    <span class="badge bg-dark">
                        ${event.participant_count ?? 0} joined
                    </span>
                </td>

                <td>
                    ${renderActions(event.event_id)}
                </td>

            </tr>`;
        });

    } catch (err) {
        console.error("Failed to load events:", err);
    }
}

function renderStatus(status) {
    if (status === 'approved') return `<span class="badge bg-success">Approved</span>`;
    if (status === 'pending') return `<span class="badge bg-warning text-dark">Pending</span>`;
    if (status === 'cancelled') return `<span class="badge bg-danger">Cancelled</span>`;
    return `<span class="badge bg-secondary">Unknown</span>`;
}

function renderState(state) {
    if (state === 'upcoming') return `<span class="badge bg-info">Upcoming</span>`;
    if (state === 'ongoing') return `<span class="badge bg-success">Ongoing</span>`;
    if (state === 'ended') return `<span class="badge bg-secondary">Ended</span>`;
    return `<span class="badge bg-light text-dark">Unknown</span>`;
}

function formatTime(start, end) {
    if (!start && !end) return `<span class="text-muted">Not set</span>`;
    if (start && end) return `${start} - ${end}`;
    return start ?? '';
}

function renderActions(id) {
    if (ROLE !== 'admin') {
        return `<span class="text-muted small">View only</span>`;
    }

    return `
        <a href="./components/event/update.php?id=${id}"
           class="btn btn-info btn-sm">
            <i class='bx bx-edit'></i>
        </a>

        <button class="btn btn-danger btn-sm"
                onclick="deleteEvent(${id})">
            <i class='bx bx-trash'></i>
        </button>

        <a href="./components/event/assign_staff.php?id=${id}"
           class="btn btn-secondary btn-sm">
            <i class='bx bx-user-plus'></i>
        </a>
    `;
}

async function deleteEvent(id) {
    if (!confirm("Delete this event?")) return;

    try {
        const res = await fetch(`./api/events/delete.php?id=${id}`, {
            method: 'DELETE'
        });

        const data = await res.json();

        if (data.status === "success") {
            loadEvents(); 
        } else {
            alert(data.message || "Delete failed");
        }

    } catch (err) {
        console.error("Delete error:", err);
        alert("Server error");
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.toString().replace(/[&<>"']/g, m => ({
        '&':'&amp;',
        '<':'&lt;',
        '>':'&gt;',
        '"':'&quot;',
        "'":'&#039;'
    }[m]));
}

loadEvents();
setInterval(loadEvents, 10000);
</script>

<?php include_once('./templates/footer.php'); ?>

</body>
</html>