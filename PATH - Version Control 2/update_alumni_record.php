<?php
// FILE: update_alumni_record.php
session_start(); // Start the session

// Check if user is logged in AND is an Admin, otherwise stop execution
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    // Return a JSON error response if unauthorized
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Check if necessary data was provided via POST
if (!isset($_POST['alumni_id'], $_POST['student_id'], $_POST['last_name'], $_POST['first_name'], $_POST['email'], $_POST['employment_status'], $_POST['college_and_course'], $_POST['graduation_year'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Required data not provided.']);
    exit();
}

// Sanitize and get data from POST
$alumni_id = trim($_POST['alumni_id']);
$student_id = trim($_POST['student_id']);
$last_name = trim($_POST['last_name']);
$first_name = trim($_POST['first_name']);
$middle_name = trim($_POST['middle_name'] ?? ''); // Middle name is optional
$email = trim($_POST['email']);
$employment_status = trim($_POST['employment_status']);
$college_and_course = trim($_POST['college_and_course']);
$graduation_year = intval($_POST['graduation_year']); // Ensure year is integer

// Get optional thesis fields
$thesis_group_id = trim($_POST['thesis_group_id'] ?? '');
$thesis_title = trim($_POST['thesis_title'] ?? '');

// Basic validation (can be expanded)
if (empty($alumni_id) || empty($student_id) || empty($last_name) || empty($first_name) || empty($email) || empty($employment_status) || empty($college_and_course) || empty($graduation_year)) {
     header('Content-Type: application/json');
     echo json_encode(['success' => false, 'message' => 'Required fields cannot be empty.']);
     exit();
}

// Add more specific validation if needed (e.g., email format, year format)

// Include your database connection file
require 'db.php'; // Adjust the path if necessary

// --- Update Alumni Table ---
$sql_alumni = "UPDATE alumni SET
                   student_id = ?,
                   last_name = ?,
                   first_name = ?,
                   middle_name = ?,
                   email = ?,
                   employment_status = ?,
                   college_and_course = ?,
                   graduation_year = ?
               WHERE alumni_id = ?";

$stmt_alumni = $conn->prepare($sql_alumni);

if ($stmt_alumni === false) {
    error_log("Prepare failed (update alumni): (" . $conn->errno . ") " . $conn->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error preparing alumni update.']);
    $conn->close();
    exit();
}

// Bind parameters for alumni update
$stmt_alumni->bind_param("sssssssis", $student_id, $last_name, $first_name, $middle_name, $email, $employment_status, $college_and_course, $graduation_year, $alumni_id);

$alumni_update_success = $stmt_alumni->execute();

if ($alumni_update_success === false) {
    error_log("Execute failed (update alumni): (" . $stmt_alumni->errno . ") " . $stmt_alumni->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error updating alumni record.']);
    $stmt_alumni->close();
    $conn->close();
    exit();
}

$stmt_alumni->close();

// --- Update or Insert into Thesis Table ---
// Check if thesis info is provided
if (!empty($thesis_group_id) && !empty($thesis_title)) {
    // Check if a thesis record already exists for this alumni_id
    $sql_check_thesis = "SELECT thesis_group_id FROM thesis WHERE alumni_id = ?";
    $stmt_check_thesis = $conn->prepare($sql_check_thesis);

    if ($stmt_check_thesis === false) {
        error_log("Prepare failed (check thesis): (" . $conn->errno . ") " . $conn->error);
        // Continue with alumni update success, but log thesis error
    } else {
        $stmt_check_thesis->bind_param("s", $alumni_id);
        $stmt_check_thesis->execute();
        $stmt_check_thesis->store_result();

        if ($stmt_check_thesis->num_rows > 0) {
            // Thesis record exists, update it
            $sql_thesis = "UPDATE thesis SET thesis_group_id = ?, title = ? WHERE alumni_id = ?";
            $stmt_thesis = $conn->prepare($sql_thesis);
            if ($stmt_thesis === false) {
                 error_log("Prepare failed (update thesis): (" . $conn->errno . ") " . $conn->error);
            } else {
                 $stmt_thesis->bind_param("sss", $thesis_group_id, $thesis_title, $alumni_id);
                 $thesis_update_success = $stmt_thesis->execute();
                 if ($thesis_update_success === false) {
                      error_log("Execute failed (update thesis): (" . $stmt_thesis->errno . ") " . $stmt_thesis->error);
                 }
                 $stmt_thesis->close();
            }

        } else {
            // No thesis record exists, insert a new one
            $sql_thesis = "INSERT INTO thesis (alumni_id, thesis_group_id, title) VALUES (?, ?, ?)";
            $stmt_thesis = $conn->prepare($sql_thesis);
             if ($stmt_thesis === false) {
                 error_log("Prepare failed (insert thesis): (" . $conn->errno . ") " . $conn->error);
             } else {
                 $stmt_thesis->bind_param("sss", $alumni_id, $thesis_group_id, $thesis_title);
                 $thesis_insert_success = $stmt_thesis->execute();
                 if ($thesis_insert_success === false) {
                      error_log("Execute failed (insert thesis): (" . $stmt_thesis->errno . ") " . $stmt_thesis->error);
                 }
                 $stmt_thesis->close();
             }
        }
        $stmt_check_thesis->close();
    }
} else {
    // If thesis fields are empty, you might want to delete the existing thesis record
    // or just leave it. This code assumes you leave it if fields are empty.
    // If you want to delete:
    // $sql_delete_thesis = "DELETE FROM thesis WHERE alumni_id = ?";
    // ... prepare, bind, execute delete statement ...
}

// --- Update or Insert Current Work Experience ---
$current_profession = $_POST['current_profession'] ?? '';
$current_work_desc = $_POST['current_work_desc'] ?? '';

$stmt = $conn->prepare("SELECT id FROM work_exp_current WHERE alumni_id = ?");
$stmt->bind_param("s", $alumni_id); // <-- FIXED
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    // Update
    $stmt->close();
    $stmt = $conn->prepare("UPDATE work_exp_current SET current_profession=?, current_work_desc=? WHERE alumni_id=?");
    $stmt->bind_param("sss", $current_profession, $current_work_desc, $alumni_id); // <-- FIXED
    $stmt->execute();
} else {
    // Insert
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO work_exp_current (alumni_id, current_profession, current_work_desc) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $alumni_id, $current_profession, $current_work_desc); // <-- FIXED
    $stmt->execute();
}
$stmt->close();

// --- Update Previous Work Experience ---
// Remove all old records
$stmt = $conn->prepare("DELETE FROM work_exp_previous WHERE alumni_id = ?");
$stmt->bind_param("s", $alumni_id); // <-- FIXED
$stmt->execute();
$stmt->close();

// Insert new ones
if (!empty($_POST['previous_profession'])) {
    $professions = $_POST['previous_profession'];
    $descs = $_POST['previous_work_desc'];
    $companies = $_POST['previous_company'];
    $dates = $_POST['previous_work_date'];
    for ($i = 0; $i < count($professions); $i++) {
        if (trim($professions[$i]) !== '') {
            $stmt = $conn->prepare("INSERT INTO work_exp_previous (alumni_id, previous_profession, previous_work_desc, company, work_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $alumni_id, $professions[$i], $descs[$i], $companies[$i], $dates[$i]); // <-- FIXED
            $stmt->execute();
            $stmt->close();
        }
    }
}

// --- Return Success Response ---
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Alumni record updated successfully.']);

$conn->close(); // Close the database connection
?>
