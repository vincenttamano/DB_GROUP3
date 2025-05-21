<?php
// FILE: alumniLogin.php
session_start(); // Ensure session_start() is called at the very beginning
require_once 'includes/functions.php'; // Assuming this is needed, keep it
require 'db.php'; // Include database connection

$error = ''; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the specific form fields were submitted
    if (isset($_POST['student_id']) && isset($_POST['password'])) {
        $student_id = trim($_POST['student_id']); // Trim whitespace
        $password = $_POST['password'];

        if (empty($student_id) || empty($password)) {
             $error = "Student ID and Password are required.";
        } else {
            $stmt = $conn->prepare("SELECT alumni_id, password FROM alumni WHERE student_id = ?");
            if ($stmt === false) {
                // Database prepare error
                error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                $error = "An internal error occurred. Please try again later.";
            } else {
                $stmt->bind_param("s", $student_id);
                if (!$stmt->execute()) {
                    // Database execute error
                     error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                     $error = "An internal error occurred. Please try again later.";
                } else {
                    $stmt->store_result();
                    $stmt->bind_result($alumniID, $hashed);

                    if ($stmt->num_rows > 0 && $stmt->fetch() && password_verify($password, $hashed)) {
                        // Regenerate session ID upon successful login for security
                        session_regenerate_id(true);

                        $_SESSION['user_type'] = "Alumni";
                        $_SESSION['user_id'] = $alumniID;
                        header("Location: homePage.php");
                        exit(); // Crucial to prevent further script execution after redirect
                    } else {
                        $error = "Invalid Student ID or Password."; // More specific error
                    }
                }
                $stmt->close(); // Close the statement
            }
        }
    }
}
// $conn->close(); // Usually closed at the end of the script or when no longer needed.
                  // Depending on your db.php, it might be closed automatically.
?>
<!DOCTYPE html>
<html>
<head>
    <title>Alumni Login | PATH</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Copied styles from facultyLogin.php / adminLogin.php */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .page-heading {
            color: #337ab7;
            font-size: 2em;
            margin-bottom: 10px;
            text-align: center;
        }

        .login-box {
            background-color: #337ab7; /* Blue background */
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            box-shadow: 10px 10px 20px rgba(0, 0, 0, 0.3);
            width: 350px;
            text-align: center;
            position: relative;
        }

        input {
            width: calc(100% - 20px);
            padding: 10px;
            margin-top: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            background-color: #fff;
            color: #000;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            background-color: #285e8e; /* Darker blue */
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        button[type="submit"]:hover {
            background-color: #1f4c73; /* Even darker blue on hover */
        }

        .login-box a { /* Style for links inside the login box */
            color: #fff; /* White link color */
            text-decoration: underline;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #337ab3; /* Red for errors */
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .bottom-right-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
        }

        .bottom-right-btn a button {
            background-color: #285e8e; /* Darker blue */
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            font-weight: normal;
        }

        .bottom-right-btn a button:hover {
            background-color: #1f4c73; /* Even darker blue on hover */
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

        .social-icons {
            text-align: center;
        }

        .social-icons a {
            padding-top: 30px;
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

<?php if (!empty($error)): ?>
    <div class="notification"><?php echo htmlspecialchars($error); // Use htmlspecialchars ?></div>
<?php endif; ?>

<div class="page-heading">ALUMNI LOG-IN</div>

<div class="login-box">
    <div class="logo-placeholder">
        <img src="/PATH/WVSUMainLogo.png" alt="West Visayas State University Logo">
    </div>
    <h2>West Visayas State University Main Campus</h2>
    <form method="post">
        <input type="text" name="student_id" placeholder="Enter your Student ID" required>
        <input type="password" name="password" placeholder="Enter your password" required>
        <button type="submit">Login</button>

        <p style="margin-top: 15px;">
            Don't have an Alumni Account yet?
            <a href="alumniSignup.php">Sign Up</a>
        </p>
    </form>

</div>

<div class="bottom-right-btn">
    <a href="rolePage.php"> <button type="button">Back to Main Page</button> </a>
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

<script>
    // JavaScript to hide the notification after 4 seconds
    setTimeout(() => {
        const note = document.querySelector('.notification');
        if (note) {
            note.style.display = 'none';
        }
    }, 4000); // 4000 milliseconds = 4 seconds
</script>

</body>
</html>