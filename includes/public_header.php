<?php
// includes/public_header.php
// This header is for public-facing pages like login and registration.
// It does NOT perform session checks for user roles or fetch user-specific data.

// Ensure session is started, but only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection (if needed for public pages, though typically not for just header)
// For now, we assume public pages might need $conn, so it's included here.
// If your public pages truly don't need a DB connection, you can remove this.
require_once $_SERVER['DOCUMENT_ROOT'] . '/clinic-management/config/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'ClinicConnect'; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom Tailwind CSS configuration for 'Inter' font */
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Any global public-page specific styles can go here */
    </style>
</head>
<body class="bg-gray-100 font-inter">
    <!-- Main container for public pages, assuming a simpler layout than dashboard -->
    <div class="flex flex-col min-h-screen">
        <main class="flex-grow">
            <!-- Content will be inserted here by specific public pages -->
