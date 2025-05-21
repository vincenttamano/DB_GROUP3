<?php
    session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Faculty') {
    header("Location: rolePage.php");
    exit();
}
$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];
$current_year = date("Y");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Faculty Search Alumni | PATH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: sans-serif;
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
        .navbar-title { font-size: 1.2em; font-weight: bold; }
        .navbar-right { display: flex; align-items: center; }
        .navbar-link {
            color: white; text-decoration: none; margin-left: 20px; font-size: 1em;
            transition: color 0.3s ease;
        }
        .navbar-link:hover { color: #a0c0d0; }
        .dropdown { position: relative; display: inline-block; margin-left: 20px; }
        .dropdown-btn {
            background-color: transparent; color: white; padding: 0; font-size: 1em;
            border: none; cursor: pointer; outline: none; transition: color 0.3s ease;
            text-decoration: underline;
        }
        .dropdown-btn:hover { color: #a0c0d0; }
        .dropdown-content {
            display: none; position: absolute; background-color: #f8f8f8; min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; border-radius: 8px;
            overflow: hidden; top: 100%; left: 0;
        }
        .dropdown-content a {
            color: #337ab7; padding: 12px 16px; text-decoration: none; display: block;
            text-align: left; transition: background-color 0.3s ease;
        }
        .dropdown-content a:hover { background-color: #ddd; }
        .dropdown:hover .dropdown-content { display: block; }
        .main-content {
            flex: 1 0 auto;
            background-color: #f8f8f8; padding: 20px; margin: 20px auto;
            max-width: 1200px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            min-height: 400px;
        }
        .user-status-box {
            position: fixed; bottom: 20px; right: 20px; background-color: #337ab7;
            color: white; padding: 10px 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            font-size: 0.9em; z-index: 1001; text-align: center; white-space: nowrap;
        }
        footer {
            background-color: #1f4c73;
            color: white;
            text-align: center;
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
            position: static; /* Make sure this is NOT absolute or fixed */
            flex-shrink: 0;
        }
        .footer-social-icons a {
            color: white; font-size: 1.5em; margin: 0 10px; text-decoration: none; transition: color 0.3s ease;
        }
        .footer-social-icons a:hover { color: #a0c0d0; }
        .footer-text { margin-top: 10px; font-size: 0.9em; }
        .page-title {
            color: #337ab7; font-size: 2em; margin-bottom: 20px; text-align: center;
        }
        .filter-form {
            margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;
        }
        .filter-form input, .filter-form select {
            padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 1em;
        }
        .filter-form button {
            padding: 8px 18px; background: #337ab7; color: #fff; border: none; border-radius: 5px;
            font-size: 1em; cursor: pointer; transition: background 0.3s;
        }
        .filter-form button:hover { background: #1f4c73; }
        #resultsArea { margin-top: 20px; }
        #alumniTable {
            border-collapse: collapse; width: 100%; background: #fff; border-radius: 8px; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.07);
        }
        #alumniTable th, #alumniTable td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            text-align: center; /* <-- Center text */
        }
        #alumniTable th {
            background: #337ab7; color: #fff; font-weight: bold; letter-spacing: 0.5px; text-align: center;
        }
        #alumniTable tr:last-child td { border-bottom: none; }
        #alumniTable tr:hover { background: #f1f7ff; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="navbar-left">
            <div class="navbar-logo-placeholder">
                <img src="collegeLogos/finaldbLOGOOO.png" alt="PATH Logo" style="width:100%; height:100%; object-fit:contain; display:block;">
            </div>
            <div class="navbar-title">PATH</div>
        </div>
        <div class="navbar-right">
            <a href="homePage.php#top" class="navbar-link">HOME</a>
            <div class="dropdown">
                <button class="dropdown-btn">PATH CONTROLS</button>
                <div class="dropdown-content">
                    <?php if ($user_type == "Admin"): ?>
                        <a href="adminDashboard.php">Admin Dashboard</a>
                        <a href="filterSearchAlumniADMIN.php">Search Alumni</a>
                    <?php elseif ($user_type == "Faculty"): ?>
                        <a href="filterSearchAlumniFACULTY.php">Search Alumni</a>
                    <?php elseif ($user_type == "Alumni"): ?>
                        <a href="editOwnAlumniRecord.php">Edit Your Record</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <a href="homePage.php#about-us" class="navbar-link">ABOUT</a>
        </div>
    </div>

    <div class="main-content">
        <div class="page-title">Faculty - Search Alumni</div>

        <div class="filter-form">
            <input type="text" id="searchName" placeholder="Search Name">
            <input type="text" id="studentIdFilter" placeholder="Student ID">
            <input type="text" id="programFilter" placeholder="Program">
            <input type="text" id="yearFilter" placeholder="Graduation Year">
            <input type="text" id="thesisGroupIdFilter" placeholder="Thesis Group ID">
        </div>

        <div id="resultsArea">
            <div id="resultsCount" style="font-weight:bold; color:#337ab7; margin-bottom:10px; text-align:center;"></div>
            <table id="alumniTable">
                <thead>
                    <tr>
                        <th>Employment Status</th>
                        <th>Alumni ID</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Program</th>
                        <th>Graduation Year</th>
                        <th>Thesis Group ID</th>
                        <th>Thesis Title</th>
                    </tr>
                </thead>
                <tbody id="alumniTableBody">
                    <!-- Results will be inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="user-status-box">
        Connected as: <?php echo htmlspecialchars($user_type); ?> / <?php echo htmlspecialchars($user_id); ?>
    </div>

    <footer>
        <div class="footer-social-icons">
            <a href="https://mail.google.com/" target="_blank" title="Gmail"><i class="fas fa-envelope"></i></a>
            <a href="https://www.facebook.com/fritzmarick.fernandez" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="https://www.instagram.com/_kciram?igsh=MWhyaGtqOGYydGh2eA==" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://github.com/Maricklabs" target="_blank" title="GitHub"><i class="fab fa-github"></i></a>
        </div>
        <div class="footer-text">
            &copy; <?php echo $current_year; ?> West Visayas State University - PATH. All rights reserved.
        </div>
    </footer>

    <script>
    function fetchData() {
        const name = document.getElementById('searchName').value;
        const studentId = document.getElementById('studentIdFilter').value;
        const program = document.getElementById('programFilter').value;
        const year = document.getElementById('yearFilter').value;
        const thesisGroupId = document.getElementById('thesisGroupIdFilter').value;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "fetchAlumniFACULTY.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            document.getElementById('alumniTableBody').innerHTML = xhr.responseText;

            // Count matching rows (excluding "No results" or "Loading" rows)
            const rows = document.querySelectorAll('#alumniTableBody tr');
            let count = 0;
            rows.forEach(row => {
                if (
                    !row.textContent.toLowerCase().includes('no results') &&
                    !row.textContent.toLowerCase().includes('loading')
                ) {
                    count++;
                }
            });
            document.getElementById('resultsCount').textContent = `Matching Results: ${count}`;
        };
        xhr.send(
            "name=" + encodeURIComponent(name) +
            "&studentId=" + encodeURIComponent(studentId) +
            "&program=" + encodeURIComponent(program) +
            "&year=" + encodeURIComponent(year) +
            "&thesisGroupId=" + encodeURIComponent(thesisGroupId)
        );
    }

    window.onload = fetchData;
    document.querySelectorAll('#searchName, #studentIdFilter, #programFilter, #yearFilter, #thesisGroupIdFilter').forEach(el => {
        el.addEventListener('input', fetchData);
    });
    </script>
</body>
</html>
