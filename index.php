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
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen font-inter p-4"> <!-- Added p-4 for general page padding -->
    <div class="login-container bg-white p-8 rounded-lg shadow-md w-full max-w-md mx-auto"> <!-- Added mx-auto for horizontal centering -->
        <h2 class="text-3xl font-bold text-center text-blue-800 mb-6">Welcome to ClinicConnect</h2>
        <p class="text-center text-gray-600 mb-6">Please log in to your account</p>

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
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus-ring-blue-500"
                       placeholder="Enter your password" required>
            </div>
            <div id="errorMessage" class="text-red-500 text-center mb-4 hidden"></div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                Login
            </button>
        </form>
        <p class="text-center text-gray-500 text-sm mt-6">Don't have an account? <a href="#" class="text-blue-600 hover:underline">Register here</a></p>
        <!-- This is a placeholder for registration functionality later -->
    </div>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Custom Tailwind CSS configuration for 'Inter' font
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    },
                },
            },
        };

        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const form = event.target;
            const formData = new FormData(form);
            const errorMessageDiv = document.getElementById('errorMessage');

            fetch(form.action, {
                method: form.method,
                body: formData
            })
            .then(response => response.json()) // Expect JSON response
            .then(data => {
                if (data.success) {
                    // Redirect based on user role
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        // Fallback, though redirect_url should always be present on success
                        console.log("Login successful, but no redirect URL provided.");
                        // Potentially redirect to a default dashboard if no URL is given
                        window.location.href = 'dashboard/patient/index.php'; // Example default
                    }
                } else {
                    errorMessageDiv.textContent = data.message || "An unknown error occurred.";
                    errorMessageDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessageDiv.textContent = 'An error occurred during login. Please try again.';
                errorMessageDiv.classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
