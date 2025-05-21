<?php
// FILE: rolePage.php
// Move the PHP redirection logic to the top of the file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['admin'])) {
        header("Location: adminLogin.php");
        exit();
    }
    if (isset($_POST['faculty'])) {
        header("Location: facultyLogin.php");
        exit();
    }
    if (isset($_POST['alumni'])) {
        header("Location: alumniLogin.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title> Post Alumni Tracking Hub | PATH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: sans-serif;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .page-heading {
            color: #337ab7;
            font-size: 2em;
            margin-bottom: 20px;
            text-align: center;
        }

        .role-container {
            background-color: #f8f8f8;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 10px 10px 20px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 90%;
            max-width: 400px;
            box-sizing: border-box;
            margin-bottom: 30px; /* Add space below the container */
        }

        .university-name {
            color: #337ab7;
            font-size: 1.5em;
            margin-bottom: 15px;
            text-align: center;
        }

        .logo-placeholder {
            width: 180px;
            height: 180px;
            margin: 0 auto 20px auto;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-placeholder img {
            max-width: 100%;
            max-height: 100%;
            display: block;
            object-fit: contain;
        }

        .role-form button {
            display: block;
            width: 100%;
            padding: 12px 20px;
            margin-bottom: 15px;
            border: none;
            border-radius: 8px;
            background-color: #337ab7;
            color: white;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .role-form button:hover {
            background-color: #286090;
        }

        .role-form button:last-child {
            margin-bottom: 0;
        }

        /* --- Social Icons Style --- */
        .social-icons {
            text-align: center;
        }

        .social-icons a {
            display: inline-block; /* Arrange icons in a row */
            margin: 0 10px; /* Space between icons */
            color: #337ab7; /* Icon color */
            font-size: 1.8em; /* Icon size */
            transition: color 0.3s ease; /* Smooth hover effect */
        }

        .social-icons a:hover {
            color: #286090; /* Darker blue on hover */
        }
    </style>
</head>
<body>

    <div class="page-heading">Post Alumni Tracking Hub</div>

    <div class="role-container">
        <div class="logo-placeholder">
            <img src="/PATH/WVSUMainLogo.png" alt="West Visayas State University Logo">
        </div>

        <div class="university-name">West Visayas State University Main Campus</div>

        <form method="post" class="role-form">
            <button name="admin">Admin</button>
            <button name="faculty">Faculty</button>
            <button name="alumni">Alumni</button>
        </form>
    </div>

    <div class="social-icons">
        <a href="https://mail.google.com/" target="_blank" title="Gmail">
            <i class="fas fa-envelope"></i>
        </a>
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

</body>
</html>
