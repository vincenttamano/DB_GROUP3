<?php
session_start();
require 'db.php'; // Make sure this connects to your database

$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
$current_year = date('Y');

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_faculty_id'])) {
    $faculty_id = $_POST['delete_faculty_id'];
    $stmt = $conn->prepare("DELETE FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("s", $faculty_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch faculty records
$sql = "SELECT faculty_id, email FROM faculty";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Faculty | PATH Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f0f0f0;
            padding-top: 60px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #1f4c73;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            box-sizing: border-box;
            z-index: 1000;
        }
        .navbar-left { display: flex; align-items: center; }
        .navbar-logo-placeholder {
            width: 40px; height: 40px; background-color: #ddd; border-radius: 5px;
            margin-right: 15px; display: flex; align-items: center; justify-content: center;
            font-size: 0.7em; color: #555;
        }
        .navbar-logo-placeholder img {
            width: 100%; height: 100%; object-fit: contain; display: block;
        }
        .navbar-title { font-size: 1.2em; font-weight: bold; }
        .navbar-right { display: flex; align-items: center; }
        .navbar-link {
            color: white; text-decoration: none; margin-left: 20px; font-size: 1em;
            transition: color 0.3s ease;
        }
        .navbar-link:hover { color: #a0c0d0; }
        /* Dropdown styles */
        .dropdown {
            position: relative;
            margin-left: 20px;
        }
        .dropdown-btn {
            background-color: transparent;
            color: white;
            padding: 0;
            font-size: 1em;
            border: none;
            cursor: pointer;
            outline: none;
            transition: color 0.3s ease;
            text-decoration: underline;
        }
        .dropdown-btn:hover {
            color: #a0c0d0;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f8f8f8;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 8px;
            overflow: hidden;
            top: 100%;
            left: 0;
        }
        .dropdown-content a {
            color: #337ab7;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background 0.2s;
        }
        .dropdown-content a:hover {
            background-color: #e6f0fa;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .main-content {
            flex: 1 0 auto;
            margin: 0 auto;
            width: 90%;
            max-width: 700px;
            margin-top: 40px;
        }
        .page-title {
            font-size: 2em;
            color: #337ab7;
            margin: 30px 0 20px 0;
            text-align: center;
        }
        #facultyTable {
            border-collapse: collapse; width: 100%; background: #fff; border-radius: 8px; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.07);
        }
        #facultyTable th, #facultyTable td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
        }
        #facultyTable th {
            background: #337ab7; color: #fff; font-weight: bold; letter-spacing: 0.5px; text-align: center;
        }
        #facultyTable tr:last-child td { border-bottom: none; }
        #facultyTable tr:hover { background: #f1f7ff; }
        .delete-btn {
            background: #d9534f;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 6px 14px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.2s;
        }
        .delete-btn:hover {
            background: #b52b27;
        }
        .user-status-box {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #337ab7;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            font-size: 0.9em;
            z-index: 1001;
            text-align: center;
            white-space: nowrap;
        }
        /* --- Footer Style --- */
        footer {
            background-color: #1f4c73;
            color: white;
            text-align: center;
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
            position: static;
            flex-shrink: 0;
        }
        .footer-social-icons a {
            color: white;
            font-size: 1.5em;
            margin: 0 10px;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer-social-icons a:hover {
            color: #a0c0d0;
        }
        .footer-text {
            margin-top: 10px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <div class="navbar-logo-placeholder">
                <img src="collegeLogos/finaldbLOGOOO.png" alt="PATH Logo">
            </div>
            <div class="navbar-title">PATH</div>
        </div>
        <div class="navbar-right">
            <a href="homePage.php#top" class="navbar-link">HOME</a>
            <div class="dropdown">
                <button class="dropdown-btn">PATH CONTROLS</button>
                <div class="dropdown-content">
                    <a href="adminDashboard.php">Admin Dashboard</a>
                    <a href="filterSearchAlumniADMIN.php">Search Alumni</a>
                    <a href="searchFacultyADMIN.php">Search Faculty</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <a href="homePage.php#about-us" class="navbar-link">ABOUT</a>
        </div>
    </div>
    <div class="main-content">
        <div class="page-title">Faculty Records</div>
        <table id="facultyTable">
            <thead>
                <tr>
                    <th>Faculty ID</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['faculty_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <form method="post" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this faculty record?');">
                                    <input type="hidden" name="delete_faculty_id" value="<?php echo htmlspecialchars($row['faculty_id']); ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">No faculty records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="user-status-box">
        Connected as: <?php echo htmlspecialchars($user_type); ?> / <?php echo htmlspecialchars($user_id); ?>
    </div>

    <footer>
        <div class="footer-social-icons">
            <a href="https://mail.google.com/mail" target="_blank" title="Gmail"><i class="fas fa-envelope"></i></a>
            <a href="https://www.facebook.com/fritzmarick.fernandez" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="https://www.instagram.com/_kciram?igsh=MWhyaGtqOGYydGh2eA==" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://github.com/Maricklabs" target="_blank" title="GitHub"><i class="fab fa-github"></i></a>
        </div>
        <div class="footer-text">
            &copy; <?php echo $current_year; ?> West Visayas State University - PATH. All rights reserved.
        </div>
    </footer>
</body>
</html>