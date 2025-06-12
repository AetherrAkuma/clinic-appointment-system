<?php
// dashboard/admin/edit_user.php
$page_title = "Edit User";
include_once '../../includes/header.php'; // Use the unified header

// Check if the logged-in user is an admin
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /clinic-management/index.php"); // Redirect if not admin
    exit();
}

$user_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$user_role = htmlspecialchars(trim($_GET['role'] ?? ''));

$user_data = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from actions/manage_users_action.php
if (isset($_SESSION['user_management_message'])) {
    $message = $_SESSION['user_management_message'];
    $message_type = $_SESSION['user_management_message_type'];
    unset($_SESSION['user_management_message']);
    unset($_SESSION['user_management_message_type']);
}

if (empty($user_id) || empty($user_role)) {
    $message = 'Invalid user ID or role provided.';
    $message_type = 'error';
    // Optionally redirect back to manage_users if invalid
    // header("Location: manage_users.php"); exit();
} else {
    $table = '';
    $id_column = '';
    $select_columns = '';

    switch ($user_role) {
        case 'patient':
            $table = 'PatientTBL';
            $id_column = 'PatientID';
            $select_columns = 'FirstName, LastName, Email, Age, Gender, Address, ContactNumber, MedicalHistory';
            break;
        case 'assistant':
            $table = 'AssistantTBL';
            $id_column = 'AssistantID';
            $select_columns = 'FirstName, LastName, Email, Specialization, SessionFee';
            break;
        case 'admin':
            $table = 'AdminTBL';
            $id_column = 'AdminID';
            $select_columns = 'FirstName, LastName, Email';
            break;
        default:
            $message = 'Invalid user role.';
            $message_type = 'error';
    }

    if (empty($message)) { // Proceed only if no initial errors
        $stmt = $conn->prepare("SELECT " . $select_columns . " FROM " . $table . " WHERE " . $id_column . " = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
            } else {
                $message = ucfirst($user_role) . ' not found.';
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Database query preparation failed: ' . $conn->error;
            $message_type = 'error';
            error_log("Failed to prepare statement for fetching user data for edit: " . $conn->error);
        }
    }
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">Edit User Account (<?php echo ucfirst($user_role); ?>)</h1>
    <p class="text-gray-700 text-lg">Modify the details for this user account.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($user_data)): ?>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">User Details</h2>
        <form action="../../actions/manage_users_action.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="userId" value="<?php echo htmlspecialchars($user_id); ?>">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($user_role); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="firstName" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                    <input type="text" id="firstName" name="firstName"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           value="<?php echo htmlspecialchars($user_data['FirstName'] ?? ''); ?>" required>
                </div>
                <div>
                    <label for="lastName" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                    <input type="text" id="lastName" name="lastName"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           value="<?php echo htmlspecialchars($user_data['LastName'] ?? ''); ?>" required>
                </div>
                <div class="md:col-span-2">
                    <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                    <input type="email" id="email" name="email"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                           value="<?php echo htmlspecialchars($user_data['Email'] ?? ''); ?>" readonly>
                    <p class="text-xs text-gray-500 mt-1">Email cannot be changed here.</p>
                </div>

                <!-- Conditional fields based on role -->
                <?php if ($user_role === 'patient'): ?>
                    <div>
                        <label for="age" class="block text-gray-700 text-sm font-medium mb-2">Age</label>
                        <input type="number" id="age" name="age"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?php echo htmlspecialchars($user_data['Age'] ?? ''); ?>" min="0">
                    </div>
                    <div>
                        <label for="gender" class="block text-gray-700 text-sm font-medium mb-2">Gender</label>
                        <select id="gender" name="gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo (isset($user_data['Gender']) && $user_data['Gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($user_data['Gender']) && $user_data['Gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($user_data['Gender']) && $user_data['Gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="address" class="block text-gray-700 text-sm font-medium mb-2">Address</label>
                        <input type="text" id="address" name="address"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?php echo htmlspecialchars($user_data['Address'] ?? ''); ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label for="contactNumber" class="block text-gray-700 text-sm font-medium mb-2">Contact Number</label>
                        <input type="text" id="contactNumber" name="contactNumber"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?php echo htmlspecialchars($user_data['ContactNumber'] ?? ''); ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label for="medicalHistory" class="block text-gray-700 text-sm font-medium mb-2">Medical History</label>
                        <textarea id="medicalHistory" name="medicalHistory" rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 resize-y"><?php echo htmlspecialchars($user_data['MedicalHistory'] ?? ''); ?></textarea>
                    </div>
                <?php elseif ($user_role === 'assistant'): ?>
                    <div>
                        <label for="specialization" class="block text-gray-700 text-sm font-medium mb-2">Specialization</label>
                        <input type="text" id="specialization" name="specialization"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?php echo htmlspecialchars($user_data['Specialization'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="sessionFee" class="block text-gray-700 text-sm font-medium mb-2">Session Fee (₱)</label>
                        <input type="number" step="0.01" id="sessionFee" name="sessionFee"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                               value="<?php echo htmlspecialchars($user_data['SessionFee'] ?? ''); ?>" min="0">
                    </div>
                <?php endif; ?>
                <!-- Admin has no extra fields beyond name and email for now -->
            </div>

            <div class="flex justify-end space-x-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                    Save Details
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password Section for any user -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Change Password</h2>
        <form action="../../actions/manage_users_action.php" method="POST">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="userId" value="<?php echo htmlspecialchars($user_id); ?>">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($user_role); ?>">
            <input type="hidden" name="isSelfChange" value="false"> <!-- This is always false when admin edits another user -->

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
