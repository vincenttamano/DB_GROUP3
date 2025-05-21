<?php
// FILE: fetch_alumnus_for_edit.php
session_start(); // Start the session

// Check if user is logged in AND is an Admin, otherwise stop execution
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    // Return a JSON error response if unauthorized
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Check if search_id was provided via POST
if (!isset($_POST['search_id']) || empty($_POST['search_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Search ID not provided.']);
    exit();
}

$search_id = $_POST['search_id'];

// Include your database connection file
require 'db.php'; // Adjust the path if necessary

// --- Construct the SQL Query to Fetch Single Alumnus Data by Alumni ID or Student ID ---
// We'll search in both alumni_id and student_id columns
$sql = "SELECT
            a.alumni_id,
            a.student_id,
            a.last_name,
            a.first_name,
            a.middle_name,
            a.email,
            a.employment_status,
            a.college_and_course,
            a.graduation_year,
            t.thesis_group_id,
            t.thesis_date,
            t.title AS thesis_title
        FROM
            alumni a
        LEFT JOIN
            thesis t ON a.alumni_id = t.alumni_id
        WHERE
            a.alumni_id = ? OR a.student_id = ?"; // Search by either ID

// --- Prepare and Execute the Statement ---
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Handle prepare error
    error_log("Prepare failed (fetch for edit): (" . $conn->errno . ") " . $conn->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error preparing query.']);
    $conn->close();
    exit();
}

// Bind the search_id parameter twice (for both WHERE conditions)
$stmt->bind_param("ss", $search_id, $search_id);
$stmt->execute();
$result = $stmt->get_result(); // Get the result set

// --- Fetch and Return Data ---
header('Content-Type: application/json'); // Set content type to JSON

if ($result->num_rows > 0) {
    $alumnus_data = $result->fetch_assoc();

    // Fetch current work experience
    $currentExp = null;
    $stmt2 = $conn->prepare("SELECT current_profession, current_work_desc FROM work_exp_current WHERE alumni_id = ?");
    $stmt2->bind_param("s", $alumnus_data['alumni_id']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if ($row = $result2->fetch_assoc()) {
        $currentExp = $row;
    }
    $stmt2->close();

    // Fetch previous work experiences
    $previousExp = [];
    $stmt3 = $conn->prepare("SELECT previous_profession, previous_work_desc, company, work_date FROM work_exp_previous WHERE alumni_id = ? ORDER BY work_date DESC");
    $stmt3->bind_param("s", $alumnus_data['alumni_id']);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    while ($row = $result3->fetch_assoc()) {
        $previousExp[] = $row;
    }
    $stmt3->close();

    // Add to response
    $alumnus_data['current_work_exp'] = $currentExp;
    $alumnus_data['previous_work_exp'] = $previousExp;

    echo json_encode(['success' => true, 'data' => $alumnus_data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Alumnus not found.']);
}

$stmt->close();
$conn->close(); // Close the database connection
?>
