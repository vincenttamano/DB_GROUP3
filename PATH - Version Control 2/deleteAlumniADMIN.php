<?php
// FILE: delete_alumni_admin.php
session_start(); // Start the session

// Check if user is logged in AND is an Admin, otherwise stop execution
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    // Return an error message or status
    http_response_code(403); // Forbidden
    echo "Unauthorized access.";
    exit(); // Stop script execution
}

// Include your database connection file
require 'db.php'; // Adjust the path if necessary

// Get the alumni_id from the AJAX POST request
$alumni_id = $_POST['alumni_id'] ?? ''; // Expecting 'alumni_id' now

// Validate the received ID
if (empty($alumni_id)) {
    echo "Error: Invalid Alumni ID.";
    $conn->close();
    exit();
}

// --- Prepare and Execute the DELETE Statement ---
// Using prepared statements to prevent SQL injection
$sql = "DELETE FROM alumni WHERE alumni_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Handle prepare error
    error_log("Prepare failed (delete): (" . $conn->errno . ") " . $conn->error);
    echo "Error: Database error preparing delete query.";
    $conn->close();
    exit();
}

// Bind the alumni_id parameter (assuming alumni_id is a string)
$stmt->bind_param("s", $alumni_id);

// Execute the delete statement
if ($stmt->execute()) {
    // Check if any rows were affected
    if ($stmt->affected_rows > 0) {
        echo "Record deleted successfully.";
    } else {
        echo "Error: Record not found or already deleted.";
    }
} else {
    // Handle execution error
    error_log("Execute failed (delete): (" . $stmt->errno . ") " . $conn->error);
    echo "Error: Database error during deletion.";
}

$stmt->close();
$conn->close(); // Close the database connection
?>
