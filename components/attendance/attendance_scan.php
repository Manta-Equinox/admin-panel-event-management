<?php
session_start();
require_once __DIR__ . "/../../connect.php";

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $qr = trim($_POST['qr_code'] ?? '');

    if ($qr === '') {
        $msg = "Empty QR";
    } else {

        $stmt = $dbc->prepare("
            SELECT event_id, participant_id, expires_at
            FROM qr_tokens
            WHERE qr_code = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $qr);
        $stmt->execute();
        $qrData = $stmt->get_result()->fetch_assoc();

        if (!$qrData) {
            $msg = "Invalid QR";
        } else {

            if (!empty($qrData['expires_at']) && strtotime($qrData['expires_at']) < time()) {
                $msg = "QR expired";
            } else {

                $check = $dbc->prepare("
                    SELECT log_id
                    FROM attendance_logs
                    WHERE event_id = ? AND participant_id = ?
                ");

                $check->bind_param("ii", $qrData['event_id'], $qrData['participant_id']);
                $check->execute();

                if ($check->get_result()->num_rows > 0) {
                    $msg = "Already scanned";
                } else {

                    $scanned_by = (int) $_SESSION['Aid'];

                    $insert = $dbc->prepare("
                        INSERT INTO attendance_logs (event_id, participant_id, scanned_by)
                        VALUES (?, ?, ?)
                    ");

                    $insert->bind_param(
                        "iii",
                        $qrData['event_id'],
                        $qrData['participant_id'],
                        $scanned_by
                    );

                    $insert->execute();

                    $msg = "Attendance recorded";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Scan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

<h3>QR Attendance Scanner</h3>

<?php if ($msg) { ?>
    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
<?php } ?>

<form method="POST">
    <input type="text" name="qr_code" class="form-control mb-3" placeholder="Scan QR here" required>
    <button class="btn btn-primary">Scan</button>
</form>

</body>
</html>