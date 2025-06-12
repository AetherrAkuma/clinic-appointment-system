<?php
// includes/header.php
session_start(); // Ensure session is started for all pages that include this header

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: /clinic-management/index.php"); // Redirect to login page if not logged in
    exit();
}

// Include database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/clinic-management/config/db_connection.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = "User"; // Default name
$dashboard_base_path = ""; // Base path for dashboard links
$nav_color_from = "";
$nav_color_to = "";
$accent_color = ""; // For sidebar icons and hover states

// Determine user-specific data and styling
switch ($user_role) {
    case 'patient':
        $stmt = $conn->prepare("SELECT FirstName FROM PatientTBL WHERE PatientID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_name = $row['FirstName'];
        }
        $stmt->close();
        $dashboard_base_path = "/clinic-management/dashboard/patient";
        $nav_color_from = "from-blue-700";
        $nav_color_to = "to-blue-900";
        $accent_color = "text-blue-500"; // For icons and hover text
        $hover_bg_color = "hover:bg-blue-100";
        $hover_text_color = "hover:text-blue-700";
        $btn_color = "bg-blue-500 hover:bg-blue-600";
        $welcome_prefix = "Welcome, ";
        break;
    case 'assistant':
        $stmt = $conn->prepare("SELECT FirstName FROM AssistantTBL WHERE AssistantID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_name = $row['FirstName'];
        }
        $stmt->close();
        $dashboard_base_path = "/clinic-management/dashboard/assistant";
        $nav_color_from = "from-teal-700";
        $nav_color_to = "to-teal-900";
        $accent_color = "text-teal-500";
        $hover_bg_color = "hover:bg-teal-100";
        $hover_text_color = "hover:text-teal-700";
        $btn_color = "bg-teal-500 hover:bg-teal-600";
        $welcome_prefix = "Welcome, Dr. ";
        break;
    case 'admin':
        $stmt = $conn->prepare("SELECT FirstName FROM AdminTBL WHERE AdminID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_name = $row['FirstName'];
        }
        $stmt->close();
        $dashboard_base_path = "/clinic-management/dashboard/admin";
        $nav_color_from = "from-purple-700";
        $nav_color_to = "to-purple-900";
        $accent_color = "text-purple-500";
        $hover_bg_color = "hover:bg-purple-100";
        $hover_text_color = "hover:text-purple-700";
        $btn_color = "bg-purple-500 hover:bg-purple-600";
        $welcome_prefix = "Welcome, Admin ";
        break;
    default:
        // Should not happen if roles are managed correctly, but as a fallback
        header("Location: /clinic-management/index.php");
        exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? ucfirst($user_role) . ' Dashboard'; ?> - ClinicConnect</title>
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
        /* Mobile-first sidebar: hidden by default, visible on larger screens */
        .sidebar {
            display: none;
        }
        @media (min-width: 640px) { /* Tailwind's sm breakpoint */
            .sidebar {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-inter">
    <div class="flex flex-col min-h-screen">
        <!-- Top Navigation Bar -->
        <nav class="bg-gradient-to-r <?php echo $nav_color_from; ?> <?php echo $nav_color_to; ?> p-4 shadow-lg">
            <div class="container mx-auto flex justify-between items-center">
                <a href="<?php echo $dashboard_base_path; ?>/index.php" class="text-white text-2xl font-bold rounded-lg px-3 py-1 <?php echo str_replace('bg-', 'hover:bg-', $nav_color_from); ?> transition duration-300">
                    ClinicConnect (<?php echo ucfirst($user_role); ?>)
                </a>
                <div class="flex items-center space-x-4 sm:space-x-6">
                    <span class="text-white text-base sm:text-lg hidden sm:inline"> <!-- Hidden on small mobile, visible on sm and up -->
                        <?php echo $welcome_prefix . htmlspecialchars($user_name); ?>!
                    </span>
                    <!-- Mobile Menu Button (Hamburger) -->
                    <button id="mobile-menu-button" class="sm:hidden text-white focus:outline-none">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <a href="/clinic-management/actions/logout.php"
                       class="<?php echo $btn_color; ?> text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-300 ease-in-out">
                        Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Mobile Overlay Menu -->
        <div id="mobile-menu-overlay" class="fixed inset-0 bg-gray-800 bg-opacity-75 z-40 hidden sm:hidden"></div>
        <aside id="mobile-menu-sidebar" class="fixed inset-y-0 left-0 w-64 bg-white p-6 shadow-xl border-r border-gray-200 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out sm:hidden">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Navigation</h2>
                <button id="close-mobile-menu" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav>
                <ul>
                    <!-- Dynamic Sidebar Content for Mobile -->
                    <?php if ($user_role === 'patient'): ?>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/index.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7m-1 7v-3m-6 3v-3m-8 3h3m-6 0h3"></path></svg>
                                Dashboard
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/appointments.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                My Appointments
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/create_appointment.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Book Appointment
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/history.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                Medical History
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/profile.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                My Profile
                            </a>
                        </li>
                    <?php elseif ($user_role === 'assistant'): ?>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/index.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7m-1 7v-3m-6 3v-3m-8 3h3m-6 0h3"></path></svg>
                                Dashboard
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/appointments.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                My Appointments
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/schedule.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Manage Schedule
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/profile.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                My Profile
                            </a>
                        </li>
                    <?php elseif ($user_role === 'admin'): ?>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/index.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7m-1 7v-3m-6 3v-3m-8 3h3m-6 0h3"></path></svg>
                                Dashboard
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/manage_appointments.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Manage Appointments
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/manage_users.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2m0 2H7m-7 0h3a2 2 0 012-2h10a2 2 0 012 2v2M3 10v10m6-10v10m6-10v10m-6-4h.01M9 16h.01"></path></svg>
                                Manage Users
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/reports.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-4m0 0V7m0 6h4m-4 0H7m4-4H7m4 0h4m-4 4h.01M9 16h.01M12 21a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                                View Reports
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="<?php echo $dashboard_base_path; ?>/profile.php"
                               class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                My Profile
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area with Sidebar (Desktop) -->
        <div class="flex flex-1">
            <!-- Sidebar Navigation (Desktop) -->
            <aside class="w-64 bg-white p-6 shadow-xl border-r border-gray-200 hidden sm:block">
                <nav>
                    <ul>
                        <!-- Dynamic Sidebar Content for Desktop -->
                        <?php if ($user_role === 'patient'): ?>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/index.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7m-1 7v-3m-6 3v-3m-8 3h3m-6 0h3"></path></svg>
                                    Dashboard
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/appointments.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    My Appointments
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/create_appointment.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Book Appointment
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/history.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    Medical History
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/profile.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    My Profile
                                </a>
                            </li>
                        <?php elseif ($user_role === 'assistant'): ?>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/index.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7m-1 7v-3m-6 3v-3m-8 3h3m-6 0h3"></path></svg>
                                    Dashboard
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/appointments.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    My Appointments
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/schedule.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Manage Schedule
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/profile.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    My Profile
                                </a>
                            </li>
                        <?php elseif ($user_role === 'admin'): ?>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/index.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7m-1 7v-3m-6 3v-3m-8 3h3m-6 0h3"></path></svg>
                                    Dashboard
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/manage_appointments.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Manage Appointments
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/manage_users.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2m0 2H7m-7 0h3a2 2 0 012-2h10a2 2 0 012 2v2M3 10v10m6-10v10m6-10v10m-6-4h.01M9 16h.01"></path></svg>
                                    Manage Users
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/reports.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-4m0 0V7m0 6h4m-4 0H7m4-4H7m4 0h4m-4 4h.01M9 16h.01M12 21a9 9 0 110-18 9 9 0 010 18z"></path></svg>
                                    View Reports
                                </a>
                            </li>
                            <li class="mb-4">
                                <a href="<?php echo $dashboard_base_path; ?>/profile.php"
                                   class="flex items-center p-3 text-lg text-gray-700 <?php echo $hover_bg_color; ?> <?php echo $hover_text_color; ?> rounded-md transition duration-200 ease-in-out">
                                    <svg class="w-6 h-6 mr-3 <?php echo $accent_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    My Profile
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </aside>

            <!-- Content Area for Dashboard Pages -->
            <main class="flex-1 p-8 bg-gray-100 main-content">
                <!-- Content will be inserted here by specific dashboard pages -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const closeMobileMenuButton = document.getElementById('close-mobile-menu');
        const mobileMenuSidebar = document.getElementById('mobile-menu-sidebar');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');

        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenuSidebar.classList.remove('-translate-x-full');
                mobileMenuOverlay.classList.remove('hidden');
            });
        }

        if (closeMobileMenuButton) {
            closeMobileMenuButton.addEventListener('click', () => {
                mobileMenuSidebar.classList.add('-translate-x-full');
                mobileMenuOverlay.classList.add('hidden');
            });
        }

        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', () => {
                mobileMenuSidebar.classList.add('-translate-x-full');
                mobileMenuOverlay.classList.add('hidden');
            });
        }
    });
</script>
