<?php
// FILE: filterSearchAlumniADMIN.php
session_start(); // Start the session

// Check if user is logged in AND is an Admin, otherwise redirect
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    // Redirect to rolePage.php or a specific unauthorized access page
    header("Location: rolePage.php");
    exit();
}

// Get user information from session
$user_type = $_SESSION['user_type']; // Should be "Admin"
$user_id = $_SESSION['user_id'];

// Get current year for copyright footer
$current_year = date("Y");

// Include your database connection file to fetch programs for the dropdown
require 'db.php'; // Adjust the path if necessary

// --- PHP Logic for Fetching Programs for the Dropdown ---
$programs_list = []; // Initialize an empty array

// SQL query to get distinct college and course combinations from the single column
// Selects the existing 'college_and_course' column
$sql_programs = "SELECT DISTINCT college_and_course FROM alumni ORDER BY college_and_course";
$result_programs = $conn->query($sql_programs);

if ($result_programs === false) {
    error_log("Error fetching programs for dropdown: " . $conn->error);
    // Handle error if necessary, maybe add a default option indicating error
    $programs_list[] = "Error fetching programs"; // Add an error option
} else {
    if ($result_programs->num_rows > 0) {
        while($row_programs = $result_programs->fetch_assoc()) {
            // Use the value directly from the 'college_and_course' column
            // Only add non-empty values to the list
            if (!empty($row_programs['college_and_course'])) {
                 $programs_list[] = htmlspecialchars($row_programs['college_and_course']);
            }
        }
    }
    // If no rows, $programs_list will be an empty array, which is fine.

    $result_programs->free(); // Free result set
}

// Close the database connection after fetching programs
// The connection will be reopened in fetchAlumniADMIN.php and fetch_incomplete_alumni.php
$conn->close();
// --- End PHP Logic for Fetching Programs ---

