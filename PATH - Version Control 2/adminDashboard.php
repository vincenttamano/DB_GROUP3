<?php
// FILE: adminDashboard.php
session_start(); // Start the session

// Check if user is logged in AND is an Admin, otherwise redirect
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    // Redirect to rolePage.php or a specific unauthorized access page
    header("Location: rolePage.php");
    exit();
}

$user_type = $_SESSION['user_type']; // Should be "Admin"
$user_id = $_SESSION['user_id'];

// Get current year for copyright footer
$current_year = date("Y");

require 'db.php'; // Adjust the path if necessary

// --- PHP Logic for Fetching Programs for the Dropdown ---
// This block is no longer strictly needed on THIS page
// as the filter dropdown has been moved back to filterSearchAlumniADMIN.php
$programs_list = []; // Initialize an empty array
// $sql_programs = "SELECT DISTINCT college_and_course FROM alumni ORDER BY college_and_course";
// $result_programs = $conn->query($sql_programs);
// if ($result_programs === false) { error_log("Error fetching programs for dropdown: " . $conn->error); }
// else { while($row_programs = $result_programs->fetch_assoc()) { if (!empty($row_programs['college_and_course'])) { $programs_list[] = htmlspecialchars($row_programs['college_and_course']); } } $result_programs->free(); }
// $conn->close(); // Close the database connection

