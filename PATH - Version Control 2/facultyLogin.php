<?php
// FILE: facultyLogin.php
require_once 'includes/functions.php';
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT faculty_id, password FROM faculty WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($facultyID, $hashed);

    if ($stmt->num_rows > 0 && $stmt->fetch() && password_verify($password, $hashed)) {
        $_SESSION['user_type'] = "Faculty";
        $_SESSION['user_id'] = $facultyID;
        header("Location: homePage.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Login | PATH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
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
            background-color: #337ab7;
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

        button {
            width: 100%;
            padding: 10px;
            margin-top: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            background-color: #285e8e;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #1f4c73;
        }

        a {
            color: #fff;
            text-decoration: underline;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #337ab7;
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
            background-color: #285e8e;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
        }

        .bottom-right-btn a button:hover {
            background-color: #1f4c73;
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
    <div class="notification"><?php echo $error; ?></div>
<?php endif; ?>

<div class="page-heading">FACULTY LOG-IN</div>

<div class="login-box">
    <div class="logo-placeholder">
        <img src="/PATH/WVSUMainLogo.png" alt="West Visayas State University Logo">
    </div>
    <h2>West Visayas State University Main Campus</h2>
    <form method="post">
        <input type="email" name="email" placeholder="example@wvsu.edu.ph" required>
        <input type="password" name="password" placeholder="Enter your password" required>

        <button type="submit">Login</button>

        <p style="margin-top: 15px;">
            Still don't have a Faculty Account? 
            <a href="facultySignup.php">Sign Up</a>
        </p>
    </form>

    
</div>
<div class="bottom-right-btn">
        <a href="rolePage.php">
            <button type="button" name="returnBtn">Back to Main Page</button>
        </a>
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
    }, 4000);
</script>

</body>
</html>
