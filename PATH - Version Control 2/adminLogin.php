<?php
session_start();
require 'db.php'; // Adjust the path if needed

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT admin_password FROM admin WHERE admin_username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($db_password);
            $stmt->fetch();

            // If you store hashed passwords, use password_verify($password, $db_password)
            if ($password === $db_password) {
                $_SESSION['user_type'] = "Admin";
                $_SESSION['user_id'] = $username;
                header("Location: homePage.php");
                exit();
            } else {
                $error = "Invalid Admin Credentials!";
            }
        } else {
            $error = "Invalid Admin Credentials!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login | PATH</title>
    <!-- Font Awesome CDN for social icons -->
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

        button[type="submit"] {
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

        button[type="submit"]:hover {
            background-color: #1f4c73;
        }

        .login-box a {
            color: #fff;
            text-decoration: underline;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #337ab3;
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
            position: fixed;
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
            font-weight: normal;
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
    <div class="notification"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="page-heading">ADMIN LOG-IN</div>

<div class="login-box">
    <div class="logo-placeholder">
        <img src="/PATH/WVSUMainLogo.png" alt="West Visayas State University Logo">
    </div>
    <h2>West Visayas State University Main Campus</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Enter Admin Name" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit">Login</button>
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

<div class="bottom-right-btn">
    <a href="rolePage.php"> <button type="button">Back to Main Page</button> </a>
</div>


<script>
    setTimeout(() => {
        const note = document.querySelector('.notification');
        if (note) {
            note.style.display = 'none';
        }
    }, 4000);
</script>

 

</body>
</html>
