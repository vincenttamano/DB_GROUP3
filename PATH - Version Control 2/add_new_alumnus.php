<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require 'db.php';

$response = ['success' => false, 'message' => ''];

// Validate required fields
$required = [
    'student_id', 'last_name', 'first_name', 'email', 'password',
    'employment_status', 'college_and_course', 'graduation_year'
];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $response['message'] = "Missing required field: $field";
        echo json_encode($response);
        exit;
    }
}

// Prepare data
$student_id = trim($_POST['student_id']);
$last_name = trim($_POST['last_name']);
$first_name = trim($_POST['first_name']);
$middle_name = trim($_POST['middle_name'] ?? '');
$email = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$employment_status = trim($_POST['employment_status']);
$college_and_course = trim($_POST['college_and_course']);
$graduation_year = $_POST['graduation_year']; // year(4) can be string or int
$thesis_group_id = trim($_POST['thesis_group_id'] ?? '');
$thesis_title = trim($_POST['thesis_title'] ?? '');
$thesis_date = trim($_POST['thesis_date'] ?? '');

// Check for duplicate email or student_id
$stmt = $conn->prepare("SELECT alumni_id FROM alumni WHERE email = ? OR student_id = ?");
if (!$stmt) {
    $response['message'] = "Prepare failed: " . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt->bind_param("ss", $email, $student_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $response['message'] = "Email or Student ID already exists.";
    echo json_encode($response);
    exit;
}
$stmt->close();

// Generate a new alumni_id in the format AlumniID001, AlumniID002, etc.
$result = $conn->query("SELECT alumni_id FROM alumni ORDER BY alumni_id DESC LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $last_id = $row['alumni_id'];
    $num = intval(substr($last_id, 8)) + 1;
    $alumni_id = 'AlumniID' . str_pad($num, 3, '0', STR_PAD_LEFT);
} else {
    $alumni_id = 'AlumniID001';
}

// Insert new alumni (do NOT include id, it auto-increments)
$stmt = $conn->prepare(
    "INSERT INTO alumni 
    (alumni_id, student_id, last_name, first_name, middle_name, email, password, employment_status, college_and_course, graduation_year) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
if (!$stmt) {
    $response['message'] = "Prepare failed: " . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt->bind_param(
    "ssssssssss",
    $alumni_id,
    $student_id,
    $last_name,
    $first_name,
    $middle_name,
    $email,
    $password,
    $employment_status,
    $college_and_course,
    $graduation_year
);

if ($stmt->execute()) {
    // If thesis info is provided, insert into thesis table
    if (!empty($thesis_group_id) && !empty($thesis_title)) {
        $stmt2 = $conn->prepare("INSERT INTO thesis (thesis_group_id, title, alumni_id, thesis_date) VALUES (?, ?, ?, ?)");
        if ($stmt2) {
            $stmt2->bind_param("ssss", $thesis_group_id, $thesis_title, $alumni_id, $thesis_date);
            if ($stmt2->execute()) {
                $response['success'] = true;
                $response['message'] = "Alumnus and thesis added successfully!";
                $response['alumni_id'] = $alumni_id;
            } else {
                $response['success'] = true;
                $response['message'] = "Alumnus added, but thesis not saved: " . $stmt2->error;
                $response['alumni_id'] = $alumni_id;
            }
            $stmt2->close();
        } else {
            $response['success'] = true;
            $response['message'] = "Alumnus added, but thesis prepare failed: " . $conn->error;
            $response['alumni_id'] = $alumni_id;
        }
    } else {
        $response['success'] = true;
        $response['message'] = "Alumnus added successfully!";
        $response['alumni_id'] = $alumni_id;
    }
} else {
    $response['message'] = "Database error: " . $stmt->error;
}
$stmt->close();
$conn->close();

echo json_encode($response);