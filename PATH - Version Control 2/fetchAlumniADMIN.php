<?php
// FILE: fetchAlumniADMIN.php
session_start(); // Start the session

// Check if user is logged in AND is an Admin, otherwise stop execution
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    // You might want to return an empty response or error message here
    // http_response_code(403); // Forbidden
    // echo "Unauthorized access.";
    exit(); // Stop script execution
}

// Include your database connection file
require 'db.php'; // Adjust the path if necessary

// Get filter values from the AJAX POST request
$name = $_POST['name'] ?? '';
$status = $_POST['status'] ?? '';
// Expecting a single value from the dropdown for 'program'
$program = $_POST['program'] ?? '';
$year = $_POST['year'] ?? '';
$thesisGroupId = isset($_POST['thesisGroupId']) ? trim($_POST['thesisGroupId']) : '';

// --- Construct the SQL Query with Filters ---
$sql = "SELECT
            a.employment_status,
            a.alumni_id,
            a.student_id,
            a.last_name,
            a.first_name,
            a.college_and_course, -- Select the combined column
            a.graduation_year,
            t.thesis_group_id, -- Select the thesis_group_id column
            t.title AS thesis_title -- Select the thesis title
        FROM
            alumni a
        LEFT JOIN
            thesis t ON a.alumni_id = t.alumni_id
        WHERE 1=1"; // Start with a true condition to easily append filters

// Append filters based on provided input
if (!empty($name)) {
    // Search in first name, last name, or both
    $sql .= " AND (a.first_name LIKE ? OR a.last_name LIKE ?)";
    $name_param = "%" . $conn->real_escape_string($name) . "%";
}
if (!empty($status)) {
    $sql .= " AND a.employment_status = ?";
    $status_param = $conn->real_escape_string($status);
}
if (!empty($program)) {
    // Match the exact combined college and course string from the dropdown
    // Using the actual column name and exact match
    $sql .= " AND a.college_and_course = ?";
    $program_param = $conn->real_escape_string($program);
}
if (!empty($year)) {
    // Assuming graduation_year is a YEAR or INT type
    $sql .= " AND a.graduation_year = ?";
    $year_param = intval($year); // Ensure year is an integer
}
if ($thesisGroupId !== '') {
    $sql .= " AND t.thesis_group_id LIKE ?";
    $thesisGroupId_param = "%" . $conn->real_escape_string($thesisGroupId) . "%";
}

// --- Prepare and Execute the Statement (Using prepared statements is safer) ---
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Handle prepare error
    error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    // Output a generic error message to the client
    echo '<tr><td colspan="10" style="text-align: center; color: red;">Database error preparing query.</td></tr>';
    $conn->close();
    exit();
}

// Bind parameters if they exist
$param_types = '';
$params = [];

if (!empty($name)) {
    $param_types .= 'ss';
    $params[] = &$name_param;
    $params[] = &$name_param; // Bind twice for both LIKE conditions
}
if (!empty($status)) {
    $param_types .= 's';
    $params[] = &$status_param;
}
if (!empty($program)) {
    $param_types .= 's'; // Program is now an exact string match
    $params[] = &$program_param;
}
if (!empty($year)) {
    $param_types .= 'i'; // 'i' for integer
    $params[] = &$year_param;
}
if ($thesisGroupId !== '') {
    $param_types .= 's';
    $params[] = &$thesisGroupId_param;
}

// Only bind parameters if there are any
if (!empty($params)) {
    // Use call_user_func_array to bind parameters dynamically
    call_user_func_array([$stmt, 'bind_param'], array_merge([$param_types], $params));
}

$stmt->execute();
$result = $stmt->get_result(); // Get the result set

// --- Generate HTML Table Rows ---
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Determine employment status class for the dot
        // Use null coalescing operator (?? '') for safety in case column is null
        $status = htmlspecialchars($row['employment_status'] ?? '');
        $dotClass = 'not-employed'; // Default to red
        if (strcasecmp($status, 'Employed') == 0 || strcasecmp($status, 'Full-time') == 0 || strcasecmp($status, 'Part-time') == 0 || strcasecmp($status, 'Freelance') == 0) {
            $dotClass = 'employed'; // Green for employed statuses
        }
        // Add more conditions here if you have other statuses that should be green

        // Add data-id attribute and clickable-row class to the table row
        echo "<tr class='clickable-row' data-id='" . htmlspecialchars($row['alumni_id'] ?? '') . "'>";
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
        echo "<td>" . htmlspecialchars($row['college_and_course'] ?? '') . "</td>"; // Use the combined column
        // Graduation Year
        echo "<td>" . htmlspecialchars($row['graduation_year'] ?? '') . "</td>";
        // Thesis Group ID (Displaying the new column)
        echo "<td>" . htmlspecialchars($row['thesis_group_id'] ?? '') . "</td>"; // Use the thesis_group_id column
        // Thesis Title
        echo "<td>" . htmlspecialchars($row['thesis_title'] ?? '') . "</td>"; // Use the aliased name for displaying
        // Action (Delete Button) - Always visible for Admin
        // Pass alumni_id to the delete button
        // Note: The delete button is still here, clicking the row redirects, clicking the icon triggers delete.
        echo "<td><span class='delete-btn' data-id='" . htmlspecialchars($row['alumni_id'] ?? '') . "'>&#128465;</span></td>"; // Using trash icon
        echo "</tr>";
    }
} else {
    // No results found
    echo '<tr><td colspan="10" style="text-align: center;">No results found.</td></tr>';
}

$stmt->close();
$conn->close(); // Close the database connection
?>
