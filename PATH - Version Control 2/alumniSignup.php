<?php
// FILE: alumniSignup.php
session_start(); // START SESSION AT THE VERY TOP!

// Include PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function or conditional
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (if you are using Composer)
// require 'vendor/autoload.php';
// If not using Composer, include the files manually:
require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';


require_once 'includes/functions.php'; // Assuming this is needed and contains isValidPassword
require 'db.php'; // Your database connection file

$error = ''; // Initialize error variable
$success_message = ''; // Initialize success message variable
$debug_output = ''; // Variable to store debug output

// Function to validate WVSU email format
function isValidWVSUEmail($email) {
    // Basic email format check and check if it ends with @wvsu.edu.ph
    return filter_var($email, FILTER_VALIDATE_EMAIL) && str_ends_with($email, '@wvsu.edu.ph');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the specific form fields were submitted
    if (isset($_POST['student_id'], $_POST['last_name'], $_POST['first_name'], $_POST['middle_name'], $_POST['email'], $_POST['password'])) {

        $student_id = trim($_POST['student_id']);
        $last_name = trim($_POST['last_name']);
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name']); // Middle name can be empty
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // --- Input Validation ---
        // Student ID Format: Example assumes 4 digits, 1 uppercase letter, 4 digits. Adjust regex if needed.
        if (!preg_match("/^\d{4}[A-Z]\d{4}$/", $student_id)) {
            $error = "Invalid student ID format (e.g., 1234A5678).";
        } elseif (empty($last_name) || empty($first_name) || empty($email) || empty($password)) {
             $error = "Please fill out all required fields (Last Name, First Name, Email, Password).";
        } elseif (!isValidWVSUEmail($email)) { // Validate WVSU email format
            $error = "Email must be a valid @wvsu.edu.ph address.";
        } elseif (!isValidPassword($password)) { // Assumes function checks complexity (min 8 chars, upper, lower, number)
            $error = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number.";
        } else {
            // --- Check if Student ID or Email already exists ---
            $checkStmt = $conn->prepare("SELECT alumni_id FROM alumni WHERE student_id = ? OR email = ?");
            if ($checkStmt === false) {
                error_log("Prepare failed (check ID/email): (" . $conn->errno . ") " . $conn->error);
                $error = "An internal error occurred during validation. Please try again.";
            } else {
                $checkStmt->bind_param("ss", $student_id, $email);
                $checkStmt->execute();
                $checkStmt->store_result();
                if ($checkStmt->num_rows > 0) {
                    // Check which one exists
                    $checkStmt->bind_result($existing_alumni_id);
                    $checkStmt->fetch(); // Fetch one result to check

                    // A more detailed check would involve fetching the actual student_id and email
                    // but for simplicity, we'll just say one of them exists.
                    // You might want to refine this to tell the user if it's the ID or email.
                    $error = "This Student ID or Email address is already registered. Please login.";

                } else {
                    // --- Proceed with Signup ---
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    // --- Generate Alumni ID based on the highest existing ID ---
                    $result = $conn->query("SELECT alumni_id FROM alumni ORDER BY alumni_id DESC LIMIT 1");
                    if ($result === false) {
                        error_log("ID query failed: (" . $conn->errno . ") " . $conn->error);
                        $error = "An internal error occurred generating ID. Please try again.";
                        $alumniID = null;
                    } else {
                        $row = $result->fetch_assoc();
                        if ($row && preg_match('/AlumniID(\d+)/', $row['alumni_id'], $matches)) {
                            $nextNum = intval($matches[1]) + 1;
                            $alumniID = "AlumniID" . str_pad($nextNum, 3, "0", STR_PAD_LEFT);
                        } else {
                            $alumniID = "AlumniID001";
                        }
                        $result->free();
                    }

                    if ($alumniID) { // Only proceed if ID generation succeeded
                        // Prepare the INSERT statement with new columns
                        $stmt = $conn->prepare("INSERT INTO alumni (alumni_id, student_id, last_name, first_name, middle_name, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt === false) {
                             error_log("Prepare failed (insert): (" . $conn->errno . ") " . $conn->error);
                             $error = "An internal error occurred during registration. Please try again.";
                        } else {
                            // Bind parameters including the new fields
                            $stmt->bind_param("sssssss", $alumniID, $student_id, $last_name, $first_name, $middle_name, $email, $hash);
                            if ($stmt->execute()) {
                                // --- Successful database insert ---

                                // --- PHPMailer Email Sending Logic ---
                                $mail = new PHPMailer(true); // Pass true to enable exceptions

                                try {
                                    //Server settings
                                    // Enable verbose debug output *only if needed for debugging*
                                    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                                    // Capture debug output to a variable
                                    $mail->Debugoutput = function($str, $level) use (&$debug_output) {
                                        $debug_output .= $str;
                                    };

                                    $mail->isSMTP(); // Send using SMTP
                                    $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through (Gmail SMTP)
                                    $mail->SMTPAuth   = true; // Enable SMTP authentication
                                    $mail->Username   = 'pathwvsu2025@gmail.com'; // SMTP username (your Gmail address)
                                    $mail->Password   = 'wqez aadd zwbd dlvp'; // SMTP password (App Password)
                                    // Use SMTPS (implicit TLS) on port 465 for Gmail
                                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                    $mail->Port       = 465;

                                    //Recipients
                                    $mail->setFrom('pathwvsu2025@gmail.com', 'WVSU PATH Team'); // Sender email and name
                                    $mail->addAddress($email, $first_name . ' ' . $last_name); // Add a recipient

                                    // Content
                                    $mail->isHTML(true); // Set email format to HTML
                                    $mail->Subject = 'Welcome to WVSU PATH!';
                                    $mail->Body    = "Dear " . htmlspecialchars($first_name) . ",<br><br>";
                                    $mail->Body   .= "Thank you for registering with the West Visayas State University Post Alumni Tracking Hub (PATH).<br>";
                                    $mail->Body   .= "Your Alumni ID is: <strong>" . htmlspecialchars($alumniID) . "</strong><br>";
                                    $mail->Body   .= "You can now log in using your Student ID (" . htmlspecialchars($student_id) . ") and the password you created.<br><br>";
                                    $mail->Body   .= "Sincerely,<br>The WVSU PATH Team";

                                    $mail->AltBody = "Dear " . htmlspecialchars($first_name) . ",\n\n";
                                    $mail->AltBody .= "Thank you for registering with the West Visayas State University Post Alumni Tracking Hub (PATH).\n";
                                    $mail->AltBody .= "Your Alumni ID is: " . htmlspecialchars($alumniID) . "\n";
                                    $mail->AltBody .= "You can now log in using your Student ID (" . htmlspecialchars($student_id) . ") and the password you created.\n\n";
                                    $mail->AltBody .= "Access the login page here: http://yourwebsite.com/alumniLogin.php \n\n"; // Replace with your actual login page URL
                                    $mail->AltBody .= "Sincerely,\nThe WVSU PATH Team";


                                    $mail->send();
                                    $success_message = "Registration successful! A confirmation email has been sent to your WVSU email address.";

                                } catch (Exception $e) {
                                    // Capture the error message and debug output
                                    $error = "Registration successful! However, we could not send a confirmation email at this time. Mailer Error: " . $mail->ErrorInfo . "<br><br>Debug Output:<br>" . nl2br(htmlspecialchars($debug_output));
                                    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                                    error_log("Mailer Debug Output:\n" . $debug_output); // Log debug output to server logs
                                }
                                // --- End PHPMailer Email Sending Logic ---

                                // Redirect to alumniLogin.php after successful registration
                                // ONLY if there was no error during the database insert AND no email sending error
                                if (empty($error) && empty($success_message)) { // Check both error and success_message to be safe
                                     // This case should ideally not be reached if DB insert was successful but email failed
                                     // If email sending fails, $error will be populated
                                } elseif (empty($error)) {
                                     // If DB insert was successful and email sending succeeded or failed gracefully
                                     header("Location: alumniLogin.php?signup=success"); // Add a query parameter to indicate success
                                     exit(); // Crucial after redirect
                                }


                            } else {
                                error_log("Execute failed (insert): (" . $stmt->errno . ") " . $conn->error); // Corrected error logging variable
                                // Check for duplicate entry specifically (though the check above should prevent it)
                                if ($stmt->errno == 1062) { // 1062 is MySQL error code for duplicate entry
                                    $error = "This Student ID or Email address is already registered.";
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
        // Handle cases where form fields might be missing, though 'required' should prevent this in browsers
        $error = "Please fill out all required fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Alumni Signup | PATH</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Copied styles from login pages */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            height: 100vh;
            margin: 0;
            display: block; /* Remove flex and centering */
        }

        .page-heading {
            color: #337ab7;
            font-size: 2em;
            margin-bottom: 10px;
            margin-top: 40px; /* Add this for spacing from the top */
            text-align: center;
        }

        .login-box { /* Reusing login-box class for consistency */
            background-color: #337ab7; /* Blue background */
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            box-shadow: 10px 10px 20px rgba(0, 0, 0, 0.3);
            width: 450px; /* Slightly wider to accommodate more fields */
            text-align: center;
            position: relative;
            margin: 0 auto 30px auto; /* Center horizontally, add space below */
        }

        /* Style for input containers (for first/middle name row) */
        .input-row {
            display: flex;
            gap: 10px; /* Space between inputs in the row */
            margin-top: 12px;
        }

        .input-row input {
            width: 100%; /* Allow inputs in the row to take up available space */
            margin-top: 0; /* Remove top margin here, handled by .input-row */
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
            margin-top: 20px; /* More space before button */
            border: none;
            border-radius: 5px;
            font-size: 14px;
            background-color: #285e8e; /* Green for signup/success actions */
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        button[type="submit"]:hover {
            background-color: #1f4c73; /* Darker green on hover */
        }

        .login-box a { /* Style for links inside the box */
            color: #fff;
            text-decoration: underline;
        }

        /* Notification style */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #c0392b; /* Red for errors */
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: fadeIn 0.5s ease-out;
        }

        .success-notification {
             position: fixed;
             top: 20px;
             right: 20px;
             background-color: #28a745; /* Green for success */
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

        /* Bottom Right Button Style */
        .bottom-right-btn {
            /* Position relative to body now */
            position: fixed; /* Fixed position relative to viewport */
            bottom: 20px;
            right: 20px;
             z-index: 10; /* Ensure it's above general content but below notification */
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

        button[type="submit"]:hover {
            background-color: #1f4c73; /* Even darker blue on hover */
        }

        /* Optional: Add some helper text style */
        .input-hint {
            font-size: 0.85em;
            color: #eee; /* Lighter text color for hints inside the box */
            text-align: left;
            margin-top: 5px;
            margin-left: 5px;
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

<?php if (!empty($success_message)): ?>
    <div class="success-notification"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>


<div class="page-heading">ALUMNI SIGN-UP</div>

<div class="login-box">
    <div class="logo-placeholder">
        <img src="/PATH/WVSUMainLogo.png" alt="West Visayas State University Logo">
    </div>
    <h2>West Visayas State University Main Campus</h2>
    <form method="post" novalidate>
        <input type="text" name="student_id" placeholder="Enter Student ID" required pattern="\d{4}[A-Z]\d{4}" title="Format: 4 digits, 1 uppercase letter, 4 digits (e.g., 1234A5678)">
        <div class="input-hint">Format: 2025M1234</div>

        <input type="text" name="last_name" placeholder="Last Name" required>

        <div class="input-row">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="middle_name" placeholder="Middle Name (Optional)">
        </div>

        <input type="email" name="email" placeholder="WVSU Email (@wvsu.edu.ph)" required title="Must be a valid @wvsu.edu.ph email address.">
        <div class="input-hint">Must end with @wvsu.edu.ph</div>

        <input type="password" name="password" placeholder="Create password" required pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" title="Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, and a number.">
             <div class="input-hint">Min 8 characters, include: Upper, Lower, Number</div>

        <button type="submit">Sign Up</button>

        <p style="margin-top: 15px;">
            Already have an account?
            <a href="alumniLogin.php">Login</a>
        </p>
    </form>
</div>

<div class="bottom-right-btn">
    <a href="alumniLogin.php"> <button type="button">Back to Alumni Login</button> </a>
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
    // JavaScript to hide the notification after 5 seconds
    setTimeout(() => {
        const note = document.querySelector('.notification');
        if (note) {
            note.style.display = 'none';
        }
        const successNote = document.querySelector('.success-notification');
        if (successNote) {
             successNote.style.display = 'none';
        }
    }, 5000);
</script>

</body>
</html>

