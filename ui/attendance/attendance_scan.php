<?php
session_start();

if (!isset($_SESSION['Aname'])) {
    header("Location: ../../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>QR Scanner</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-white">

<div class="container py-5">

    <h3 class="mb-3">QR Attendance Scanner</h3>

    <div class="card p-3 bg-light text-dark">

        <video id="video" width="100%" style="border-radius:10px;"></video>

        <div class="mt-3">
            <p><b>Status:</b> <span id="status">Waiting...</span></p>
        </div>

    </div>

</div>

<script type="module">
import { BrowserMultiFormatReader } from "https://cdn.jsdelivr.net/npm/@zxing/browser@latest/+esm";

const codeReader = new BrowserMultiFormatReader();
const videoElement = document.getElementById('video');
const statusEl = document.getElementById('status');

let scanning = true;

codeReader.decodeFromVideoDevice(null, videoElement, async (result) => {

    if (!result || !scanning) return;

    scanning = false;
    statusEl.innerText = "Processing QR...";

    try {
        let text = result.getText();
        console.log("RAW QR:", text);
        if (text.includes("?")) {
            text = text.split("?")[1];
        }

        if (text.includes("|")) {
            const parts = text.split("|");
            text = parts[parts.length - 1].trim();
        }

        const params = new URLSearchParams(text);

        const event_id = params.get("event_id");
        const participant_id = params.get("participant_id");

        if (!event_id || !participant_id) {
            statusEl.innerText = "❌ Invalid QR format";
            scanning = true;
            return;
        }

        const res = await fetch("../../api/qr/validate.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `event_id=${event_id}&participant_id=${participant_id}`
        });

        const data = await res.json();

        if (data.success) {
            statusEl.innerText = "✅ " + data.message;
        } else {
            statusEl.innerText = "❌ " + data.message;
        }

    } catch (err) {
        console.error(err);
        statusEl.innerText = "Error processing QR";
    }

    setTimeout(() => {
        scanning = true;
        statusEl.innerText = "Waiting...";
    }, 2000);
});

</script>

</body>
</html>