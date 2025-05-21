<?php
// FILE: editOwnAlumniRecord.php
session_start(); // Start the session

// Check if user is logged in AND is Alumni, otherwise redirect
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Alumni') {
    // Redirect to rolePage.php or a specific unauthorized access page
    header("Location: rolePage.php");
    exit();
}

// Get user information from session
$user_type = $_SESSION['user_type']; // Should be "Alumni"
$user_id = $_SESSION['user_id']; // This is the alumni_id

// Get current year for copyright footer
$current_year = date("Y");

require 'db.php'; // Ensure db.php establishes $conn

// Handle form submission for alumni details
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's the alumni detail update form
    if (isset($_POST['last_name'])) {
        $last_name = trim($_POST['last_name']);
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name']);
        $email = trim($_POST['email']);
        $employment_status = trim($_POST['employment_status']);
        $college_and_course = trim($_POST['college_and_course']);
        $graduation_year = trim($_POST['graduation_year']);

        $stmt = $conn->prepare("UPDATE alumni SET last_name=?, first_name=?, middle_name=?, email=?, employment_status=?, college_and_course=?, graduation_year=? WHERE alumni_id=?");
        if ($stmt === false) {
            $error = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("ssssssss", $last_name, $first_name, $middle_name, $email, $employment_status, $college_and_course, $graduation_year, $user_id);
            if ($stmt->execute()) {
                $success = "Your information has been updated.";
            } else {
                $error = "Update failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch current alumni data
$alumni = null;
$stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id=?");
if ($stmt === false) {
    echo "Prepare failed: " . $conn->error;
} else {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result_alumni = $stmt->get_result();
    $alumni = $result_alumni->fetch_assoc();
    $result_alumni->free(); // Free the result set
    $stmt->close();
}


// Fetch current work experience
$current_work = null;
$stmt = $conn->prepare("SELECT * FROM work_exp_current WHERE alumni_id=?");
if ($stmt === false) {
    echo "Prepare failed: " . $conn->error;
} else {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result_current_work = $stmt->get_result();
    $current_work = $result_current_work->fetch_assoc();
    $result_current_work->free(); // Free the result set
    $stmt->close();
}


// Fetch previous work experience
$previous_work = [];
$stmt = $conn->prepare("SELECT * FROM work_exp_previous WHERE alumni_id=?");
if ($stmt === false) {
    echo "Prepare failed: " . $conn->error;
} else {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result_previous_work = $stmt->get_result();
    while ($row = $result_previous_work->fetch_assoc()) {
        $previous_work[] = $row;
    }
    $result_previous_work->free(); // Free the result set after fetching all
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Alumni Dashboard | PATH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Your existing CSS here */
        /* --- General Body and Layout Styles (Copied from other pages) --- */
        body {
            font-family: sans-serif;
            margin: 0; /* Remove default body margin */
            background-color: #f0f0f0; /* Light grey background for the entire page */
            padding-top: 60px; /* Add padding to the top to prevent content from being hidden by the fixed navbar */
            padding-bottom: 80px; /* Add padding to the bottom for the footer */
            position: relative; /* Needed for footer positioning if not using flex on body */
            min-height: 100vh; /* Ensure body is at least viewport height */
        }

        /* --- Navbar Style (Copied from other pages) --- */
        .navbar {
            background-color: #1f4c73; /* Dark blue */
            padding: 10px 20px;
            display: flex;
            justify-content: space-between; /* Distribute items */
            align-items: center; /* Vertically align items */
            color: white;
            position: fixed; /* Fix navbar at the top */
            top: 0;
            left: 0;
            width: 100%;
            box-sizing: border-box; /* Include padding in width */
            z-index: 1000; /* Ensure navbar is on top of other content */
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .navbar-logo-placeholder {
            width: 40px; /* Size of the logo placeholder */
            height: 40px;
            background-color: #ddd; /* Placeholder color */
            border-radius: 5px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            color: #555;
        }

        .navbar-title {
            font-size: 1.2em;
            font-weight: bold;
        }

        .navbar-right {
            display: flex;
            align-items: center;
        }

        .navbar-link {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 1em;
            transition: color 0.3s ease;
        }

        .navbar-link:hover {
            color: #a0c0d0; /* Lighter blue on hover */
        }

        /* --- Dropdown Style (Copied from other pages) --- */
        .dropdown {
            position: relative; /* Needed for positioning the dropdown content */
            display: inline-block; /* Allow dropdown next to other links */
            margin-left: 20px; /* Space from the previous link */
        }

        .dropdown-btn {
            background-color: transparent; /* No background */
            color: white;
            padding: 0; /* Remove default padding */
            font-size: 1em;
            border: none;
            cursor: pointer;
            outline: none; /* Remove outline on focus */
            transition: color 0.3s ease;
            text-decoration: underline; /* Underline the text */
        }

           .dropdown-btn:hover {
            color: #a0c0d0; /* Lighter blue on hover */
        }

        .dropdown-content {
            display: none; /* Hide dropdown content by default */
            position: absolute; /* Position relative to the dropdown container */
            background-color: #f8f8f8; /* Off-white background */
            min-width: 200px; /* Minimum width of the dropdown */
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1; /* Ensure it's above other content */
            border-radius: 8px;
            overflow: hidden; /* Hide overflow for rounded corners */
            top: 100%; /* Position below the button */
            left: 0; /* Align to the left of the button */
        }

        .dropdown-content a {
            color: #337ab7; /* Blue text color for links */
            padding: 12px 16px;
            text-decoration: none;
            display: block; /* Make links block level */
            text-align: left; /* Align text to the left */
            transition: background-color 0.3s ease;
        }

        .dropdown-content a:hover {
            background-color: #ddd; /* Light grey on hover */
        }

        .dropdown:hover .dropdown-content {
            display: block; /* Show dropdown content on hover */
        }

        /* --- Main Content Style --- */
        .main-content {
            background-color: #f8f8f8;
            padding: 40px 0 60px 0;
            margin: 30px auto 0 auto;
            max-width: 1000px;
            border-radius: 14px;
            box-shadow: 0 6px 24px rgba(31,76,115,0.10), 0 1.5px 4px rgba(31,76,115,0.08);
            min-height: 400px;
        }

        /* --- User Status Box Style (Copied from other pages) --- */
        .user-status-box {
            position: fixed; /* Keep it fixed on the screen */
            bottom: 20px; /* 20px from the bottom */
            right: 20px; /* 20px from the right */
            background-color: #337ab7; /* Blue background */
            color: white; /* White text */
            padding: 10px 15px; /* Padding inside the box (adjust for landscape shape) */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            font-size: 0.9em; /* Smaller font size */
            z-index: 1001; /* Ensure it's above the navbar */
            text-align: center; /* Center the text */
            white-space: nowrap; /* Prevent text from wrapping */
        }

        /* --- Footer Style (Copied from other pages) --- */
        footer {
            background-color: #1f4c73; /* Dark blue, same as navbar */
            color: white;
            text-align: center;
            padding: 20px;
            position: absolute; /* Position relative to the body */
            bottom: 0;
            left: 0;
            width: 100%;
            box-sizing: border-box;
        }

        .footer-social-icons a {
            color: white;
            font-size: 1.5em; /* Adjust icon size */
            margin: 0 10px; /* Space between icons */
            text-decoration: none; /* Remove underline */
            transition: color 0.3s ease;
        }

        .footer-social-icons a:hover {
            color: #a0c0d0; /* Lighter blue on hover */
        }

        .footer-text {
            margin-top: 10px;
            font-size: 0.9em;
        }

        /* --- Specific Styles for this page --- */
        .page-title {
            color: #337ab7;
            font-size: 2em;
            margin-bottom: 24px;
            text-align: center;
            letter-spacing: 1px;
        }

        .edit-form {
            background: #fff;
            padding: 32px 28px 60px 28px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(31,76,115,0.08);
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        .edit-form h3 {
            margin-top: 0;
            margin-bottom: 18px;
            color: #1f4c73;
            font-size: 1.3em;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .edit-form label {
            display: block;
            margin-bottom: 6px;
            color: #337ab7;
            font-weight: 600;
            letter-spacing: 0.2px;
            text-align: left;
        }
        .edit-form input,
        .edit-form select {
            width: 96%;           /* Prevents overflow, leaves a little padding on the sides */
            box-sizing: border-box;
            padding: 13px 15px;
            margin-bottom: 18px;
            border: 1.5px solid #b6c6d6;
            border-radius: 6px;
            font-size: 1.08em;
            background: #f7fafc;
            transition: border 0.2s, box-shadow 0.2s;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .edit-form input:focus, .edit-form select:focus {
            border: 1.5px solid #337ab7;
            outline: none;
            background: #fff;
            box-shadow: 0 0 0 2px #cbe5ff;
        }
        .edit-form button {
            background: linear-gradient(90deg, #337ab7 60%, #1f4c73 100%);
            color: #fff;
            border: none;
            padding: 13px;
            border-radius: 6px;
            font-size: 1.08em;
            font-weight: bold;
            cursor: pointer;
            width: 70%;           /* Make the button narrower */
            margin: 18px auto 0 auto; /* Center the button and add top margin */
            display: block;        /* Center using margin auto */
            box-shadow: 0 2px 8px rgba(31,76,115,0.08);
            transition: background 0.2s;
        }
        .edit-form button:hover {
            background: linear-gradient(90deg, #1f4c73 60%, #337ab7 100%);
        }
        .form-notification {
            position: absolute;
            left: 0; right: 0; bottom: 10px;
            margin: 0 auto;
            width: 90%;
            text-align: center;
            padding: 10px 0;
            border-radius: 6px;
            font-weight: 500;
            font-size: 1em;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s;
            z-index: 2;
        }
        .form-notification.success {
            background: #eafaf1;
            color: #1ca94c;
            border: 1px solid #b6e7c9;
            opacity: 1;
        }
        .form-notification.error {
            background: #fdeaea;
            color: #d32f2f;
            border: 1px solid #f5bcbc;
            opacity: 1;
        }

        /* Table styles for previous work experience */
        .edit-form table {
            border-collapse: collapse;
            width: 100%;
        }
        .edit-form th, .edit-form td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 0.9em;
        }
        .edit-form th {
            background-color: #f2f2f2;
            color: #1f4c73;
        }
        .edit-form td input {
            width: calc(100% - 16px); /* Adjust width to fit within cell, accounting for padding */
            padding: 6px 8px;
            margin: 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .edit-form td .removePrevBtn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8em;
            width: auto;
            margin: 0; /* Override default button margin */
            display: inline-block; /* Allow it to sit in the table cell */
        }
        .edit-form td .removePrevBtn:hover {
            background-color: #c82333;
        }
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
                        <a href="alumniDashboard.php">Alumni Dashboard</a> <?php endif; ?>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

            <a href="homePage.php#about-us" class="navbar-link">ABOUT</a> </div>
    </div>

    <div class="main-content">
        <div class="page-title">Alumni Dashboard</div>

        <div class="edit-form">
            <h3>Edit Your Information</h3>
            <form method="post" autocomplete="off" id="alumniEditForm">
                <label for="last_name" style="padding:10px;">Last Name</label>
                <input type="text" name="last_name" required value="<?php echo htmlspecialchars($alumni['last_name'] ?? ''); ?>">

                <label for="first_name" style="padding:10px;">First Name</label>
                <input type="text" name="first_name" required value="<?php echo htmlspecialchars($alumni['first_name'] ?? ''); ?>">

                <label for="middle_name" style="padding:10px;">Middle Name</label>
                <input type="text" name="middle_name" value="<?php echo htmlspecialchars($alumni['middle_name'] ?? ''); ?>">

                <label for="email" style="padding:10px;">Email</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($alumni['email'] ?? ''); ?>">

                <label for="employment_status" style="padding:10px;">Employment Status</label>
                <select name="employment_status">
                    <option value="Employed" <?php if(isset($alumni['employment_status']) && $alumni['employment_status']=="Employed") echo "selected"; ?>>Employed</option>
                    <option value="Unemployed" <?php if(isset($alumni['employment_status']) && $alumni['employment_status']=="Unemployed") echo "selected"; ?>>Unemployed</option>
                </select>

                <label for="college_and_course" style="padding:10px;">Program</label>
                <input type="text" name="college_and_course" value="<?php echo htmlspecialchars($alumni['college_and_course'] ?? ''); ?>">

                <label for="graduation_year" style="padding:10px;">Graduation Year</label>
                <input type="text" name="graduation_year" value="<?php echo htmlspecialchars($alumni['graduation_year'] ?? ''); ?>">

                <button type="submit" style="width: 200px">Save Changes</button>
            </form>
            <div id="formNotification" class="form-notification"></div>
        </div>

        <div class="edit-form" style="margin-top:30px;">
            <h3>Current Work Experience</h3>
            <form id="currentWorkForm">
                <label for="current_profession">Profession</label>
                <input type="text" name="current_profession" value="<?php echo htmlspecialchars($current_work['current_profession'] ?? ''); ?>">
                <label for="current_work_desc">Description</label>
                <input type="text" name="current_work_desc" value="<?php echo htmlspecialchars($current_work['current_work_desc'] ?? ''); ?>">
                <button type="submit" style="width: 200px">Save Current Work</button>
            </form>
            <div id="currentWorkNotif" class="form-notification"></div>
        </div>

        <div class="edit-form" style="margin-top:30px;">
            <h3>Previous Work Experience</h3>
            <form id="previousWorkForm">
                <table style="width:100%;margin-bottom:10px;">
                    <thead>
                        <tr>
                            <th>Profession</th>
                            <th>Description</th>
                            <th>Company</th>
                            <th>Date</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody id="prevWorkTableBody">
                        <?php foreach ($previous_work as $i => $exp): ?>
                        <tr>
                            <td><input type="text" name="previous_profession[]" value="<?php echo htmlspecialchars($exp['previous_profession']); ?>"></td>
                            <td><input type="text" name="previous_work_desc[]" value="<?php echo htmlspecialchars($exp['previous_work_desc']); ?>"></td>
                            <td><input type="text" name="previous_company[]" value="<?php echo htmlspecialchars($exp['company']); ?>"></td>
                            <td><input type="text" name="previous_work_date[]" value="<?php echo htmlspecialchars($exp['work_date']); ?>"></td>
                            <td><button type="button" class="removePrevBtn">Remove</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" id="addPrevWorkBtn" style="background:#337ab7;color:#fff; width: 200px">Add Previous Work</button>
                <button type="submit" style="width: 200px">Save Previous Work</button>
            </form>
            <div id="previousWorkNotif" class="form-notification"></div>
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
    document.getElementById('alumniEditForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.onload = function() {
            var notif = document.getElementById('formNotification');
            notif.className = 'form-notification';
            if (xhr.responseText.includes('Your information has been updated')) {
                notif.textContent = 'Your information has been updated.';
                notif.classList.add('success');
            } else if (xhr.responseText.includes('Update failed')) {
                notif.textContent = 'Update failed. Please try again.';
                notif.classList.add('error');
            } else {
                notif.textContent = '';
            }
            notif.style.opacity = '1';
            setTimeout(function() {
                notif.style.opacity = '0';
            }, 3000);
            if (xhr.responseText.includes('Your information has been updated')) {
                // No need to reload the whole page, just update the displayed values if necessary
                // Instead of location.reload(), you could update specific fields on the page.
                // For a full page update after main alumni data update, consider keeping the reload.
                setTimeout(function() { location.reload(); }, 1200);
            }
        };
        xhr.send(formData);
    });

    // --- Current Work Experience ---
    document.getElementById('currentWorkForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_own_work_exp.php', true);
        formData.append('type', 'current');
        xhr.onload = function() {
            var notif = document.getElementById('currentWorkNotif');
            notif.className = 'form-notification';
            if (xhr.responseText.includes('success')) {
                notif.textContent = 'Current work experience updated.';
                notif.classList.add('success');
            } else {
                notif.textContent = 'Update failed. Please try again. ' + xhr.responseText; // Added responseText for debugging
                notif.classList.add('error');
            }
            notif.style.opacity = '1';
            setTimeout(function() { notif.style.opacity = '0'; }, 3000);
            if (xhr.responseText.includes('success')) setTimeout(function(){ location.reload(); }, 1200);
        };
        xhr.send(formData);
    });

    // --- Previous Work Experience ---
    document.getElementById('previousWorkForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_own_work_exp.php', true);
        formData.append('type', 'previous');
        xhr.onload = function() {
            var notif = document.getElementById('previousWorkNotif');
            notif.className = 'form-notification';
            if (xhr.responseText.includes('success')) {
                notif.textContent = 'Previous work experience updated.';
                notif.classList.add('success');
            } else {
                notif.textContent = 'Update failed. Please try again. ' + xhr.responseText; // Added responseText for debugging
                notif.classList.add('error');
            }
            notif.style.opacity = '1';
            setTimeout(function() { notif.style.opacity = '0'; }, 3000);
            if (xhr.responseText.includes('success')) setTimeout(function(){ location.reload(); }, 1200);
        };
        xhr.send(formData);
    });

    // Add/Remove previous work rows
    document.getElementById('addPrevWorkBtn').addEventListener('click', function() {
        var tbody = document.getElementById('prevWorkTableBody');
        var tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="previous_profession[]"></td>
            <td><input type="text" name="previous_work_desc[]"></td>
            <td><input type="text" name="previous_company[]"></td>
            <td><input type="text" name="previous_work_date[]"></td>
            <td><button type="button" class="removePrevBtn">Remove</button></td>
        `;
        tbody.appendChild(tr);
    });
    document.getElementById('prevWorkTableBody').addEventListener('click', function(e) {
        if (e.target.classList.contains('removePrevBtn')) {
            e.target.closest('tr').remove();
        }
    });
    </script>

</body>
</html>