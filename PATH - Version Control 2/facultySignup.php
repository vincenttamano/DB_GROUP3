<?php
// FILE: facultySignup.php
// --- PHP Code remains the same as in your provided facultySignup.php ---
// --- Make sure session_start() is at the VERY TOP if not already ---
session_start(); // ADD THIS LINE AT THE VERY TOP if it's missing

require_once 'includes/functions.php'; // Assuming this defines isValidFacultyEmail & isValidPassword
require 'db.php';

$error = ''; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the specific form fields were submitted
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']); // Trim whitespace
        $password = $_POST['password'];

        // --- Input Validation ---
        if (!isValidFacultyEmail($email)) { // Assumes function checks for @wvsu.edu.ph
            $error = "Email must end with @wvsu.edu.ph";
        } elseif (!isValidPassword($password)) { // Assumes function checks complexity
            $error = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number.";
        } else {
             // --- Check if Email already exists ---
             $checkStmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE email = ?");
             if ($checkStmt === false) {
                 error_log("Prepare failed (check email): (" . $conn->errno . ") " . $conn->error);
                 $error = "An internal error occurred during validation. Please try again.";
             } else {
                 $checkStmt->bind_param("s", $email);
                 $checkStmt->execute();
                 $checkStmt->store_result();
                 if ($checkStmt->num_rows > 0) {
                     $error = "This Email address is already registered. Please login.";
                 } else {
                     // --- Proceed with Signup ---
                     $hash = password_hash($password, PASSWORD_DEFAULT);

                    // --- Generate Faculty ID (Consider potential race conditions - same note as alumni) ---
                    $result = $conn->query("SELECT COUNT(*) AS count FROM faculty");
                     if ($result === false) {
                         error_log("Count query failed (faculty): (" . $conn->errno . ") " . $conn->error);
                         $error = "An internal error occurred generating ID. Please try again.";
                         $facultyID = null;
                     } else {
                         $row = $result->fetch_assoc();
                         $facultyID = "FacultyID" . str_pad($row['count'] + 1, 3, "0", STR_PAD_LEFT);
                         $result->free();
                     }

                     if ($facultyID) {
                         $stmt = $conn->prepare("INSERT INTO faculty (faculty_id, email, password) VALUES (?, ?, ?)");
                          if ($stmt === false) {
                              error_log("Prepare failed (insert faculty): (" . $conn->errno . ") " . $conn->error);
                              $error = "An internal error occurred during registration. Please try again.";
                          } else {
                             $stmt->bind_param("sss", $facultyID, $email, $hash);
                             if ($stmt->execute()) {
                                 // --- Successful signup ---
                                 // Redirect to facultyLogin.php instead of auto-logging in and going to homePage.php
                                 header("Location: facultyLogin.php");
                                 exit(); // Crucial after redirect
                             } else {
                                 error_log("Execute failed (insert faculty): (" . $stmt->errno . ") " . $stmt->error);
                                 if ($stmt->errno == 1062) { // Duplicate entry
                                     $error = "This Email address is already registered.";
                                 } else {
                                     $error = "Registration failed due to an internal error. Please try again.";
                                 }
                             }
                             $stmt->close();
                          }
                     }
                 }
                 $checkStmt->close();
             }
        }
    } else {
        $error = "Please fill out all required fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Signup | PATH</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* --- Styles remain the same as in your provided facultySignup.php --- */
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

        /* Changed class name to login-box for consistency with others */
        .login-box {
            background-color: #337ab7;
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            box-shadow: 10px 10px 20px rgba(0, 0, 0, 0.3);
            width: 380px; /* Match width with alumni signup */
            text-align: center;
            position: relative; /* Added for positioning context if needed */
        }

        /* Adjusted input style slightly for consistency */
         input {
             width: calc(100% - 20px); /* Match other pages */
             padding: 10px;
             margin-top: 12px;
             border: none;
             border-radius: 5px;
             font-size: 14px;
             background-color: #fff;
             color: #000;
          }


        button[type="submit"] { /* Specific styling for submit button */
             width: 100%; /* Make button full width like input */
             padding: 10px;
             margin-top: 20px; /* More space before button */
             border: none;
             border-radius: 5px;
             font-size: 14px;
             background-color: #285e8e; /* Green for signup */
             color: white;
             cursor: pointer;
             font-weight: bold;
         }

        button[type="submit"]:hover {
             background-color: #1f4c73; /* Darker green on hover */
         }

         /* Style for links inside the box */
         .login-box a {
             color: #fff;
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

        /* Removed small ul style as hints are now divs */

        .bottom-right-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 10;
        }

        .bottom-right-btn a button {
            background-color: #285e8e;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            font-weight: normal;
        }

        .bottom-right-btn a button:hover {
            background-color: #1f4c73;
        }

        /* Input hint style (already present in your code) */
        .input-hint {
            font-size: 0.85em;
            color: #eee;
            text-align: left;
            margin-top: 5px;
            margin-left: 5px; /* Adjust as needed */
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
    <div class="notification"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div class="page-heading">FACULTY SIGN-UP</div>

<div class="login-box">
    <div class="logo-placeholder">
        <img src="/PATH/WVSUMainLogo.png" alt="West Visayas State University Logo">
    </div>
    <h2>West Visayas State University Main Campus</h2>
    <form method="post" novalidate>
        <input type="email" name="email" required placeholder="Enter your @wvsu.edu.ph email" title="Must be a valid @wvsu.edu.ph email address.">
        <div class="input-hint">Must end with @wvsu.edu.ph</div>

        <input type="password" name="password" required placeholder="Create password" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" title="Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, and a number.">
        <div class="input-hint">Min 8 characters, include: Upper, Lower, Number</div>

        <button type="submit">Sign Up</button>

         <p style="margin-top: 15px;">
             Already have an account?
             <a href="facultyLogin.php">Login</a>
         </p>
    </form>
</div>

<div class="bottom-right-btn">
    <a href="facultyLogin.php"> <button type="button">Back to Faculty Login</button> </a>
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
    setTimeout(() => {
        const note = document.querySelector('.notification');
        if (note) note.style.display = 'none';
    }, 5000); // Match alumni signup notification duration
</script>

</body>
</html>
