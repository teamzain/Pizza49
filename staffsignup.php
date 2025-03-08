<?php
// Start the session
ob_start();

session_start();
include 'loader.html';
// Include database configuration
include 'db/config.php';

// Initialize variables
$error = "";
$success = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = $_POST["role"]; // Capture role input
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
        
        // Try to execute the statement
        try {
            $stmt->execute();
            $success = "New user registered successfully. You can now log in.";
            header("Location: staff_access_authentication.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Error code for duplicate entry
                if (strpos($e->getMessage(), 'users.name') !== false) {
                    $error = "This username is already taken. Please choose a different one.";
                } elseif (strpos($e->getMessage(), 'users.email') !== false) {
                    $error = "This email is already registered. Please use a different email or try logging in.";
                } else {
                    $error = "A user with this information already exists.";
                }
            } else {
                $error = "An unexpected error occurred. Please try again later.";
            }
        }
        
        $stmt->close();
    }
}

$conn->close();
ob_end_flush();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dani's Fabric - Sign Up</title>
    <style>


        /* General styling for input fields */
input[type="text"], input[type="email"], input[type="password"], select {
    width: 100%;
    padding: 10px 0;
    border: none;
    border-bottom: 2px solid #ddd;
    outline: none;
    transition: border-color 0.3s;
    font-size: 16px;
    background-color: transparent; /* Ensure background is transparent */
}

/* Focus styling for input fields */
input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, select:focus {
    border-color: #2c7873;
}

/* Styling for the select element specifically */
select {
    background-color: transparent; /* Remove default background */
    -webkit-appearance: none; /* Remove default styling in WebKit browsers */
    -moz-appearance: none; /* Remove default styling in Firefox */
    appearance: none; /* Remove default styling in modern browsers */
    padding: 10px;
    font-size: 16px;
    cursor: pointer; /* Change cursor to pointer */
}

/* Styling for the select container to ensure proper display */
.input-group {
    position: relative;
    margin-bottom: 20px;
}

label {
    position: absolute;
    top: 10px;
    left: 0;
    pointer-events: none;
    transition: 0.3s ease all;
    color: #999;
}

select:focus ~ label, select:not(:placeholder-shown) ~ label {
    top: -20px;
    font-size: 12px;
    color: #2c7873;
}

/* Custom arrow for select element */
.input-group::after {
    content: '▼';
    font-size: 12px;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #999;
}

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
            color: black;
            text-align: center;
            padding: 20px 0;
            font-size: 2.5em;
            font-weight: 700;
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

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px 0;
            border: none;
            border-bottom: 2px solid #ddd;
            outline: none;
            transition: border-color 0.3s;
            font-size: 16px;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
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

        .social-signup {
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

        .error-message, .success-message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .error-message {
            background-color: #fce4e4;
            border: 1px solid #fcc2c3;
            color: #cc0033;
        }

        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
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
    </style>
 
<body>
    <header class="header">
        Dani's Fabric
    </header>
    <div class="container">
        <div class="left-section">
            <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
            </div>
            <h1>Already a member?</h1>
            <p>Sign in to access your account and explore our fabric collection!</p>
            <button onclick="goToLogin()">Explore More </button>
        </div>
        <div class="right-section">
            <h1>Create Account</h1>
            <?php
            if (!empty($error)) {
                echo "<div class='error-message'>$error</div>";
            }
            if (!empty($success)) {
                echo "<div class='success-message'>$success</div>";
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group">
                    <input type="text" name="name" required placeholder=" ">
                    <label>Full Name</label>
                </div>
                <div class="input-group">
                    <input type="email" name="email" required placeholder=" ">
                    <label>Email</label>
                </div>

                <div class="input-group">
    <select name="role" required>
        <option value="">Select Role</option>
        <option value="staff">Staff</option>
    </select>
    <label for="role">Role</label>
</div>




                <div class="input-group">
                    <input type="password" name="password" required placeholder=" ">
                    <label>Password</label>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" required placeholder=" ">
                    <label>Confirm Password</label>
                </div>
                
                <button type="submit">Add User</button>
            </form>
          
        </div>
    </div>

    <script>
        function goToLogin() {
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>
        