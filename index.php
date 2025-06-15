<?php
session_start(); // Start the session
// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'patient') {
        header("Location: dashboard/patient/index.php");
        exit();
    } elseif ($_SESSION['user_role'] === 'assistant') {
        header("Location: dashboard/assistant/index.php");
        exit();
    } elseif ($_SESSION['user_role'] === 'admin') {
        header("Location: dashboard/admin/index.php");
        exit();
    }
}

// Check for messages passed from registration or password change
$message = '';
$message_type = '';
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Appointment System - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Inter font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Tailwind CSS configuration for 'Inter' font */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Any other custom styles from style.css can be inlined here or kept in style.css */
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen font-inter p-4">
    <div class="login-container bg-white p-8 rounded-lg shadow-md w-full max-w-md mx-auto">
        <h2 class="text-3xl font-bold text-center text-blue-800 mb-6">Welcome to ClinicConnect</h2>
        <p class="text-center text-gray-600 mb-6">Please log in to your account</p>

        <?php if (!empty($message)): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
                <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="actions/login.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                <input type="email" id="email" name="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter your email" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <input type="password" id="password" name="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter your password" required>
            </div>
            <div id="errorMessage" class="text-red-500 text-center mb-4 hidden"></div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                Login
            </button>
        </form>
        <p class="text-center text-gray-500 text-sm mt-6">Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Register here</a></p>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const form = event.target;
            const formData = new FormData(form);
            const errorMessageDiv = document.getElementById('errorMessage');
            errorMessageDiv.classList.add('hidden'); // Hide previous errors

            fetch(form.action, {
                method: form.method,
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    // If response is not OK (e.g., 404, 500), parse as text for debugging
                    return response.text().then(text => { throw new Error(text) });
                }
                return response.json(); // Expect JSON response
            })
            .then(data => {
                if (data.success) {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        console.log("Login successful, but no redirect URL provided. Redirecting to default patient dashboard.");
                        window.location.href = 'dashboard/patient/index.php';
                    }
                } else {
                    errorMessageDiv.textContent = data.message || "An unknown error occurred.";
                    errorMessageDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessageDiv.textContent = `An error occurred during login: ${error.message || 'Please check server logs.'}`;
                errorMessageDiv.classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