// Re-close the connection if it was opened, as we won't use it here anymore
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
// --- End PHP Logic for Fetching Programs ---

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard | PATH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- General Body and Layout Styles --- */
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #f0f0f0;
            padding-top: 60px;
            padding-bottom: 80px;
            position: relative;
            min-height: 100vh;
        }

        /* --- Navbar Style --- */
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

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .navbar-logo-placeholder {
            width: 40px;
            height: 40px;
            background-color: #ddd;
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
            color: #a0c0d0;
        }

        /* --- Dropdown Style --- */
        .dropdown {
            position: relative;
            display: inline-block;
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
            text-align: left;
            transition: background-color 0.3s ease;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* --- Main Content Style --- */
        .main-content {
            background-color: #f8f8f8;
            padding: 20px;
            margin: 20px auto;
            max-width: 1200px; /* Increased max-width for content */
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            min-height: 400px;
        }

        /* --- User Status Box Style --- */
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
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            box-sizing: border-box;
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

        /* --- Specific Styles for this page --- */
        .page-title {
            color: #337ab7;
            font-size: 2em;
            margin-bottom: 20px;
            text-align: center;
        }

        /* --- Tab Styles --- */
        .tabs {
            display: flex;
            flex-wrap: wrap; /* Allow tabs to wrap on smaller screens */
            justify-content: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .tab-button {
            background-color: transparent;
            border: none;
            padding: 10px 15px; /* Adjusted padding */
            cursor: pointer;
            font-size: 1em;
            color: #555;
            transition: color 0.3s ease, border-bottom 0.3s ease;
            margin: 0 5px;
            outline: none;
            border-bottom: 2px solid transparent;
            white-space: nowrap; /* Prevent button text from wrapping */
        }

        .tab-button:hover {
            color: #337ab7;
        }

        .tab-button.active {
            color: #1f4c73;
            border-bottom: 2px solid #1f4c73;
            font-weight: bold;
        }

        /* --- Tab Content Styles --- */
        .tab-content-container {
            padding-top: 20px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .tab-content h3 {
            color: #337ab7;
            margin-top: 0;
            margin-bottom: 15px;
            text-align: center;
        }

        .tab-content p {
             color: #555;
             line-height: 1.6;
             text-align: left; /* Default text align left */
        }

        /* Styles for Single Alumni View (Used in View Tab) */
        .single-alumnus-details {
             margin-top: 20px;
             padding: 20px;
             border: 1px solid #ccc;
             border-radius: 8px;
             background-color: #fff;
             text-align: left;
             max-width: 600px; /* Limit width for better readability */
             margin-left: auto;
             margin-right: auto;
        }

         .single-alumnus-details h4 {
             color: #1f4c73;
             margin-top: 0;
             margin-bottom: 15px;
             text-align: center;
         }

         .single-alumnus-details p {
             margin-bottom: 10px;
         }

         .single-alumnus-details strong {
             display: inline-block;
             width: 150px; /* Align labels */
             margin-right: 10px;
             color: #555;
         }

         .back-to-list-btn {
             display: inline-block;
             margin-top: 20px;
             padding: 8px 15px;
             background-color: #337ab7;
             color: white;
             border: none;
             border-radius: 5px;
             cursor: pointer;
             transition: background-color 0.3s ease;
         }

         .back-to-list-btn:hover {
             background-color:rgb(6, 70, 129);
         }

         /* Style for the message when no alumnus is selected */
         .no-alumnus-selected {
             text-align: center;
             color: #777;
             padding: 40px;
             border: 1px dashed #ccc;
             border-radius: 8px;
             background-color: #fcfcfc;
         }

         /* --- Form Styles (for Add/Update tabs) --- */
         .dashboard-form {
             display: flex;
             flex-direction: column;
             gap: 15px;
             max-width: 600px; /* Limit form width */
             margin: 20px auto; /* Center the form */
             padding: 20px;
             border: 1px solid #ccc;
             border-radius: 8px;
             background-color: #fff; /* White background for forms */
        }

        .dashboard-form label {
             font-weight: bold;
             color: #555;
        }

        .dashboard-form input[type="text"],
        .dashboard-form input[type="email"],
        .dashboard-form input[type="password"],
        .dashboard-form input[type="number"],
        .dashboard-form select,
        .dashboard-form textarea {
             padding: 10px;
             border: 1px solid #ccc;
             border-radius: 5px;
             font-size: 1em;
             width: 100%; /* Full width inputs */
             box-sizing: border-box; /* Include padding in width */
        }

         .dashboard-form button[type="submit"] {
             background-color: #337ab7;
             color: white;
             padding: 10px 20px;
             border: none;
             border-radius: 5px;
             font-size: 1em;
             cursor: pointer;
             transition: background-color 0.3s ease;
         }

         .dashboard-form button[type="submit"]:hover {
             background-color: #286090;
         }

         .form-message {
             margin-top: 15px;
             padding: 10px;
             border-radius: 5px;
             text-align: center;
         }

         .form-message.success {
             background-color: #d4edda;
             color: #155724;
             border-color: #c3e6cb;
         }

         .form-message.error {
             background-color: #f8d7da;
             color: #721c24;
             border-color: #f5c6cb;
         }

         /* Style for the search input in Update tab */
         .update-search-form {
             display: flex;
             gap: 10px;
             margin-bottom: 20px;
             justify-content: center;
         }

         .update-search-form input[type="text"] {
              padding: 8px;
              font-size: 14px;
              border: 1px solid #ccc;
              border-radius: 5px;
              height: 34px;
              box-sizing: border-box;
              flex-grow: 1; /* Allow input to grow */
              max-width: 300px; /* Max width for search input */
         }

         .update-search-form button {
              padding: 8px 15px;
              background-color: #337ab7;
              color: white;
              border: none;
              border-radius: 5px;
              cursor: pointer;
              transition: background-color 0.3s ease;
              height: 34px; /* Match input height */
         }

         .update-search-form button:hover {
             background-color: #286090;
         }

         /* New styles for the editable single alumnus form layout */
         .editable-alumnus-form {
             margin-top: 20px;
             padding: 20px;
             border: 1px solid #ccc;
             border-radius: 8px;
             background-color: #fff;
             text-align: left;
             max-width: 600px; /* Limit width */
             margin-left: auto;
             margin-right: auto;
             display: flex; /* Use flexbox for layout */
             flex-direction: column;
             gap: 15px; /* Space between form groups */
         }

         .form-group {
             display: flex;
             flex-direction: column; /* Stack label and input */
         }

         .form-group label {
             font-weight: bold;
             color: #555;
             margin-bottom: 5px; /* Space between label and input */
         }

         .form-group input[type="text"],
         .form-group input[type="email"],
         .form-group input[type="number"],
         .form-group select {
             padding: 10px;
             border: 1px solid #ccc;
             border-radius: 5px;
             font-size: 1em;
             width: 100%; /* Full width of the form group */
             box-sizing: border-box;
         }

         .form-actions {
             margin-top: 20px;
             text-align: center; /* Center buttons */
         }

         .form-actions button {
              padding: 10px 20px;
              border: none;
              border-radius: 5px;
              font-size: 1em;
              cursor: pointer;
              transition: background-color 0.3s ease;
         }

         .form-actions button[type="submit"] {
              background-color: #337ab7;
              color: white;
              margin-right: 10px; /* Space between buttons */
         }

         .form-actions button[type="submit"]:hover {
             background-color: #286090;
         }

          /* Style for the message when no alumnus is found for update */
         .update-alumnus-not-found {
             text-align: center;
             color: #c0392b;
             padding: 20px;
             border: 1px dashed #c0392b;
             border-radius: 8px;
             background-color: #f8d7da;
             max-width: 600px;
             margin: 20px auto;
         }

         .alumni-view-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    margin-bottom: 10px;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(51, 122, 183, 0.08);
}

.alumni-view-table th {
    background-color: #337ab7;
    color: #fff;
    text-align: center;
    padding: 10px 8px;
    font-weight: bold;
    font-size: 1em;
    border-bottom: 2px solid #286090;
}

.alumni-view-table td {
    padding: 8px 8px;
    border-bottom: 1px solid #e0e0e0;
    text-align: left;
    font-size: 0.98em;
}

.alumni-view-table tr:last-child td {
    border-bottom: none;
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
                        <a href="searchFacultyADMIN.php">Search Faculty</a>
                    <?php elseif ($user_type == "Faculty"): ?>
                        <a href="filterSearchAlumniFACULTY.php">Search Alumni</a>
                    <?php elseif ($user_type == "Alumni"): ?>
                        <a href="alumniDashboard.php">Alumni Dashboard</a>
                    <?php endif; ?>
                        <a href="logout.php">Logout</a>
                </div>
            </div>
            <a href="homePage.php#about-us" class="navbar-link">ABOUT</a>
        </div>
    </div>

    <div class="main-content">
        <div class="page-title">Admin Dashboard</div>

        <div class="tabs">
            <button class="tab-button active" data-target="view-alumni">View Alumni Information</button>
            <button class="tab-button" data-target="update-alumni">Update Alumni Information</button>
            <button class="tab-button" data-target="add-alumni">Add New Alumni Information</button>
        </div>

        <div class="tab-content-container">

            <div id="view-alumni" class="tab-content active">
                <h3>View Alumni Information</h3>

                <div id="noAlumnusSelectedMessage" class="no-alumnus-selected" style="display: none;">
                     <p style="text-align: center">Click on an alumnus record in the <a href="filterSearchAlumniADMIN.php">Search Alumni</a> page to view their details here.</p>
                </div>

                <div id="singleAlumnusView" class="single-alumnus-details" style="display: none;">
                    <h3>Alumnus Details</h3>
                    <div id="alumnusDetailsContent">
                        Loading details...
                    </div>

                    <button id="backToListBtn" class="back-to-list-btn">Back to Search Results</button>
                    <button id="editRecordBtn" class="back-to-list-btn" style="background-color:#337ab7;margin-right:10px;">Edit Record</button>
                </div>

            </div>

            <div id="update-alumni" class="tab-content">
                <h3>Update Alumni Information</h3>

                <div class="update-search-form">
                    <input type="text" id="updateSearchId" placeholder="Enter a Student ID or Alumni ID">
                    <button id="searchForUpdateBtn">Search</button>
                </div>

                <div id="updateAlumnusNotFound" class="update-alumnus-not-found" style="display: none;">Alumnus not found with the provided ID.</div>

                <form id="updateAlumniForm" class="editable-alumnus-form" style="display: none;">
                    <h4>Edit Alumni Record</h4>
                    <input type="hidden" id="updateAlumniId" name="alumni_id">

                    <div class="form-group">
                        <label for="updateStudentId">Student ID:</label>
                        <input type="text" id="updateStudentId" name="student_id" required>
                    </div>

                    <div class="form-group">
                        <label for="updateLastName">Last Name:</label>
                        <input type="text" id="updateLastName" name="last_name" required>
                    </div>

                    <div class="form-group">
                        <label for="updateFirstName">First Name:</label>
                        <input type="text" id="updateFirstName" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label for="updateMiddleName">Middle Name:</label>
                        <input type="text" id="updateMiddleName" name="middle_name">
                    </div>

                    <div class="form-group">
                        <label for="updateEmail">Email:</label>
                        <input type="email" id="updateEmail" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="updateEmploymentStatus">Employment Status:</label>
                        <select id="updateEmploymentStatus" name="employment_status" required>
                             <option value="">Select Status</option>
                             <option value="Employed">Employed</option>
                             <option value="Unemployed">Unemployed</option>
                             </select>
                    </div>

                    <div class="form-group">
                        <label for="updateCollegeAndCourse">College & Course:</label>
                        <input type="text" id="updateCollegeAndCourse" name="college_and_course" required>
                    </div>

                    <div class="form-group">
                        <label for="updateGraduationYear">Graduation Year:</label>
                        <input type="number" id="updateGraduationYear" name="graduation_year" required min="1900" max="3000" value="<?php echo date('Y'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="updateThesisGroupId">Thesis Group ID:</label>
                        <input type="text" id="updateThesisGroupId" name="thesis_group_id">
                    </div>

                    <div class="form-group">
                        <label for="updateThesisTitle">Thesis Title:</label>
                        <input type="text" id="updateThesisTitle" name="thesis_title">
                    </div>

                    <div class="form-group">
                        <label for="updateCurrentProfession">Current Profession:</label>
                        <input type="text" id="updateCurrentProfession" name="current_profession">
                    </div>
                    <div class="form-group">
                        <label for="updateCurrentWorkDesc">Current Work Description:</label>
                        <input type="text" id="updateCurrentWorkDesc" name="current_work_desc">
                    </div>
                    <div class="form-group">
                        <label>Previous Work Experience:</label>
                        <table id="previousWorkTable" class="alumni-view-table">
                            <thead>
                                <tr>
                                    <th>Profession</th>
                                    <th>Description</th>
                                    <th>Company</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rows will be added dynamically -->
                            </tbody>
                        </table>
                        <button type="button" id="addPreviousWorkBtn">Add Previous Work</button>
                    </div>

                    <div class="form-actions">
                        <button type="submit">Update Record</button>
                    </div>
                    <div id="updateMessage" class="form-message" style="display: none;"></div>
                </form>


            </div>

            <div id="add-alumni" class="tab-content">
                <h3>Add New Alumni Information</h3>

                <form id="addAlumniForm" class="dashboard-form">
                    <label for="addStudentId">Student ID:</label>
                    <input type="text" id="addStudentId" name="student_id" required>

                    <label for="addLastName">Last Name:</label>
                    <input type="text" id="addLastName" name="last_name" required>

                    <label for="addFirstName">First Name:</label>
                    <input type="text" id="addFirstName" name="first_name" required>

                    <label for="addMiddleName">Middle Name:</label>
                    <input type="text" id="addMiddleName" name="middle_name">

                    <label for="addEmail">Email:</label>
                    <input type="email" id="addEmail" name="email" required>

                    <label for="addPassword">Password:</label>
                    <input type="password" id="addPassword" name="password" required>
                     <div class="input-hint">Min 8 characters, include: Upper, Lower, Number</div>


                    <label for="addEmploymentStatus">Employment Status:</label>
                    <select id="addEmploymentStatus" name="employment_status" required>
                         <option value="">Select Status</option>
                         <option value="Employed">Employed</option>
                         <option value="Unemployed">Unemployed</option>
                         </select>

                    <label for="addCollegeAndCourse">College & Course:</label>
                    <input type="text" id="addCollegeAndCourse" name="college_and_course" required>

                    <label for="addGraduationYear">Graduation Year:</label>
                    <input type="number" id="addGraduationYear" name="graduation_year" required min="1900" max="3000" value="<?php echo date('Y'); ?>">

                    <label for="addThesisGroupId">Thesis Group ID (Optional):</label>
                    <input type="text" id="addThesisGroupId" name="thesis_group_id">

                    <label for="addThesisTitle">Thesis Title (Optional):</label>
                    <input type="text" id="addThesisTitle" name="thesis_title">

                    <button type="submit">Add Alumnus</button>
                    <div id="addMessage" class="form-message" style="display: none;"></div>
                </form>
            </div>

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
        // --- Tab Functionality ---
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            const singleAlumnusView = document.getElementById('singleAlumnusView');
            const backToListBtn = document.getElementById('backToListBtn');
            const noAlumnusSelectedMessage = document.getElementById('noAlumnusSelectedMessage');

             const editRecordBtn = document.getElementById('editRecordBtn');
    if (editRecordBtn) {
        editRecordBtn.addEventListener('click', function() {
            // Get the currently viewed alumni ID from the URL
            const urlParams = new URLSearchParams(window.location.search);
            const alumniId = urlParams.get('alumni_id');
            if (alumniId) {
                // Switch to the Update Alumni tab and pre-fill the search box
                document.querySelector('.tab-button[data-target="update-alumni"]').click();
                document.getElementById('updateSearchId').value = alumniId;
                // Trigger the search and populate function
                if (typeof searchAndPopulateUpdateForm === "function") {
                    searchAndPopulateUpdateForm(alumniId);
                } else {
                    document.getElementById('searchForUpdateBtn').click();
                }
            }
        });
    }

            // Function to parse URL parameters
            function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                const results = regex.exec(location.search);
                return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
            }

            // Check for alumni_id in the URL on page load
            let alumniIdFromUrl = getUrlParameter('alumni_id'); // Use let as it might be cleared

            function showTab(tabId) {
                tabButtons.forEach(button => button.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                tabContents.forEach(content => content.style.display = 'none');

                const activebutton = document.querySelector(`.tab-button[data-target="${tabId}"]`);
                if (activebutton) {
                    activebutton.classList.add('active');
                }

                const targetContent = document.getElementById(tabId);
                if (targetContent) {
                    targetContent.classList.add('active');
                    targetContent.style.display = 'block';

                    // --- Handle View Alumni tab content based on URL parameter ---
                    if (tabId === 'view-alumni') {
                        if (alumniIdFromUrl) {
                            // If alumni_id is in URL, show single alumnus view
                            noAlumnusSelectedMessage.style.display = 'none'; // Hide the "no selected" message
                            singleAlumnusView.style.display = 'block';
                            displaySingleAlumnus(alumniIdFromUrl); // Fetch and display single alumnus
                        } else {
                            // If no alumni_id in URL, show the "no selected" message
                            noAlumnusSelectedMessage.style.display = 'block';
                            singleAlumnusView.style.display = 'none';
                        }
                    } else if (tabId === 'update-alumni') {
                        // Hide the update form and messages when switching to the update tab initially
                        document.getElementById('updateAlumniForm').style.display = 'none';
                        document.getElementById('updateMessage').style.display = 'none';
                        document.getElementById('updateAlumnusNotFound').style.display = 'none';

                        // --- Automatically load data for update if alumni_id is in URL ---
                        if (alumniIdFromUrl) {
                            const updateSearchInput = document.getElementById('updateSearchId');
                            updateSearchInput.value = alumniIdFromUrl;

                            // Directly call the search and populate function (no need to simulate click)
                            if (typeof searchAndPopulateUpdateForm === "function") {
                                searchAndPopulateUpdateForm(alumniIdFromUrl);
                            } else {
                                // If not defined globally, fallback to simulating a click
                                const searchButton = document.getElementById('searchForUpdateBtn');
                                if (searchButton) {
                                    searchButton.click();
                                }
                            }
                        }
                    }
                    // Reset add form and message when switching tabs
                    document.getElementById('addAlumniForm').reset();
                    document.getElementById('addMessage').style.display = 'none';
                }
            }

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-target');
                    // Only clear alumni_id from URL if navigating away from this PHP page
                    // Do NOT clear alumni_id when switching tabs
                    showTab(tabId);
                    // Optionally, scroll to top on tab switch
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });

            // Event listener for the "Back to Full List" button
            backToListBtn.addEventListener('click', function() {
                window.location.href = "filterSearchAlumniADMIN.php";
            });

            // Show the correct tab based on URL or default to 'view-alumni'
            if (alumniIdFromUrl) {
                // If alumni_id is in the URL, show the update tab and auto-populate
                showTab('view-alumni');
            } else {
                // If no alumni_id, show the first tab marked as active (or default to view-alumni)
                const initialTabButton = document.querySelector('.tab-button.active') || document.querySelector('.tab-button[data-target="view-alumni"]');
                if (initialTabButton) {
                    const initialTabId = initialTabButton.getAttribute('data-target');
                    showTab(initialTabId);
                } else if (tabButtons.length > 0) {
                    const firstTabId = tabButtons[0].getAttribute('data-target');
                    showTab(firstTabId);
                }
            }
        });

        // --- AJAX Functions for View Alumni Tab ---

        // Function to fetch and display a single alumnus's details
        function displaySingleAlumnus(alumniId) {
            const detailsContentDiv = document.getElementById('alumnusDetailsContent');
            detailsContentDiv.innerHTML = 'Loading details...'; // Loading message

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch_single_alumnus.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success && response.data) {
                        const alumnus = response.data;
                        // Assign work experience fields from root to alumnus object
                        alumnus.current_work_exp = response.current_work_exp;
                        alumnus.previous_work_exp = response.previous_work_exp;

                        detailsContentDiv.innerHTML = `
                            <p><strong>Alumni ID:</strong> ${htmlspecialchars(alumnus.alumni_id ?? '')}</p>
                            <p><strong>Student ID:</strong> ${htmlspecialchars(alumnus.student_id ?? '')}</p>
                            <p><strong>Name:</strong> ${htmlspecialchars(alumnus.first_name ?? '')} ${htmlspecialchars(alumnus.middle_name ?? '')} ${htmlspecialchars(alumnus.last_name ?? '')}</p>
                            <p><strong>Email:</strong> ${htmlspecialchars(alumnus.email ?? '')}</p>
                            <p><strong>Employment Status:</strong> <span class="status-indicator-dot ${getDotClass(alumnus.employment_status ?? '')}"></span>${htmlspecialchars(alumnus.employment_status ?? '')}</p>
                            <p><strong>College & Course:</strong> ${htmlspecialchars(alumnus.college_and_course ?? '')}</p>
                            <p><strong>Graduation Year:</strong> ${htmlspecialchars(alumnus.graduation_year ?? '')}</p>
                            <p><strong>Thesis Group ID:</strong> ${htmlspecialchars(alumnus.thesis_group_id ?? '')}</p>
                            <p><strong>Thesis Title:</strong> ${htmlspecialchars(alumnus.thesis_title ?? '')}</p>
                        `;

                        let workExpHtml = '';
                        if (alumnus.current_work_exp) {
                            workExpHtml += `
                                <br>
                                <h3>Work Experience Details</h3>
                                <h4 style="color:#337ab7; text-align:left">Current Profession</h4>
                                <p><strong>Profession:</strong> ${htmlspecialchars(alumnus.current_work_exp.current_profession ?? '')}</p>
                                <p><strong>Description:</strong> ${htmlspecialchars(alumnus.current_work_exp.current_work_desc ?? '')}</p>
                            `;
                        } else {
                            workExpHtml += `<h4>Work Experience Details</h4><p>No current work experience recorded.</p>`;
                        }

                        if (alumnus.previous_work_exp && alumnus.previous_work_exp.length > 0) {
                            let tableRows = alumnus.previous_work_exp.map(exp => `
                                <tr>
                                    <td>${htmlspecialchars(exp.previous_profession ?? '')}</td>
                                    <td>${htmlspecialchars(exp.previous_work_desc ?? '')}</td>
                                    <td>${htmlspecialchars(exp.company ?? '')}</td>
                                    <td>${htmlspecialchars(exp.work_date ?? '')}</td>
                                </tr>
                            `).join('');
                            workExpHtml += `
                                <h4 style="color:#337ab7; text-align:left">Previous Work Experience</h4>
                                <table class="alumni-view-table">
                                    <thead>
                                        <tr>
                                            <th>Profession</th>
                                            <th>Description</th>
                                            <th>Company</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${tableRows}
                                    </tbody>
                                </table>
                            `;
                        } else {
                            workExpHtml += `<p>No previous work experience recorded.</p>`;
                        }

                        detailsContentDiv.innerHTML += workExpHtml;
                    } else {
                        detailsContentDiv.innerHTML = '<p style="color: red;">Alumnus not found.</p>';
                        console.error('Fetch Single Alumnus failed:', response.message);
                    }
                } else {
                    detailsContentDiv.innerHTML = '<p style="color: red;">Error loading alumnus details.</p>';
                    console.error('Fetch Single Alumnus AJAX failed:', xhr.status, xhr.statusText);
                    console.error('Fetch Single Response:', xhr.responseText);
                }
            };

            xhr.onerror = function() {
                detailsContentDiv.innerHTML = '<p style="color: red;">Network error loading alumnus details.</p>';
                console.error('Network error during fetch single alumnus AJAX request.');
            };

            xhr.send("alumni_id=" + encodeURIComponent(alumniId));
        }

        // Helper function to get dot class for employment status
        function getDotClass(status) {
            if (status && (status.toLowerCase() === 'employed' || status.toLowerCase() === 'full-time' || status.toLowerCase() === 'part-time' || status.toLowerCase() === 'freelance')) {
                return 'employed';
            }
            return 'not-employed';
        }

        // Helper function for HTML escaping (basic)
        function htmlspecialchars(str) {
             if (typeof str !== 'string') return str; // Return non-strings as is
             return str.replace(/&/g, "&amp;")
                       .replace(/</g, "&lt;")
                       .replace(/>/g, "&gt;")
                       .replace(/"/g, "&quot;")
                       .replace(/'/g, "&#039;");
        }


        // --- AJAX Functions for Delete (Removed from this page) ---
        // Delete functionality now resides only on filterSearchAlumniADMIN.php

        // Removed addDeleteEventListeners()
        // Removed handleDeleteClick()
        // Removed deleteRecord()

        // Removed event listeners for filter inputs as they are not on this page
        // document.querySelectorAll('#searchName, #statusFilter, #programFilter, #yearFilter').forEach(el => { ... });


        // --- JavaScript for Update Alumni Tab ---
        document.addEventListener('DOMContentLoaded', function() {
            const updateTab = document.getElementById('update-alumni');
            const searchInput = updateTab.querySelector('#updateSearchId');
            const searchButton = updateTab.querySelector('#searchForUpdateBtn');
            const updateForm = updateTab.querySelector('#updateAlumniForm');
            const updateMessage = updateTab.querySelector('#updateMessage');
            const notFoundMessage = updateTab.querySelector('#updateAlumnusNotFound'); // Updated ID

            // Function to handle the search and populate the update form
            function searchAndPopulateUpdateForm(searchId) {
                 if (searchId === '') {
                     alert('Please enter a Student ID or Alumni ID to search.');
                     return;
                 }

                 // Hide previous messages and form
                 updateForm.style.display = 'none';
                 updateMessage.style.display = 'none';
                 notFoundMessage.style.display = 'none';


                 const xhr = new XMLHttpRequest();
                 // This PHP file fetches data for a single alumnus for editing
                 xhr.open("POST", "fetch_alumnus_for_edit.php", true);
                 xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                 xhr.onload = function() {
                     if (xhr.status >= 200 && xhr.status < 300) {
                         const response = JSON.parse(xhr.responseText);
                         if (response.success && response.data) {
                             const alumnus = response.data;
                             // Populate the form fields with fetched data
                             updateForm.querySelector('#updateAlumniId').value = alumnus.alumni_id ?? '';
                             updateForm.querySelector('#updateStudentId').value = alumnus.student_id ?? '';
                             updateForm.querySelector('#updateLastName').value = alumnus.last_name ?? '';
                             updateForm.querySelector('#updateFirstName').value = alumnus.first_name ?? '';
                             updateForm.querySelector('#updateMiddleName').value = alumnus.middle_name ?? '';
                             updateForm.querySelector('#updateEmail').value = alumnus.email ?? '';
                             updateForm.querySelector('#updateEmploymentStatus').value = alumnus.employment_status ?? '';
                             updateForm.querySelector('#updateCollegeAndCourse').value = alumnus.college_and_course ?? '';
                             updateForm.querySelector('#updateGraduationYear').value = alumnus.graduation_year ?? '';
                             updateForm.querySelector('#updateThesisGroupId').value = alumnus.thesis_group_id ?? ''; // Populate new field
                             updateForm.querySelector('#updateThesisTitle').value = alumnus.thesis_title ?? ''; // Populate thesis title
                             updateForm.querySelector('#updateCurrentProfession').value = alumnus.current_work_exp?.current_profession ?? '';
                             updateForm.querySelector('#updateCurrentWorkDesc').value = alumnus.current_work_exp?.current_work_desc ?? '';

                             // Populate previous work table
                             const prevTableBody = document.querySelector('#previousWorkTable tbody');
                             prevTableBody.innerHTML = '';
                             if (alumnus.previous_work_exp && alumnus.previous_work_exp.length > 0) {
                                 alumnus.previous_work_exp.forEach(exp => {
                                     addPreviousWorkRow(exp.previous_profession, exp.previous_work_desc, exp.company, exp.work_date);
                                 });
                             } else {
                                 addPreviousWorkRow('', '', '', '');
                             }

                             // Show the update form
                             updateForm.style.display = 'flex'; // Use flex as defined in CSS
                         } else {
                             // Show not found message
                             notFoundMessage.style.display = 'block';
                         }
                     } else {
                         // Handle AJAX error
                         updateMessage.classList.remove('success');
                         updateMessage.classList.add('error');
                         updateMessage.innerHTML = 'Error searching for alumnus.';
                         updateMessage.style.display = 'block';
                         console.error('Search for Update AJAX failed:', xhr.status, xhr.statusText);
                         console.error('Search Response:', xhr.responseText);
                     }
                 };

                 xhr.onerror = function() {
                     updateMessage.classList.remove('success');
                     updateMessage.classList.add('error');
                     updateMessage.innerHTML = 'Network error during search.';
                     updateMessage.style.display = 'block';
                     console.error('Network error during search AJAX request.');
                 };

                 // Send the search ID
                 xhr.send("search_id=" + encodeURIComponent(searchId));
            }

            // Helper to add a row
            function addPreviousWorkRow(profession = '', desc = '', company = '', date = '') {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="text" name="previous_profession[]" value="${htmlspecialchars(profession)}"></td>
                    <td><input type="text" name="previous_work_desc[]" value="${htmlspecialchars(desc)}"></td>
                    <td><input type="text" name="previous_company[]" value="${htmlspecialchars(company)}"></td>
                    <td><input type="text" name="previous_work_date[]" value="${htmlspecialchars(date)}"></td>
                    <td><button type="button" class="removePrevWorkBtn">Remove</button></td>
                `;
                row.querySelector('.removePrevWorkBtn').onclick = function() {
                    row.remove();
                };
                const prevTableBody = document.querySelector('#previousWorkTable tbody');
                prevTableBody.appendChild(row);
            }

            document.getElementById('addPreviousWorkBtn').onclick = function() {
                addPreviousWorkRow('', '', '', '');
            };

            // Event listener for the manual search button click
            searchButton.addEventListener('click', function() {
                const searchId = searchInput.value.trim();
                searchAndPopulateUpdateForm(searchId); // Call the new function
            });

            // Event listener for the Update form submission
            updateForm.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                const formData = new FormData(updateForm);

                // AJAX call to update alumnus data
                const xhr = new XMLHttpRequest();
                 // This PHP file handles the update
                xhr.open("POST", "update_alumni_record.php", true);
                // No need to set Content-type header for FormData

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                         const response = JSON.parse(xhr.responseText);
                         updateMessage.style.display = 'block';
                         if (response.success) {
                              updateMessage.classList.remove('error');
                              updateMessage.classList.add('success');
                              updateMessage.innerHTML = response.message;
                              // Optionally hide the form or clear it after successful update
                              // updateForm.style.display = 'none';
                              // searchInput.value = ''; // Clear search input
                              // No need to call fetchData() here anymore as it's not on this page
                         } else {
                              updateMessage.classList.remove('success');
                              updateMessage.classList.add('error');
                              updateMessage.innerHTML = response.message;
                         }
                    } else {
                        // Handle AJAX error
                        updateMessage.classList.remove('success');
                        updateMessage.classList.add('error');
                        updateMessage.innerHTML = 'Error updating record.';
                        updateMessage.style.display = 'block';
                        console.error('Update Record AJAX failed:', xhr.status, xhr.statusText);
                        console.error('Update Response:', xhr.responseText);
                    }
                };

                 xhr.onerror = function() {
                     updateMessage.classList.remove('success');
                     updateMessage.classList.add('error');
                     updateMessage.innerHTML = 'Network error during update.';
                     updateMessage.style.display = 'block';
                     console.error('Network error during update AJAX request.');
                 };


                // Send the form data
                xhr.send(formData);
            });
        });


        // --- JavaScript for Add New Alumni Tab ---
        document.addEventListener('DOMContentLoaded', function() {
            const addTab = document.getElementById('add-alumni');
            const addForm = addTab.querySelector('#addAlumniForm');
            const addMessage = addTab.querySelector('#addMessage');

            addForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(addForm);

                const xhr = new XMLHttpRequest();
                // This PHP file handles adding a new alumnus
                xhr.open("POST", "add_new_alumnus.php", true);

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                         const response = JSON.parse(xhr.responseText);
                         addMessage.style.display = 'block';
                         if (response.success) {
                              addMessage.classList.remove('error');
                              addMessage.classList.add('success');
                              addMessage.innerHTML = response.message;
                              addForm.reset();
                              // No need to call fetchData() here anymore
                         } else {
                              addMessage.classList.remove('success');
                              addMessage.classList.add('error');
                              addMessage.innerHTML = response.message;
                         }
                    } else {
                        addMessage.classList.remove('success');
                        addMessage.classList.add('error');
                        addMessage.innerHTML = 'Error adding record.';
                        addMessage.style.display = 'block';
                        console.error('Add Record AJAX failed:', xhr.status, xhr.statusText);
                        console.error('Add Response:', xhr.responseText);
                    }
                };

                xhr.onerror = function() {
                     addMessage.classList.remove('success');
                     addMessage.classList.add('error');
                     addMessage.innerHTML = 'Network error during add.';
                     addMessage.style.display = 'block';
                     console.error('Network error during add AJAX request.');
                 };


                xhr.send(formData);
            });
        });

    </script>

</body>
</html>
