<?php
session_start(); // Always start the session at the very top of any page that uses sessions
require_once 'includes/db_connection.php';
require_once 'includes/config.php';

$message = ''; // Initialize message variable

// Check if there's a registration success message from the register page
if (isset($_GET['registration']) && $_GET['registration'] == 'success') {
    $message = "Registration successful! Please log in.";
}

// Handle login attempt when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password']; // Password is not escaped here as it will be verified against its hash

    // Prepare a statement to fetch user details by username
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username); // 's' denotes a string parameter
    $stmt->execute();
    $result = $stmt->get_result(); // Get the result set

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc(); // Fetch the user data as an associative array

        // Verify the submitted password against the hashed password from the database
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables to log the user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect user based on their role
            switch ($user['role']) {
                case 'admin':
                    header("Location: " . BASE_URL . "admin/dashboard.php");
                    exit(); // Always exit after a header redirect
                case 'assistant':
                    header("Location: " . BASE_URL . "assistants/dashboard.php");
                    exit();
                case 'patient':
                    header("Location: " . BASE_URL . "patients/dashboard.php");
                    exit();
                default:
                    // Fallback for unknown roles (shouldn't happen if roles are strictly defined)
                    $message = "Unknown user role. Please contact support.";
                    break;
            }
        } else {
            // Incorrect password
            $message = "Invalid username or password.";
        }
    } else {
        // Username not found
        $message = "Invalid username or password.";
    }
    $stmt->close(); // Close the prepared statement
}
$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Login</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-container { background-color: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        h2 { color: #333; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; margin-bottom: 8px; color: #555; font-weight: bold; }
        input[type="text"], input[type="password"] {
            width: calc(100% - 22px); /* Adjust for padding and border */
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 18px; font-weight: bold;
            transition: background-color 0.3s ease;
        }
        button:hover { background-color: #218838; }
        .message { margin-bottom: 20px; padding: 10px; border-radius: 4px; font-size: 14px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .register-link { margin-top: 25px; font-size: 15px; }
        .register-link a { color: #007bff; text-decoration: none; font-weight: bold; }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Clinic Login</h2>
        <?php if (!empty($message)): ?>
            <p class="message <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="register-link">
            Don't have an account? <a href="<?php echo BASE_URL; ?>register.php">Register as Patient</a>
        </div>
    </div>
</body>
</html>