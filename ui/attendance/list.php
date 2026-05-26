<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header('Location: ../../index.php');
    exit();
}

$role = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Enigma | Attendance</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/home.css">
</head>

<body>

<?php include_once('../../templates/sidebar.php'); ?>

<section class="home-section">

<div class="container" style="padding-top: 120px;">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4 class="fw-bold mb-0">
            <i class='bx bx-qr-scan'></i> Attendance Logs
        </h4>

        <button class="btn btn-outline-secondary btn-sm" onclick="loadLogs()">
            Refresh
        </button>

    </div>

    <!-- FILTER -->
    <div class="row mb-3">
        <div class="col-md-4">
            <select id="eventFilter" class="form-select" onchange="loadLogs()">
                <option value="0">All Events</option>
            </select>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Event</th>
                    <th>Participant</th>
                    <th>Email</th>
                    <th>Scanned By</th>
                    <th>Time</th>
                </tr>
            </thead>

            <tbody id="attendanceTable">
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        Loading attendance logs...
                    </td>
                </tr>
            </tbody>

        </table>
    </div>

</div>

</section>

<script>

async function loadEventsFilter() {
    try {
        const res = await fetch('../../api/events/list.php');
        const data = await res.json();

        const select = document.getElementById('eventFilter');

        (data.data || []).forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.event_id;
            opt.textContent = e.title;
            select.appendChild(opt);
        });

    } catch (err) {
        console.error("Failed to load events filter", err);
    }
}

async function loadLogs() {
    try {

        const eventId = document.getElementById('eventFilter').value;

        const res = await fetch(`../../api/attendance/list.php?event_id=${eventId}`);
        const data = await res.json();

        const tbody = document.getElementById('attendanceTable');
        tbody.innerHTML = '';

        if (!data.success || !data.data.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        No attendance records found
                    </td>
                </tr>
            `;
            return;
        }

        data.data.forEach(log => {
            tbody.innerHTML += `
                <tr>
                    <td>${log.log_id}</td>
                    <td>${escapeHtml(log.event_title ?? '')}</td>
                    <td>${escapeHtml(log.participant_name ?? '')}</td>
                    <td>${escapeHtml(log.participant_email ?? '')}</td>
                    <td>${escapeHtml(log.scanner_name ?? 'QR Scanner')}</td>
                    <td>${log.scan_time}</td>
                </tr>
            `;
        });

    } catch (err) {
        console.error("Load logs error:", err);
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

loadEventsFilter();
loadLogs();

setInterval(loadLogs, 5000);

</script>

<?php include_once('../../templates/footer.php'); ?>

</body>
</html>