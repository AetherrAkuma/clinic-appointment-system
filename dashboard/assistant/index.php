<?php
// dashboard/assistant/index.php
$page_title = "Assistant Dashboard"; // Set page title for the header
include_once '../../includes/assistant_header.php'; // Include the common assistant header
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-teal-800 mb-4">Welcome, Doctor!</h1>
    <p class="text-gray-700 text-lg">Here you can manage your appointments, schedule, and profile.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Card: Upcoming Appointments -->
    <a href="appointments.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-teal-100 rounded-full p-3 text-teal-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">My Appointments</h2>
        </div>
        <p class="text-gray-600">View and manage your scheduled patient appointments.</p>
    </a>

    <!-- Card: Manage Schedule -->
    <a href="schedule.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-green-100 rounded-full p-3 text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">Manage Schedule</h2>
        </div>
        <p class="text-gray-600">Set your availability and manage your consultation hours.</p>
    </a>

    <!-- Card: Profile Settings -->
    <a href="profile.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-purple-100 rounded-full p-3 text-purple-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">My Profile</h2>
        </div>
        <p class="text-gray-600">Update your personal information and specialization details.</p>
    </a>
</div>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
