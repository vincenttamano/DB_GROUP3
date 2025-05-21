<?php
require 'db.php';

$name = $_POST['name'] ?? '';
$schoolId = $_POST['schoolId'] ?? '';
$program = $_POST['program'] ?? '';
$year = $_POST['year'] ?? '';
$thesisGroupId = $_POST['thesisGroupId'] ?? '';

$sql = "SELECT alumni.*, thesis.thesis_group_id, thesis.title AS thesis_title
        FROM alumni
        LEFT JOIN thesis ON alumni.alumni_id = thesis.alumni_id
        WHERE 
            alumni.employment_status IS NOT NULL AND alumni.employment_status != '' AND
            alumni.last_name != '' AND alumni.first_name != '' AND
            alumni.college_and_course != '' AND
            alumni.graduation_year IS NOT NULL";

$params = [];
$types = "";

if ($name !== '') {
    $sql .= " AND (alumni.first_name LIKE ? OR alumni.last_name LIKE ?)";
    $params[] = "%$name%";
    $params[] = "%$name%";
    $types .= "ss";
}
if ($schoolId !== '') {
    $sql .= " AND alumni.school_id LIKE ?";
    $params[] = "%$schoolId%";
    $types .= "s";
}
if ($program !== '') {
    $sql .= " AND alumni.college_and_course LIKE ?";
    $params[] = "%$program%";
    $types .= "s";
}
if ($year !== '') {
    $sql .= " AND alumni.graduation_year LIKE ?";
    $params[] = "%$year%";
    $types .= "s";
}
if ($thesisGroupId !== '') {
    $sql .= " AND thesis.thesis_group_id LIKE ?";
    $params[] = "%$thesisGroupId%";
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Green dot for Employed, red for others
    $status = trim(strtolower($row['employment_status']));
    if ($status === 'employed') {
        $dot = '<span style="color:green;font-size:1.5em;">&#9679;</span>';
    } else {
        $dot = '<span style="color:red;font-size:1.5em;">&#9679;</span>';
    }

    echo "<tr>";
    echo "<td style='text-align:center;'>$dot " . htmlspecialchars($row['employment_status']) . "</td>";
    echo "<td>" . htmlspecialchars($row['alumni_id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['last_name'] . ', ' . $row['first_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['college_and_course']) . "</td>";
    echo "<td>" . htmlspecialchars($row['graduation_year']) . "</td>";
    echo "<td>" . htmlspecialchars($row['thesis_group_id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['thesis_title']) . "</td>";
    echo "</tr>";
}
$stmt->close();
$conn->close();