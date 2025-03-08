<?php
ob_start();
session_start();
include 'loader.html';
include 'db/config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $success = "Login successful. Welcome, " . $user['name'] . "!";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid name or password.";
        }
    } else {
        $error = "Invalid name or password.";
    }

    $stmt->close();
}

$conn->close();
ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza 49 - Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
        }

        .header {
            /* background-color: #2c7873; */
            color: black;
            text-align: center;
            padding: 20px 0;
            font-size: 2.5em;
            font-weight: 700;
            /* box-shadow: 0 2px 5px rgba(0,0,0,0.1); */
        }

        .container {
            display: flex;
            flex-direction: row;
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            flex-grow: 1;
        }

        .left-section {
            flex: 1;
            background: linear-gradient(135deg, #2c7873, #52de97);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .right-section {
            flex: 1;
            background-color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h1 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px 0;
            border: none;
            border-bottom: 2px solid #ddd;
            outline: none;
            transition: border-color 0.3s;
            font-size: 16px;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #2c7873;
        }

        label {
            position: absolute;
            top: 10px;
            left: 0;
            pointer-events: none;
            transition: 0.3s ease all;
            color: #999;
        }

        input:focus ~ label, input:not(:placeholder-shown) ~ label {
            top: -20px;
            font-size: 12px;
            color: #2c7873;
        }

        button {
            background-color: #2c7873;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            font-size: 16px;
        }

        button:hover {
            background-color: #52de97;
            transform: translateY(-2px);
        }
        .social-login {
            margin-top: 20px;
        }

        .social-icon {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-right: 10px;
            transition: background-color 0.3s, color 0.3s;
        }

        .social-icon:hover {
            background-color: #2c7873;
            color: white;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            top: 0;
            left: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.1);
            animation: float 15s infinite linear;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-duration: 45s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            right: 10%;
            top: 30%;
            animation-duration: 30s;
        }

        .shape:nth-child(3) {
            width: 120px;
            height: 120px;
            bottom: 20%;
            left: 20%;
            animation-duration: 60s;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-100vh) rotate(360deg); }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                width: 95%;
            }

            .left-section, .right-section {
                padding: 30px;
            }

            .header {
                font-size: 2em;
                padding: 15px 0;
            }

            .shape:nth-child(1) {
                width: 60px;
                height: 60px;
            }

            .shape:nth-child(2) {
                width: 40px;
                height: 40px;
            }

            .shape:nth-child(3) {
                width: 80px;
                height: 80px;
            }
        }

        @media (max-width: 480px) {
            .container {
                width: 100%;
                border-radius: 0;
            }

            .left-section, .right-section {
                padding: 20px;
            }

            .header {
                font-size: 1.8em;
                padding: 10px 0;
            }

            button {
                width: 100%;
            }

            .social-icon {
                width: 35px;
                height: 35px;
                line-height: 35px;
                margin-right: 5px;
            }
        }
        .signup-link {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            background-color: #2c7873;
            color: white;
            text-decoration: none;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .signup-link:hover {
            background-color: #52de97;
            transform: translateY(-2px);
        }
  



        .popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 20px;
            transform: translateX(-50%);
            padding: 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            animation: fadeInOut 5s ease-in-out;
        }

        .error-popup {
            background-color: #cc0033;
        }

        .success-popup {
            background-color: #28a745;
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0; }
            10%, 90% { opacity: 1; }
        }
    </style>
</head>
<body>
    <header class="header">
        Pizza 49
    </header>

    <div id="errorPopup" class="popup error-popup"></div>
    <div id="successPopup" class="popup success-popup"></div>

    <div class="container">
        <div class="left-section">
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>
            <h1>New here?</h1>
            <p>Join our fabric community and unlock a world of textile possibilities!</p>
            <button onclick="goToSignup()">Explore More</button>
        </div>
        
        <div class="right-section">
            <h1>Welcome Back</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group">
                    <input type="text" name="name" required placeholder=" ">
                    <label>Username</label>
                </div>
                <div class="input-group">
                    <input type="password" name="password" required placeholder=" ">
                    <label>Password</label>
                </div>
                <a href="#" style="color: #2c7873; text-decoration: none; margin-bottom: 20px; display: inline-block;">Forgot Password?</a>
                <br>
                <button type="submit">LOGIN</button>
            </form>
            <div class="social-login">
                <p>Or Sign in with</p>
                <div>
                    <span class="social-icon">f</span>
                    <span class="social-icon">t</span>
                    <span class="social-icon">G</span>
                    <span class="social-icon">in</span>
                </div>
                <br>
                 <a href="signup.php" class="signup-link">Sign Up</a>
            </div>
        </div>
    </div>

    <script>
        function goToSignup() {
            console.log("Navigating to signup page...");
            window.location.href = 'signup.php';
        }

        function showPopup(message, isError) {
            const popup = document.getElementById(isError ? 'errorPopup' : 'successPopup');
            popup.textContent = message;
            popup.style.display = 'block';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 5000);
        }

        <?php
        if (!empty($error)) {
            echo "showPopup(" . json_encode($error) . ", true);";
        }
        if (!empty($success)) {
            echo "showPopup(" . json_encode($success) . ", false);";
        }
        ?>
    </script>
</body>
</html>