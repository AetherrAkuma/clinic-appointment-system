<?php
// about.php
$page_title = "About Us";
include_once 'includes/header.php'; // Use the unified header for general pages
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">About Our Team</h1>
    <p class="text-gray-700 text-lg">Meet the dedicated individuals behind the Clinic Management System project.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    <!-- Team Member 1 -->
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <img class="w-32 h-32 rounded-full mx-auto mb-4 object-cover" src="https://placehold.co/128x128/e0e0e0/555555?text=Member+1" alt="Team Member 1">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Member One Name</h2>
        <!-- <p class="text-gray-600 mb-4">Role/Contribution (e.g., Lead Developer)</p> -->
        <a href="https://www.facebook.com/yourprofile1" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-300 ease-in-out">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 13.5h2c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v3.5c0 .55.45 1 1 1zm4-6V6c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v1.5c0 .55.45 1 1 1h2c.55 0 1-.45 1-1zM22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15h-2.5v-3H10V9.5C10 7.43 11.43 6 13.5 6H16v3h-2.5c-.28 0-.5.22-.5.5V12h3V15h-3v6.95C18.57 20.65 22 16.27 22 12z"></path></svg>
            Facebook
        </a>
    </div>

    <!-- Team Member 2 -->
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <img class="w-32 h-32 rounded-full mx-auto mb-4 object-cover" src="https://placehold.co/128x128/e0e0e0/555555?text=Member+2" alt="Team Member 2">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Member Two Name</h2>
        <!-- <p class="text-gray-600 mb-4">Role/Contribution (e.g., UI/UX Designer)</p> -->
        <a href="https://www.facebook.com/yourprofile2" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-300 ease-in-out">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 13.5h2c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v3.5c0 .55.45 1 1 1zm4-6V6c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v1.5c0 .55.45 1 1 1h2c.55 0 1-.45 1-1zM22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15h-2.5v-3H10V9.5C10 7.43 11.43 6 13.5 6H16v3h-2.5c-.28 0-.5.22-.5.5V12h3V15h-3v6.95C18.57 20.65 22 16.27 22 12z"></path></svg>
            Facebook
        </a>
    </div>

    <!-- Team Member 3 -->
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <img class="w-32 h-32 rounded-full mx-auto mb-4 object-cover" src="https://placehold.co/128x128/e0e0e0/555555?text=Member+3" alt="Team Member 3">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Member Three Name</h2>
        <!-- <p class="text-gray-600 mb-4">Role/Contribution (e.g., Backend Developer)</p> -->
        <a href="https://www.facebook.com/yourprofile3" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-300 ease-in-out">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 13.5h2c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v3.5c0 .55.45 1 1 1zm4-6V6c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v1.5c0 .55.45 1 1 1h2c.55 0 1-.45 1-1zM22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15h-2.5v-3H10V9.5C10 7.43 11.43 6 13.5 6H16v3h-2.5c-.28 0-.5.22-.5.5V12h3V15h-3v6.95C18.57 20.65 22 16.27 22 12z"></path></svg>
            Facebook
        </a>
    </div>

    <!-- Team Member 4 -->
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <img class="w-32 h-32 rounded-full mx-auto mb-4 object-cover" src="https://placehold.co/128x128/e0e0e0/555555?text=Member+4" alt="Team Member 4">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Member Four Name</h2>
        <!-- <p class="text-gray-600 mb-4">Role/Contribution (e.g., Quality Assurance)</p> -->
        <a href="https://www.facebook.com/yourprofile4" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-300 ease-in-out">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 13.5h2c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v3.5c0 .55.45 1 1 1zm4-6V6c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v1.5c0 .55.45 1 1 1h2c.55 0 1-.45 1-1zM22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15h-2.5v-3H10V9.5C10 7.43 11.43 6 13.5 6H16v3h-2.5c-.28 0-.5.22-.5.5V12h3V15h-3v6.95C18.57 20.65 22 16.27 22 12z"></path></svg>
            Facebook
        </a>
    </div>

    <!-- Team Member 5 -->
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <img class="w-32 h-32 rounded-full mx-auto mb-4 object-cover" src="https://placehold.co/128x128/e0e0e0/555555?text=Member+5" alt="Team Member 5">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Member Five Name</h2>
        <!-- <p class="text-gray-600 mb-4">Role/Contribution (e.g., Documentation Specialist)</p> -->
        <a href="https://www.facebook.com/yourprofile5" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-300 ease-in-out">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 13.5h2c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v3.5c0 .55.45 1 1 1zm4-6V6c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v1.5c0 .55.45 1 1 1h2c.55 0 1-.45 1-1zM22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15h-2.5v-3H10V9.5C10 7.43 11.43 6 13.5 6H16v3h-2.5c-.28 0-.5.22-.5.5V12h3V15h-3v6.95C18.57 20.65 22 16.27 22 12z"></path></svg>
            Facebook
        </a>
    </div>

</div>

<?php
include_once 'includes/footer.php'; // Include the common footer
?>
