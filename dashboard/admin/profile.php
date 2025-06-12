<?php
// dashboard/admin/profile.php
$page_title = "My Profile";
include_once '../../includes/header.php'; // Use the unified header

// Check if the logged-in user is an admin
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /clinic-management/index.php"); // Redirect if not admin
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_data = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from actions/manage_users_action.php
if (isset($_SESSION['user_management_message'])) {
    $message = $_SESSION['user_management_message'];
    $message_type = $_SESSION['user_management_message_type'];
    unset($_SESSION['user_management_message']);
    unset($_SESSION['user_management_message_type']);
} else if (isset($_GET['message']) && isset($_GET['type'])) { // Check for messages passed from login page after password change
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}


// Fetch current admin data
$stmt = $conn->prepare("SELECT FirstName, LastName, Email FROM AdminTBL WHERE AdminID = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $admin_data = $result->fetch_assoc();
    } else {
        $message = "Admin profile not found.";
        $message_type = 'error';
    }
    $stmt->close();
} else {
    $message = "Database query preparation failed: " . $conn->error;
    $message_type = 'error';
    error_log("Failed to prepare statement for admin profile: " . $conn->error);
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">My Admin Profile</h1>
    <p class="text-gray-700 text-lg">Update your personal account details and password.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($admin_data)): ?>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Personal Details</h2>
        <form action="../../actions/manage_users_action.php" method="POST">
            <input type="hidden" name="action" value="profile_update">
            <input type="hidden" name="userId" value="<?php echo htmlspecialchars($admin_id); ?>">
            <input type="hidden" name="role" value="admin">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="firstName" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                    <input type="text" id="firstName" name="firstName"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           value="<?php echo htmlspecialchars($admin_data['FirstName'] ?? ''); ?>" required>
                </div>
                <div>
                    <label for="lastName" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                    <input type="text" id="lastName" name="lastName"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           value="<?php echo htmlspecialchars($admin_data['LastName'] ?? ''); ?>" required>
                </div>
                <div class="md:col-span-2">
                    <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                    <input type="email" id="email" name="email"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                           value="<?php echo htmlspecialchars($admin_data['Email'] ?? ''); ?>" readonly>
                    <p class="text-xs text-gray-500 mt-1">Email cannot be changed here.</p>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                    Save Personal Changes
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Change Password</h2>
        <form action="../../actions/manage_users_action.php" method="POST">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="userId" value="<?php echo htmlspecialchars($admin_id); ?>">
            <input type="hidden" name="role" value="admin">
            <input type="hidden" name="isSelfChange" value="true">

            <div class="mb-4">
                <label for="newPassword" class="block text-gray-700 text-sm font-medium mb-2">New Password</label>
                <input type="password" id="newPassword" name="newPassword"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                       placeholder="Enter new password" required minlength="8">
                <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long.</p>
            </div>
            <div class="mb-6">
                <label for="confirmNewPassword" class="block text-gray-700 text-sm font-medium mb-2">Confirm New Password</label>
                <input type="password" id="confirmNewPassword" name="confirmNewPassword"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                       placeholder="Confirm new password" required minlength="8">
            </div>

            <div class="flex justify-end space-x-4">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                    Change Password
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
