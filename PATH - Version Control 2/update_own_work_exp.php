<?php

session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Alumni') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
require 'db.php';
$alumni_id = $_SESSION['user_id'];

if ($_POST['type'] === 'current') {
    $profession = trim($_POST['current_profession'] ?? '');
    $desc = trim($_POST['current_work_desc'] ?? '');

    // Check if record exists
    $stmt = $conn->prepare("SELECT id FROM work_exp_current WHERE alumni_id=?");
    $stmt->bind_param("s", $alumni_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $stmt = $conn->prepare("UPDATE work_exp_current SET current_profession=?, current_work_desc=? WHERE alumni_id=?");
        $stmt->bind_param("sss", $profession, $desc, $alumni_id);
        $stmt->execute();
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO work_exp_current (alumni_id, current_profession, current_work_desc) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $alumni_id, $profession, $desc);
        $stmt->execute();
    }
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true]);
    exit();
}

if ($_POST['type'] === 'previous') {
    // Remove all old records
    $stmt = $conn->prepare("DELETE FROM work_exp_previous WHERE alumni_id=?");
    $stmt->bind_param("s", $alumni_id);
    $stmt->execute();
    $stmt->close();

    // Insert new ones
    $professions = $_POST['previous_profession'] ?? [];
    $descs = $_POST['previous_work_desc'] ?? [];
    $companies = $_POST['previous_company'] ?? [];
    $dates = $_POST['previous_work_date'] ?? [];
    for ($i = 0; $i < count($professions); $i++) {
        if (trim($professions[$i]) !== '') {
            $stmt = $conn->prepare("INSERT INTO work_exp_previous (alumni_id, previous_profession, previous_work_desc, company, work_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $alumni_id, $professions[$i], $descs[$i], $companies[$i], $dates[$i]);
            $stmt->execute();
            $stmt->close();
        }
    }
    $conn->close();
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);