// Note: Data fetching for the main table is handled by fetchAlumniADMIN.php via AJAX.
// Data fetching for the incomplete table is handled by fetch_incomplete_alumni.php via AJAX.

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Search Alumni | PATH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- General Body and Layout Styles (Copied from homePage.php) --- */
        body {
            font-family: sans-serif;
            margin: 0; /* Remove default body margin */
            background-color: #f0f0f0; /* Light grey background for the entire page */
            padding-top: 60px; /* Add padding to the top to prevent content from being hidden by the fixed navbar */
            padding-bottom: 80px; /* Add padding to the bottom for the footer */
            position: relative; /* Needed for footer positioning if not using flex on body */
            min-height: 100vh; /* Ensure body is at least viewport height */
        }

        /* --- Navbar Style (Copied from homePage.php) --- */
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

        /* --- Dropdown Style (Copied from homePage.php) --- */
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
            background-color: #f8f8f8; /* Off-white */
            padding: 20px;
            margin: 20px auto; /* Center the content block and add vertical margin */
            max-width: 1400px; /* Increased max-width to accommodate more columns */
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            min-height: 400px; /* Add a minimum height for content */
        }

        /* --- User Status Box Style (Copied from homePage.php) --- */
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

        /* --- Footer Style (Copied from homePage.php) --- */
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
            margin-bottom: 20px;
            text-align: center;
        }

        /* Styles for the filter/search area */
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            justify-content: center; /* Center filter elements */
        }

        .filter-container input[type="text"],
        .filter-container select {
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
             /* Ensure select has a similar height to inputs */
            height: 34px; /* Adjust if needed to match input height */
            box-sizing: border-box;
        }


        /* --- Table Styles --- */
        .alumni-table {
            width: 100%;
            border-collapse: collapse; /* Remove space between borders */
            margin-top: 20px;
        }

        .alumni-table th, .alumni-table td {
            border: 1px solid #ddd; /* Light grey borders */
            padding: 10px; /* Padding inside cells */
            text-align: left; /* Align text left in cells */
            word-break: break-word; /* Allow text to wrap */
        }

        .alumni-table th {
            background-color: #1f4c73; /* Dark blue header background */
            color: white; /* White header text */
            text-align: center; /* Center header text */
        }

        .alumni-table tbody tr:nth-child(even) {
            background-color: #f2f2f2; /* Zebra striping for rows */
        }

        /* Style for clickable rows */
        .alumni-table tbody tr.clickable-row:hover,
        .incomplete-table tbody tr.clickable-row:hover {
            background-color: #cce0f0; /* Lighter blue on hover */
            cursor: pointer; /* Indicate it's clickable */
        }

         /* Style for the incomplete records table */
        .incomplete-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px; /* More space above this table */
        }

        .incomplete-table th, .incomplete-table td {
             border: 1px solid #ddd; /* Light grey borders */
             padding: 10px; /* Padding inside cells */
             text-align: left; /* Align text left in cells */
             word-break: break-word; /* Allow text to wrap */
        }

         .incomplete-table th {
             background-color: #c0392b; /* Red header background for incomplete */
             color: white; /* White header text */
             text-align: center; /* Center header text */
         }

         .incomplete-table tbody tr:nth-child(even) {
             background-color: #f2f2f2; /* Zebra striping */
         }


        /* --- Employment Status Button/Indicator Styles --- */
        .status-indicator-dot { /* Renamed for clarity */
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .employed {
            background-color: green;
        }

        .not-employed {
            background-color: red;
        }
        /* Add more status colors here if needed */

        /* --- Delete Button Style --- */
        .delete-btn {
            color: #c0392b; /* Red color for delete */
            cursor: pointer;
            font-size: 1.2em; /* Slightly larger icon */
            margin: 0 auto; /* Center the icon in the cell */
            display: block; /* Make it a block element to center */
            text-align: center; /* Ensure the icon itself is centered */
        }

        .delete-btn:hover {
            color: #e74c3c; /* Darker red on hover */
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
                        <a href="editOwnAlumniRecord.php">Edit Your Record</a>
                    <?php endif; ?>
                     <a href="logout.php">Logout</a>
                </div>
            </div>

            <a href="homePage.php#about-us" class="navbar-link">ABOUT</a>
        </div>
    </div>

    <div class="main-content">
        <div class="page-title">Admin - Search Alumni</div>

        <div class="filter-container">
            <input type="text" id="searchName" placeholder="Search Alumni">

            <select id="statusFilter">
                <option value="">All Status</option>
                <option value="Employed">Employed</option>
                <option value="Unemployed">Unemployed</option>
                 </select>

            <select id="programFilter">
                <option value="">All Programs</option>
                <?php
                // Include your database connection file here to fetch programs
                require 'db.php'; // Adjust the path if necessary

                $sql_programs = "SELECT DISTINCT college_and_course FROM alumni ORDER BY college_and_course";
                $result_programs = $conn->query($sql_programs);

                if ($result_programs === false) {
                    error_log("Error fetching programs for dropdown: " . $conn->error);
                    // Optionally add an error option to the dropdown
                    echo '<option value="">Error loading programs</option>';
                } else {
                    if ($result_programs->num_rows > 0) {
                        while($row_programs = $result_programs->fetch_assoc()) {
                            // Use the value directly from the 'college_and_course' column
                            if (!empty($row_programs['college_and_course'])) {
                                echo '<option value="' . htmlspecialchars($row_programs['college_and_course']) . '">' . htmlspecialchars($row_programs['college_and_course']) . '</option>';
                            }
                        }
                    }
                    $result_programs->free();
                }

                $conn->close();
                ?>
            </select>

            <input type="text" id="yearFilter" placeholder="Search Year (Ex: 2020)">
            <input type="text" id="thesisGroupIdFilter" placeholder="Search Thesis Group ID">
        </div>
        <div class="results-area">
            <h3>Search Results</h3>
            <div id="resultsCount" style="font-weight:bold; color:#337ab7; margin-bottom:10px;"></div>
            <div id="totalCompleteCount" style="font-weight:bold; color:#337ab7; margin-bottom:10px; text-align:center;"></div>
            <table class="alumni-table">
                <thead>
                    <tr>
                        <th>Employment Status</th>
                        <th>Alumni ID</th>
                        <th>Student ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>College & Course</th>
                        <th>Graduation Year</th>
                        <th>Thesis Group ID</th>
                        <th>Thesis Title</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="alumniTable">
                    <tr>
                        <td colspan="10" style="text-align: center;">Loading alumni data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="incomplete-results-area">
            <h3>Incomplete Records</h3>
            <div id="totalIncompleteCount" style="font-weight:bold; color:#c0392b; margin-bottom:10px; text-align:center;"></div>
            <table class="incomplete-table">
                <thead>
                    <tr>
                        <th>Employment Status</th>
                        <th>Alumni ID</th>
                        <th>Student ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>College & Course</th>
                        <th>Graduation Year</th>
                        <th>Thesis Group ID</th>
                        <th>Thesis Title</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="incompleteAlumniTable">
                    <tr>
                        <td colspan="10" style="text-align: center;">Loading incomplete records...</td>
                    </tr>
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
            <a href="https://www.facebook.com/fritzmarick.fernandez" target="_blank" title="Facebook Profile">
            <i class="fab fa-facebook"></i>
        </a>
        <a href="https://www.instagram.com/_kciram?igsh=MWhyaGtqOGYydGh2eA==" target="_blank" title="Instagram Profile">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="https://github.com/Maricklabs" target="_blank" title="GitHub Profile">
            <i class="fab fa-github"></i>
        </a>
        </div>
        <div class="footer-text">
            &copy; <?php echo $current_year; ?> West Visayas State University - PATH. All rights reserved.
        </div>
    </footer>

    <script>
        // Function to fetch and display alumni data based on filters (for main table)
        function fetchData() {
            const name = document.getElementById('searchName').value;
            const status = document.getElementById('statusFilter').value;
            const program = document.getElementById('programFilter').value;
            const year = document.getElementById('yearFilter').value;
            const thesisGroupId = document.getElementById('thesisGroupIdFilter').value; // NEW

            const xhrMain = new XMLHttpRequest();
            xhrMain.open("POST", "fetchAlumniADMIN.php", true);
            xhrMain.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            xhrMain.onload = function () {
                const alumniTableBody = document.getElementById('alumniTable');
                if (xhrMain.status >= 200 && xhrMain.status < 300) {
                    alumniTableBody.innerHTML = xhrMain.responseText;
                    addDeleteEventListeners();
                    addClickableRowListeners();
                } else {
                    alumniTableBody.innerHTML = '<tr><td colspan="10" style="text-align: center; color: red;">Error loading data.</td></tr>';
                    console.error('Main AJAX request failed:', xhrMain.status, xhrMain.statusText);
                    console.error('Main Response:', xhrMain.responseText);
                }
            };

            xhrMain.onerror = function() {
                document.getElementById('alumniTable').innerHTML = '<tr><td colspan="10" style="text-align: center; color: red;">Network error. Could not load data.</td></tr>';
                console.error('Network error during main AJAX request.');
            };

            // Add thesisGroupId to the request
            xhrMain.send(
                `name=${encodeURIComponent(name)}&status=${encodeURIComponent(status)}&program=${encodeURIComponent(program)}&year=${encodeURIComponent(year)}&thesisGroupId=${encodeURIComponent(thesisGroupId)}`
            );

            fetchIncompleteData();
        }

        // Function to fetch and display incomplete alumni data (for the second table)
        function fetchIncompleteData() {
             // Create a new XMLHttpRequest object for incomplete data
             const xhrIncomplete = new XMLHttpRequest();

             // Configure the request: POST method to the new PHP file
             xhrIncomplete.open("POST", "fetch_incomplete_alumni.php", true);
             xhrIncomplete.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

             // Define what happens when the response is received for the incomplete table
             xhrIncomplete.onload = function () {
                 const incompleteTableBody = document.getElementById('incompleteAlumniTable');
                 if (xhrIncomplete.status >= 200 && xhrIncomplete.status < 300) {
                     // Update the incomplete table body with the received HTML
                     incompleteTableBody.innerHTML = xhrIncomplete.responseText;

                     // Add event listeners to the delete buttons in the incomplete table
                     addDeleteEventListeners();
                     // Add click listeners to rows after the table is updated
                     addClickableRowListeners();

                 } else {
                      // Handle errors during the AJAX request for the incomplete table
                     incompleteTableBody.innerHTML = '<tr><td colspan="10" style="text-align: center; color: red;">Error loading incomplete records.</td></tr>';
                     console.error('Incomplete AJAX request failed:', xhrIncomplete.status, xhrIncomplete.statusText);
                     console.error('Incomplete Response:', xhrIncomplete.responseText); // Log the response for debugging
                 }
             }

             // Handle network errors for the incomplete table request
             xhrIncomplete.onerror = function() {
                  document.getElementById('incompleteAlumniTable').innerHTML = '<tr><td colspan="10" style="text-align: center; color: red;">Network error. Could not load incomplete records.</td></tr>';
                  console.error('Network error during incomplete AJAX request.');
             };

             // Send the request for incomplete data (no filters needed for this specific query)
             xhrIncomplete.send(); // No data to send for this request
        }

        // Function to fetch and display count of matching alumni records
        function fetchCount() {
            const name = document.getElementById('searchName').value;
            const status = document.getElementById('statusFilter').value;
            const program = document.getElementById('programFilter').value;
            const year = document.getElementById('yearFilter').value;
            const thesisGroupId = document.getElementById('thesisGroupIdFilter').value;

            const xhrCount = new XMLHttpRequest();
            xhrCount.open("POST", "countAlumniADMIN.php", true);
            xhrCount.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhrCount.onload = function () {
                if (xhrCount.status >= 200 && xhrCount.status < 300) {
                    const data = JSON.parse(xhrCount.responseText);
                    document.getElementById('resultsCount').textContent = `Matching Results: ${data.count}`;
                    document.getElementById('totalCompleteCount').textContent = `Total Complete Records: ${data.total_complete}`;
                    document.getElementById('totalIncompleteCount').textContent = `Total Incomplete Records: ${data.total_incomplete}`;
                } else {
                    document.getElementById('resultsCount').textContent = '';
                    document.getElementById('totalCompleteCount').textContent = '';
                    document.getElementById('totalIncompleteCount').textContent = '';
                }
            };
            xhrCount.send(
                `name=${encodeURIComponent(name)}&status=${encodeURIComponent(status)}&program=${encodeURIComponent(program)}&year=${encodeURIComponent(year)}&thesisGroupId=${encodeURIComponent(thesisGroupId)}`
            );
        }

        // Function to add event listeners to delete buttons in both tables
        function addDeleteEventListeners() {
             // Select delete buttons within both tables
             document.querySelectorAll('#alumniTable .delete-btn, #incompleteAlumniTable .delete-btn').forEach(btn => {
                 // Remove existing listeners to prevent duplicates
                 btn.removeEventListener('click', handleDeleteClick);
                 // Add the new listener
                 btn.addEventListener('click', handleDeleteClick);
             });
        }

        // Event handler for delete button clicks
        function handleDeleteClick(event) {
             // 'this' refers to the clicked button
             event.stopPropagation(); // Prevent the row click event from firing
             const alumniId = this.dataset.id;
             // Confirm deletion with the user
             if (confirm("Are you sure you want to delete this record?")) {
                 // Call the deleteRecord function with the alumni ID
                 deleteRecord(alumniId);
             }
        }

         // Function to add click listeners to table rows
        function addClickableRowListeners() {
            // Select clickable rows within both tables
            document.querySelectorAll('#alumniTable .clickable-row, #incompleteAlumniTable .clickable-row').forEach(row => {
                 // Remove existing listeners to prevent duplicates
                 row.removeEventListener('click', handleRowClick);
                 // Add the new listener
                 row.addEventListener('click', handleRowClick);
            });
        }

        // Event handler for row clicks
        function handleRowClick() {
            const alumniId = this.dataset.id;
            // Redirect to adminDashboard.php with the alumni_id as a URL parameter
            window.location.href = `adminDashboard.php?alumni_id=${encodeURIComponent(alumniId)}`;
        }


        // Function to delete an alumni record
        function deleteRecord(alumniId) {
            const xhr = new XMLHttpRequest();
            // Configure the request: POST method to deleteAlumniADMIN.php
            xhr.open("POST", "deleteAlumniADMIN.php", true); // Using the filename you provided
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            // Define what happens when the response is received
            xhr.onload = function () {
                 if (xhr.status >= 200 && xhr.status < 300) {
                    // Show the response message (e.g., "Record deleted.")
                    alert(xhr.responseText);
                    // Reload BOTH table data after deletion
                    fetchData(); // This will call fetchIncompleteData internally
                    fetchCount(); // Update the count after deletion
                 } else {
                    // Handle errors during the AJAX request
                    alert("Error deleting record.");
                    console.error('Delete AJAX request failed:', xhr.status, xhr.statusText);
                    console.error('Response:', xhr.responseText); // Log the response for debugging
                }
            }

             // Handle network errors
            xhr.onerror = function() {
                 alert("Network error. Could not delete record.");
                 console.error('Network error during delete AJAX request.');
            };

            // Send the request with the alumni ID to delete
            xhr.send("alumni_id=" + encodeURIComponent(alumniId)); // Send alumni_id
        }

        // Add event listeners to all filter inputs to trigger data fetching for the main table and count
        document.querySelectorAll('#searchName, #statusFilter, #programFilter, #yearFilter, #thesisGroupIdFilter').forEach(el => {
            el.addEventListener('input', function() {
                fetchData();
                fetchCount();
            });
        });

        // Fetch data for BOTH tables and count when the page loads
        window.onload = function() {
            fetchData(); // Load initial alumni data for the main table (which calls fetchIncompleteData)
            fetchCount(); // Load initial count of matching records
        };

    </script>

</body>
</html>
