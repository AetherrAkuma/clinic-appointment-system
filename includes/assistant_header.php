<?php
// includes/assistant_header.php
session_start(); // Ensure session is started for all pages that include this header

// Check if user is logged in, and if they are an assistant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistant') {
    header("Location: /clinic-management/index.php"); // Redirect to login page if not authorized
    exit();
}

// Include database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/clinic-management/config/db_connection.php';

// Fetch assistant's first name for display in the header
$assistant_id = $_SESSION['user_id'];
$assistant_name = "Assistant"; // Default name

$stmt = $conn->prepare("SELECT FirstName FROM AssistantTBL WHERE AssistantID = ?");
if ($stmt) {
    $stmt->bind_param("i", $assistant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $assistant_name = $row['FirstName'];
    }
    $stmt->close();
}
// Note: $conn is kept open here to be used by the page including this header.
// It will be closed in the footer.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Assistant Dashboard'; ?> - ClinicConnect</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom Tailwind CSS configuration for 'Inter' font */
        html, body {
            height: 100%; /* Ensure html and body take full height */
        }
        body {
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
        }
        .main-content {
            flex-grow: 1; /* Allows content area to take available space */
        }
    </style>
    <!-- You can add specific CSS files per page if needed here -->
</head>
<body class="bg-gray-100 font-inter">
    <div class="flex flex-col min-h-screen">
        <!-- Top Navigation Bar -->
        <nav class="bg-gradient-to-r from-teal-700 to-teal-900 p-4 shadow-lg"> <!-- Teal theme for Assistant -->
            <div class="container mx-auto flex justify-between items-center">
                <a href="/clinic-management/dashboard/assistant/index.php" class="text-white text-2xl font-bold rounded-lg px-3 py-1 hover:bg-teal-800 transition duration-300">
                    ClinicConnect (Assistant)
                </a>
                <div class="flex items-center space-x-6">
                    <span class="text-white text-lg">Welcome, Dr. <?php echo htmlspecialchars($assistant_name); ?>!</span>
                    <a href="/clinic-management/actions/logout.php"
                       class="bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-300 ease-in-out">
                        Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content Area with Sidebar -->
        <div class="flex flex-1">
            <!-- Sidebar Navigation -->
            <aside class="w-64 bg-white p-6 shadow-xl border-r border-gray-200">
                <nav>
                    <ul>
                        <li class="mb-4">
                            <a href="/clinic-management/dashboard/assistant/index.php"
                               class="flex items-center p-3 text-lg text-gray-700 hover:bg-teal-100 hover:text-teal-700 rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7m-1 7v-3m-6 3v-3m-8 3h3m-6 0h3"></path></svg>
                                Dashboard
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="/clinic-management/dashboard/assistant/appointments.php"
                               class="flex items-center p-3 text-lg text-gray-700 hover:bg-teal-100 hover:text-teal-700 rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                My Appointments
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="/clinic-management/dashboard/assistant/schedule.php"
                               class="flex items-center p-3 text-lg text-gray-700 hover:bg-teal-100 hover:text-teal-700 rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Manage Schedule
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="/clinic-management/dashboard/assistant/profile.php"
                               class="flex items-center p-3 text-lg text-gray-700 hover:bg-teal-100 hover:text-teal-700 rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                My Profile
                            </a>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- Content Area for Dashboard Pages -->
            <main class="flex-1 p-8 bg-gray-100 main-content">
                <!-- Content will be inserted here by specific dashboard pages -->
