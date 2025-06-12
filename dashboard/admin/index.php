<?php
// dashboard/admin/index.php
$page_title = "Admin Dashboard"; // Set page title for the header
include_once '../../includes/header.php'; // Use the unified header

// Fetch admin's first name for display (already handled in header, but for consistency)
$admin_id = $_SESSION['user_id'];
$admin_name = "Admin"; // Default name

$stmt = $conn->prepare("SELECT FirstName FROM AdminTBL WHERE AdminID = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $admin_name = $row['FirstName'];
    }
    $stmt->close();
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">Welcome, Admin <?php echo htmlspecialchars($admin_name); ?>!</h1>
    <p class="text-gray-700 text-lg">Manage all aspects of the clinic, including appointments, users, and reports.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Card: Manage Appointments -->
    <a href="manage_appointments.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-purple-100 rounded-full p-3 text-purple-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">Manage Appointments</h2>
        </div>
        <p class="text-gray-600">Oversee all patient and doctor appointments, including status updates and assignments.</p>
    </a>

    <!-- Card: Manage Users -->
    <a href="manage_users.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-purple-100 rounded-full p-3 text-purple-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2m0 2H7m-7 0h3a2 2 0 012-2h10a2 2 0 012 2v2M3 10v10m6-10v10m6-10v10m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">Manage Users</h2>
        </div>
        <p class="text-gray-600">Add, edit, or remove patient, doctor, and admin accounts.</p>
    </a>

    <!-- Card: View Reports -->
    <a href="reports.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-purple-100 rounded-full p-3 text-purple-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-4m0 0V7m0 6h4m-4 0H7m4-4H7m4 0h4m-4 4h.01M9 16h.01M12 21a9 9 0 110-18 9 9 0 010 18z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">View Reports</h2>
        </div>
        <p class="text-gray-600">Access various reports on clinic performance, appointments, and user activity.</p>
    </a>

    <!-- Card: My Profile -->
    <a href="profile.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 transform hover:-translate-y-1">
        <div class="flex items-center mb-4">
            <div class="bg-purple-100 rounded-full p-3 text-purple-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 ml-4">My Profile</h2>
        </div>
        <p class="text-gray-600">Update your personal administration account details.</p>
    </a>
</div>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
