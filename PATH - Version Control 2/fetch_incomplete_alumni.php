<?php
// FILE: fetch_incomplete_alumni.php
session_start(); // Start the session

// Check if user is logged in AND is an Admin, otherwise stop execution
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    // Return an empty response or error message if unauthorized
    // http_response_code(403); // Forbidden
    // echo "<tr><td colspan='10' style='text-align: center; color: red;'>Unauthorized access.</td></tr>";
    exit(); // Stop script execution
}

// Include your database connection file
require 'db.php'; // Adjust the path if necessary

// --- Construct the SQL Query for Incomplete Records ---
// Define which columns are considered essential for a complete record.
// UPDATED: Checking for incompleteness based on 'thesis_group_id' and 'thesis_title' (t.title)
$incomplete_columns = [
    'employment_status',
    'student_id',
    'last_name',
    'first_name',
    'college_and_course',
    'graduation_year',
    'thesis_group_id', // Check for missing Thesis Group ID
    'thesis_title' // Check for missing Thesis Title (corresponds to 'title' column in thesis table)
];

$sql = "SELECT
            a.employment_status,
            a.alumni_id,
            a.student_id,
            a.last_name,
            a.first_name,
            a.college_and_course,
            a.graduation_year,
            t.thesis_group_id, -- Select the thesis_group_id column
            t.title AS thesis_title -- Select the thesis title and alias it
        FROM
            alumni a
        LEFT JOIN
            thesis t ON a.alumni_id = t.alumni_id
        WHERE "; // Start the WHERE clause

// Build the conditions for incomplete records (where any of the specified columns are empty or null)
$conditions = [];
foreach ($incomplete_columns as $col) {
    // Check for both NULL and empty string '' for columns
    if ($col === 'thesis_group_id') {
         // For thesis_group_id, check if the LEFT JOIN resulted in NULL OR if the field is an empty string
         $conditions[] = "t.thesis_group_id IS NULL OR t.thesis_group_id = ''";
    } elseif ($col === 'thesis_title') {
         // For thesis_title (which is t.title), check if NULL or empty string
         $conditions[] = "t.title IS NULL OR t.title = ''";
    }
    else {
         // For alumni table fields, check if they are NULL or empty string
         $conditions[] = "a." . $col . " IS NULL OR a." . $col . " = ''";
    }

}

// Combine conditions with OR
$sql .= implode(" OR ", $conditions);

// Add ordering if desired (e.g., order by last name or college_and_course)
$sql .= " ORDER BY a.last_name, a.first_name";


// --- Execute the Query ---
$result = $conn->query($sql);

if ($result === false) {
    // Handle query error
    error_log("Error fetching incomplete alumni: (" . $conn->errno . ") " . $conn->error);
    echo '<tr><td colspan="10" style="text-align: center; color: red;">Database error loading incomplete records.</td></tr>';
    $conn->close();
    exit();
}

// --- Generate HTML Table Rows ---
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Determine employment status class for the dot (same logic as main table)
        $status = htmlspecialchars($row['employment_status'] ?? ''); // Use null coalescing for safety
        $dotClass = 'not-employed'; // Default to red
        if (strcasecmp($status, 'Employed') == 0 || strcasecmp($status, 'Full-time') == 0 || strcasecmp($status, 'Part-time') == 0 || strcasecmp($status, 'Freelance') == 0) {
            $dotClass = 'employed'; // Green for employed statuses
        }
        // Add more conditions here if you have other statuses that should be green

        echo "<tr>";
        // Employment Status (Dot)
        echo "<td style='text-align: center;'><span class='status-indicator-dot {$dotClass}'></span>" . $status . "</td>";
        // Alumni ID
        echo "<td>" . htmlspecialchars($row['alumni_id'] ?? '') . "</td>";
        // Student ID
        echo "<td>" . htmlspecialchars($row['student_id'] ?? '') . "</td>";
        // Last Name
        echo "<td>" . htmlspecialchars($row['last_name'] ?? '') . "</td>";
        // First Name
        echo "<td>" . htmlspecialchars($row['first_name'] ?? '') . "</td>";
        // College & Course (Combined)
        echo "<td>" . htmlspecialchars($row['college_and_course'] ?? '') . "</td>";
        // Graduation Year
        echo "<td>" . htmlspecialchars($row['graduation_year'] ?? '') . "</td>";
        // Thesis Group ID (Displaying the new column)
        echo "<td>" . htmlspecialchars($row['thesis_group_id'] ?? '') . "</td>"; // Use the thesis_group_id column
        // Thesis Title
        echo "<td>" . htmlspecialchars($row['thesis_title'] ?? '') . "</td>"; // Use the aliased name for displaying
        // Action (Delete Button) - Always visible for Admin
        // Pass alumni_id to the delete button
        echo "<td><span class='delete-btn' data-id='" . htmlspecialchars($row['alumni_id'] ?? '') . "'>&#128465;</span></td>"; // Using trash icon
        echo "</tr>";
    }
} else {
    // No incomplete records found
    echo '<tr><td colspan="10" style="text-align: center;">No incomplete records found.</td></tr>';
}

$conn->close(); // Close the database connection
?>
