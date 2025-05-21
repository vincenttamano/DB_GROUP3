<?php
// FILE: homePage.php
session_start(); // Start the session

// Check if user is logged in, otherwise redirect to rolePage.php
if (!isset($_SESSION['user_type'])) {
    header("Location: rolePage.php");
    exit();
}

// Get user information from session
$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];

// Get current year for copyright
$current_year = date("Y");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Home | PATH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: sans-serif;
            margin: 0; /* Remove default body margin */
            background-color: #f0f0f0; /* Light grey background for the entire page */
            padding-top: 60px; /* Add padding to the top to prevent content from being hidden by the fixed navbar */
            padding-bottom: 80px; /* Add padding to the bottom for the footer */
            position: relative; /* Needed for footer positioning if not using flex on body */
            min-height: 100vh; /* Ensure body is at least viewport height */
        }

        /* --- Navbar Style --- */
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
            width: 40px;
            height: 40px;
            background-color: #ddd;
            border-radius: 5px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Ensures image stays inside */
        }

        .navbar-logo-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Ensures the logo is fully visible and contained */
            display: block;
            background: transparent;
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

        /* --- Dropdown Style --- */
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
            max-width: 960px; /* Max width for content */
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .section {
            margin-top: 40px; /* Space between sections */
            margin-bottom: 20px; /* Space between sections */
            padding-bottom: 10px; /* Padding at the bottom of sections */
            border-bottom: 1px solid #eee; /* Separator line */
        }

        .section:last-child {
            border-bottom: none; /* No border for the last section */
            margin-bottom: 0;
        }

        h2 {
            color: #337ab7; /* Blue heading color */
            text-align: center;
            margin-top: 0;
            font-size: 4.5em; /* Larger font size for main headings */
            font-weight: bold; /* Bold font for main headings */
            margin-left: 1in; /* Added 1 inch left margin */
            margin-right: 1in; /* Added 1 inch right margin */
        }

        /* Centering h3 within sections */
        .section h3 {
            color: #337ab7; /* Blue heading color */
            margin-bottom: 10px;
            text-align: center; /* Center h3 text */
        }


        .user-info {
            text-align: center;
            margin-bottom: 15px; /* Reduced margin to fit within container */
            color: #555; /* Text color for user info in main content */
        }

        .logout-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #c0392b; /* Red color for logout */
            text-decoration: none;
            font-weight: bold;
        }

        .logout-link:hover {
            text-decoration: underline;
        }

        /* --- Site Description Style --- */
        .site-description {
            margin-top: 20px;
            margin-bottom: 30px;
            text-align: left;
            color: #555; /* Darker grey for text */
            line-height: 1.6; /* Improve readability */
            margin-left: 1in; /* Added 1 inch left margin */
            margin-right: 1in; /* Added 1 inch right margin */
        }

        .site-description h3 {
            color: #337ab7; /* Blue heading color */
            margin-bottom: 10px;
        }


        /* --- College Logos Section --- */
        .college-logos {
            display: flex;
            flex-direction: column; /* Stack logo rows vertically */
            align-items: center; /* Center logo rows horizontally */
            gap: 20px; /* Space between rows of logos */
            margin-top: 30px;
            margin-bottom: 30px; /* Add space below the logos */
        }

        .college-logos .logo-row {
            display: flex; /* Arrange logos in a row */
            justify-content: center; /* Center logos within the row */
            flex-wrap: wrap; /* Allow logos in a row to wrap if needed */
            gap: 70px; /* Increased space between logos in a row */
        }


        .college-logo-container { /* New container for logo and label */
            text-align: center; /* Center content within the container */
            flex-basis: 180px; /* Suggest a base width, allows flexibility */
            flex-grow: 1; /* Allow containers to grow */
            max-width: 200px; /* Max width to prevent stretching too much */
            flex-shrink: 0; /* Prevent containers from shrinking below base */
            display: flex; /* Use flexbox for vertical centering of logo and text */
            flex-direction: column; /* Stack logo and text vertically */
            align-items: center; /* Center logo and text horizontally within container */
        }

        .college-logo-placeholder {
            width: 200px; /* Increased size of college logo placeholder */
            height: 200px; /* Increased size of college logo placeholder */
            /* background-color: #ddd; /* Placeholder color - Removed */
            border-radius: 10px; /* Rounded corners */
            display: flex;
            align-items: center;
            justify-content: center;
            /* font-size: 0.8em; /* Removed as it was for placeholder text */
            /* color: #555; /* Removed as it was for placeholder text */
            margin: 0 auto 5px auto; /* Center logo placeholder and add bottom margin */
            flex-shrink: 0; /* Ensure logo placeholder doesn't shrink */
            overflow: hidden; /* Ensure image stays within bounds */
        }

        .college-logo-placeholder img {
             max-width: 100%;
             max-height: 100%;
             display: block;
             object-fit: contain; /* Ensures the image fits without stretching */
        }

        .college-label { /* Style for the college label */
            font-size: 0.9em;
            color: #555;
            word-break: break-word; /* Allow long words to break and wrap */
            text-align: center; /* Ensure text is centered */
            margin-top: 5px; /* Add space between logo and text */
        }


        /* --- About Us Section (Developer Info) --- */
        #about-us {
            /* This is the target for the scroll link */
            padding-top: 60px; /* Add padding to account for the fixed navbar */
            margin-top: -60px; /* Negative margin to pull the content up */
        }

        .developer-info-text {
            margin-bottom: 30px;
            text-align: left;
            color: #555;
            line-height: 1.6;
            margin-left: 1in; /* Added 1 inch left margin */
            margin-right: 1in; /* Added 1 inch right margin */
        }

        .developer-profiles {
            display: flex;
            flex-direction: column; /* Stack developer rows vertically */
            align-items: center; /* Center developer rows horizontally */
            gap: 30px; /* Space between rows of developers */
            margin-top: 30px;
        }

        .developer-profiles .developer-row {
            display: flex; /* Arrange developers in a row */
            justify-content: center; /* Center developers within the row */
            flex-wrap: wrap; /* Allow developers in a row to wrap if needed */
            gap: 30px; /* Space between developers in a row */
        }

        .developer-profile {
            background-color: #eee; /* Slightly darker off-white for profile background */
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            width: 180px; /* Fixed width for portrait orientation */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .developer-image-placeholder {
            width: 150px; /* Size of developer image placeholder */
            height: 200px; /* Height for portrait */
            background-color: #ddd; /* Placeholder color */
            border-radius: 8px; /* Rounded corners */
            margin: 0 auto 10px auto; /* Center and add bottom margin */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            color: #555;
            overflow: hidden; /* Hide overflow if image is larger */
        }

        .developer-image-placeholder img {
             max-width: 100%;
             max-height: 100%;
             display: block;
             object-fit: cover; /* Cover the area without stretching */
        }

        .developer-name {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .developer-label {
            font-size: 0.9em;
            color: #777;
        }

        /* --- Smooth Scrolling --- */
        html {
            scroll-behavior: smooth;
        }

        /* --- User Status Box Style --- */
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

        /* --- Footer Style --- */
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

    </style>
</head>
<body id="top">

    <div class="navbar">
        <div class="navbar-left">
            <div class="navbar-logo-placeholder">
                <img src="/PATH/collegeLogos/finaldbLOGOOO.png" alt="PATH Logo">
            </div>
            <div class="navbar-title">PATH</div>
        </div>
        <div class="navbar-right">
            <a href="homePage.php#top" class="navbar-link">HOME </a>
            

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

        <div id="home" class="section">
            <h2>WVSU <br> Main Campus <br> PATH</h2>

            <div class="site-description">
                <h3>Post Alumni Tracking Hub</h3>
                <p>
                    PATH (Post Alumni Tracking Hub) is designed to facilitate the connection and tracking of alumni from West Visayas State University. Its purpose is to provide a centralized platform for alumni to update their information, for faculty and administrators to access alumni data for various purposes, and to foster a stronger relationship between the university and its graduates. This platform aims to improve communication, support alumni initiatives, and provide valuable insights into the career paths and achievements of WVSU alumni.
                </p>
            </div>

            <h3>Partnered Colleges</h3>
            <div class="college-logos">
                <div class="logo-row">
                    <div class="college-logo-container">
                        <div class="college-logo-placeholder">
                            <img src="/PATH/collegeLogos/cictLogo.png" alt="CICT Logo">
                        </div>
                        <div class="college-label">College of Information and Communications Technology</div>
                    </div>

                    <div class="college-logo-container">
                        <div class="college-logo-placeholder">
                            <img src="/PATH/collegeLogos/conLogo.png" alt="CON Logo">
                        </div>
                        <div class="college-label">College of Nursing</div>
                    </div>

                    <div class="college-logo-container">
                        <div class="college-logo-placeholder">
                        <img src="/PATH/collegeLogos/comLogo.png" alt="COM Logo">
                        </div>
                        <div class="college-label">College of Medicine</div>
                    </div>
                </div>

                <div class="logo-row">

                    <div class="college-logo-container">
                        <div class="college-logo-placeholder">
                            <img src="/PATH/collegeLogos/cbmLogo.png" alt="CBM Logo">
                        </div>
                        <div class="college-label">College of Business and Management</div>
                    </div>

                    <div class="college-logo-container">
                        <div class="college-logo-placeholder">
                            <img src="/PATH/collegeLogos/colLogo.png" alt="COL Logo">
                        </div>
                        <div class="college-label">College of Law</div>
                    </div>
                </div>
            </div>

        </div>

        <div id="about-us" class="section">
            <h2>ABOUT PATH</h2>
            <div class="developer-info-text">
                <p>
                    We are Bachelor of Science in Information Technology students from the College of Information and Communications Technology at West Visayas State University. We developed this website as part of our academic requirements and with the goal of creating a valuable tool to connect the university with its esteemed alumni, facilitating better communication and engagement.
                </p>
            </div>
            <div class="developer-profiles">
                <div class="developer-row">
                    <div class="developer-profile">
                        <div class="developer-image-placeholder">Image 1</div>
                        <div class="developer-name">Fritz Marick Fernandez</div>
                        <div class="developer-label">PROJECT MANAGER</div>
                    </div>
                    <div class="developer-profile">
                        <div class="developer-image-placeholder">Image 2</div>
                        <div class="developer-name">Nherie Acebuche</div>
                        <div class="developer-label">GRAPHIC DESIGNER</div>
                    </div>
                    <div class="developer-profile">
                        <div class="developer-image-placeholder">Image 3</div>
                        <div class="developer-name">John Emmanuel Pieza</div>
                        <div class="developer-label">UI/UX DESIGNER</div>
                    </div>
                    <div class="developer-profile">
                        <div class="developer-image-placeholder">Image 4</div>
                        <div class="developer-name">Justin Ace Ardeña</div>
                        <div class="developer-label">FRONT-END DEVELOPER</div>
                    </div>
                </div>
                <div class="developer-row">
                     <div class="developer-profile">
                        <div class="developer-image-placeholder">Image 5</div>
                        <div class="developer-name">James Remegio</div>
                        <div class="developer-label">FRONT-END DEVELOPER</div>
                    </div>
                     <div class="developer-profile">
                        <div class="developer-image-placeholder">Image 6</div>
                        <div class="developer-name">Vincent John Tamaño</div>
                        <div class="developer-label">BACK-END DEVELOPER</div>
                    </div>
                     <div class="developer-profile">
                        <div class="developer-image-placeholder">Image 7</div>
                        <div class="developer-name">Kliu Maverick Villanueva</div>
                        <div class="developer-label">BACK-END DEVELOPER</div>
                    </div>
                </div>
            </div>
        </div>

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
