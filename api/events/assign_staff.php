<?php
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

$event_id = (int)($_POST['event_id'] ?? $_GET['event_id'] ?? 0);
$staff_id = (int)($_POST['staff_id'] ?? 0);
$section  = trim($_POST['section'] ?? '');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($event_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid event ID"]);
    exit();
}

if ($action === 'get_event') {

    $stmt = $dbc->prepare("
        SELECT event_id, title, event_type
        FROM events
        WHERE event_id = ?
    ");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) {
        echo json_encode(["success" => false, "message" => "Event not found"]);
    } else {
        echo json_encode(["success" => true, "event" => $event]);
    }
    exit();
}

if ($action === 'get_staff') {

    $selected_section = $_GET['section'] ?? '';

    $sectionMap = [
        "event_manager" => "Event Manager",
        "registration" => "Registration",
        "security" => "Security",
        "decoration" => "Decoration",
        "logistics" => "Logistics",
        "technical" => "Technical Support"
    ];

    $dbSection = $sectionMap[$selected_section] ?? null;

    if ($dbSection) {

        $stmt = $dbc->prepare("
            SELECT 
                s.staff_id,
                s.name,
                sp.name AS specialization
            FROM staff_users s
            LEFT JOIN specializations sp ON s.specialization_id = sp.id
            WHERE s.role = 'employee'
            AND sp.name = ?
        ");

        $stmt->bind_param("s", $dbSection);
        $stmt->execute();
        $result = $stmt->get_result();

    } else {

        $result = $dbc->query("
            SELECT 
                s.staff_id,
                s.name,
                sp.name AS specialization
            FROM staff_users s
            LEFT JOIN specializations sp ON s.specialization_id = sp.id
            WHERE s.role = 'employee'
        ");
    }

    $staff = [];

    while ($row = $result->fetch_assoc()) {
        $staff[] = $row;
    }

    echo json_encode(["success" => true, "staff" => $staff]);
    exit();
}

if ($action === 'get_assigned') {

    $stmt = $dbc->prepare("
        SELECT 
            ea.assignment_id,
            ea.section,
            ea.assigned_at,
            s.name,
            sp.name AS specialization
        FROM event_assignments ea
        JOIN staff_users s ON ea.staff_id = s.staff_id
        LEFT JOIN specializations sp ON s.specialization_id = sp.id
        WHERE ea.event_id = ?
        ORDER BY ea.assigned_at DESC
    ");

    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(["success" => true, "assigned" => $data]);
    exit();
}

if ($action === 'assign') {

    if ($staff_id <= 0 || $section === '') {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit();
    }

    $check = $dbc->prepare("
        SELECT 1 
        FROM event_assignments
        WHERE event_id = ? AND section = ?
        LIMIT 1
    ");

    $check->bind_param("is", $event_id, $section);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Section already assigned"]);
        exit();
    }

    $insert = $dbc->prepare("
        INSERT INTO event_assignments (event_id, staff_id, section)
        VALUES (?, ?, ?)
    ");

    $insert->bind_param("iis", $event_id, $staff_id, $section);

    if ($insert->execute()) {
        echo json_encode(["success" => true, "message" => "Assigned successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => $insert->error]);
    }

    exit();
}

if ($action === 'unassign') {

    $assignment_id = (int)($_POST['assignment_id'] ?? 0);

    if ($assignment_id <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid assignment ID"]);
        exit();
    }

    $del = $dbc->prepare("
        DELETE FROM event_assignments
        WHERE assignment_id = ? AND event_id = ?
    ");

    $del->bind_param("ii", $assignment_id, $event_id);

    if ($del->execute()) {
        echo json_encode(["success" => true, "message" => "Unassigned"]);
    } else {
        echo json_encode(["success" => false, "message" => $del->error]);
    }

    exit();
}

echo json_encode([
    "success" => false,
    "message" => "Invalid action"
]);