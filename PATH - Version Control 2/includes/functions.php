<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include DB connection
require_once __DIR__ . '/../db.php';

/**
 * Validate if an email ends with @wvsu.edu.ph
 */
function isValidFacultyEmail($email) {
    return preg_match("/^[^@]+@wvsu\\.edu\\.ph$/", $email);
}

/**
 * Validate password (min 8 chars, upper, lower, digit)
 */
function isValidPassword($password) {
    return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).{8,}$/", $password);
}

/**
 * Validate school ID format like 2024M1120
 */
function isValidSchoolID($school_id) {
    return preg_match("/^\\d{4}[A-Z]\\d{4}$/", $school_id);
}

/**
 * Generate next unique ID for a table
 * Example: AlumniID001, FacultyID001
 */
function generateCustomID($prefix, $table, $conn) {
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $nextId = $row['count'] + 1;
    return $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);
}

/**
 * Redirect to homepage if user already logged in
 */
function redirectIfLoggedIn() {
    if (isset($_SESSION['user_type'])) {
        header("Location: homePage.php");
        exit();
    }
}

/**
 * Enforce login (for protected pages)
 */
function requireLogin() {
    if (!isset($_SESSION['user_type'])) {
        header("Location: rolePage.php");
        exit();
    }
}
