<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    echo json_encode(['count' => 0, 'total_complete' => 0, 'total_incomplete' => 0]);
    exit();
}
require 'db.php';

$name = $_POST['name'] ?? '';
$status = $_POST['status'] ?? '';
$program = $_POST['program'] ?? '';
$year = $_POST['year'] ?? '';
$thesisGroupId = $_POST['thesisGroupId'] ?? '';

$where = [];
$params = [];
$types = '';

if ($name !== '') {
    $where[] = "(CONCAT(last_name, ' ', first_name, ' ', middle_name) LIKE ? OR last_name LIKE ? OR first_name LIKE ? OR middle_name LIKE ?)";
    $search = "%$name%";
    $params = array_merge($params, [$search, $search, $search, $search]);
    $types .= 'ssss';
}
if ($status !== '') {
    $where[] = "employment_status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($program !== '') {
    $where[] = "college_and_course = ?";
    $params[] = $program;
    $types .= 's';
}
if ($year !== '') {
    $where[] = "graduation_year = ?";
    $params[] = $year;
    $types .= 's';
}
if ($thesisGroupId !== '') {
    $where[] = "thesis_group_id LIKE ?";
    $params[] = "%$thesisGroupId%";
    $types .= 's';
}

// Count filtered (matching) results
$sql = "SELECT COUNT(*) as total FROM alumni";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$count = 0;
if ($row = $result->fetch_assoc()) {
    $count = $row['total'];
}
$stmt->close();

// Count total complete records (no filter)
$sql_complete = "SELECT COUNT(*) as total FROM alumni WHERE employment_status IS NOT NULL AND employment_status != '' AND last_name != '' AND first_name != '' AND college_and_course != '' AND graduation_year IS NOT NULL";
$result_complete = $conn->query($sql_complete);
$total_complete = 0;
if ($row = $result_complete->fetch_assoc()) {
    $total_complete = $row['total'];
}

// Count total incomplete records (no filter)
$sql_incomplete = "SELECT COUNT(*) as total FROM alumni WHERE employment_status IS NULL OR employment_status = '' OR last_name = '' OR first_name = '' OR college_and_course = '' OR graduation_year IS NULL";
$result_incomplete = $conn->query($sql_incomplete);
$total_incomplete = 0;
if ($row = $result_incomplete->fetch_assoc()) {
    $total_incomplete = $row['total'];
}

echo json_encode([
    'count' => $count,
    'total_complete' => $total_complete,
    'total_incomplete' => $total_incomplete
]);
$conn->close();