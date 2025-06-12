<?php
// dashboard/patient/index.php
$page_title = "Patient Dashboard"; // Set page title for the header
include_once '../../includes/header.php'; // Include the common header
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-blue-800 mb-4">Welcome to Your Dashboard!</h1>
    <p class="text-gray-700 text-lg">Here you can manage your appointments, view your medical history, and update your profile.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"> <!-- Ensured sm:grid-cols-2 for smaller tablets/large phones -->
    <!-- Card: Upcoming Appointments -->
    <a href="appointments.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-blue-100 rounded-full p-3 text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">My Appointments</h2>
        </div>
        <p class="text-gray-600">View and manage your upcoming and past appointments.</p>
    </a>

    <!-- Card: Medical History -->
    <a href="history.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-green-100 rounded-full p-3 text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">Medical History</h2>
        </div>
        <p class="text-gray-600">Review your past diagnoses, prescriptions, and medical records.</p>
    </a>

    <!-- Card: Profile Settings -->
    <a href="profile.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-purple-100 rounded-full p-3 text-purple-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">My Profile</h2>
        </div>
        <p class="text-gray-600">Update your personal information and contact details.</p>
    </a>
</div>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
