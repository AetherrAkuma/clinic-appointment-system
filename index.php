<?php
// index.php - Login Page
session_start(); // Start the session at the very beginning

// Check if user is already logged in, redirect to their dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'patient':
            header("Location: dashboard/patient/index.php");
            break;
        case 'assistant':
            header("Location: dashboard/assistant/index.php");
            break;
        case 'admin':
            header("Location: dashboard/admin/index.php");
            break;
    }
    exit();
}

// Check for messages from login_action.php or logout.php
$message = '';
$message_type = ''; // 'success' or 'error'

if (isset($_SESSION['login_message'])) {
    $message = $_SESSION['login_message'];
    $message_type = $_SESSION['login_message_type'];
    unset($_SESSION['login_message']); // Clear the message after displaying
    unset($_SESSION['login_message_type']);
} elseif (isset($_GET['message'])) { // For messages like after password change/logout
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type'] ?? 'info'); // Default to info type
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ClinicConnect</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-image: url("https://png.pngtree.com/thumb_back/fh260/background/20240522/pngtree-abstract-blur-hospital-clinic-medical-interior-background-image_15683664.jpg");
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            repeat: no-repeat;
            background-size: cover;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md mx-auto login-container">
        <div class="text-center mb-6">
            <!-- Local Image Placeholder -->
            <img src="assets/images/clinic_logo.png" alt="Clinic Logo" class="mx-auto w-32 h-32 object-contain mb-4 rounded-full border-2 border-purple-200 shadow-md">
            <h1 class="text-4xl font-extrabold text-purple-800 mb-2">ClinicConnect</h1>
            <p class="text-gray-600 text-lg">Login to your account</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : ($message_type === 'error' ? 'bg-red-100 text-red-800 border-red-400' : 'bg-blue-100 text-blue-800 border-blue-400'); ?> border-l-4 shadow-sm" role="alert">
                <p class="font-bold"><?php echo htmlspecialchars(ucfirst($message_type)); ?>!</p>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="actions/login.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                <input type="email" id="email" name="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                       placeholder="your@example.com" required autocomplete="email">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <input type="password" id="password" name="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                       placeholder="••••••••" required autocomplete="current-password">
            </div>
            <button type="submit"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 transition duration-300 ease-in-out transform hover:scale-105">
                Login
            </button>
        </form>

        <p class="text-center text-gray-600 text-sm mt-6">
            Don't have an account?
            <a href="register.php" class="text-purple-600 hover:text-purple-800 font-semibold hover:underline">Register here</a>
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');

            if (loginForm) {
                loginForm.addEventListener('submit', async function(event) {
                    event.preventDefault(); // Prevent default form submission

                    const formData = new FormData(loginForm);
                    const responseDiv = loginForm.previousElementSibling; // Assuming message div is right before the form

                    try {
                        const response = await fetch(loginForm.action, {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        // Clear previous messages
                        responseDiv.innerHTML = '';
                        responseDiv.className = 'mb-4 p-4 rounded-md border-l-4 shadow-sm';

                        if (result.success) {
                            responseDiv.classList.add('bg-green-100', 'text-green-800', 'border-green-400');
                            responseDiv.innerHTML = `<p class="font-bold">Success!</p><p>${result.message}</p>`;
                            if (result.redirect_url) {
                                setTimeout(() => {
                                    window.location.href = result.redirect_url;
                                }, 500); // Redirect after a short delay
                            }
                        } else {
                            responseDiv.classList.add('bg-red-100', 'text-red-800', 'border-red-400');
                            responseDiv.innerHTML = `<p class="font-bold">Error!</p><p>${result.message}</p>`;
                        }
                    } catch (error) {
                        console.error('Login error:', error);
                        responseDiv.innerHTML = `<p class="font-bold">Error!</p><p>An unexpected error occurred. Please try again.</p>`;
                        responseDiv.classList.add('bg-red-100', 'text-red-800', 'border-red-400');
                    }
                });
            }
        });
    </script>
</body>
</html>
