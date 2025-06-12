<?php
// dashboard/assistant/profile.php
$page_title = "My Profile";
include_once '../../includes/header.php'; // Use the unified header

// Fetch assistant's current details
$assistant_id = $_SESSION['user_id'];
$assistant_data = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from update_assistant_profile.php
if (isset($_SESSION['profile_message'])) {
    $message = $_SESSION['profile_message'];
    $message_type = $_SESSION['profile_message_type'];
    unset($_SESSION['profile_message']); // Clear the message after displaying
    unset($_SESSION['profile_message_type']);
}

$stmt = $conn->prepare("SELECT FirstName, LastName, Specialization, SessionFee, Email FROM AssistantTBL WHERE AssistantID = ?");

if ($stmt) {
    $stmt->bind_param("i", $assistant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $assistant_data = $result->fetch_assoc();
    } else {
        $message = "Assistant data not found.";
        $message_type = 'error';
    }
    $stmt->close();
} else {
    $message = "Database query preparation failed: " . $conn->error;
    $message_type = 'error';
    error_log("Failed to prepare statement for assistant profile: " . $conn->error);
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-teal-800 mb-4">My Profile</h1>
    <p class="text-gray-700 text-lg">Update your personal information, specialization, and session fee here.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form id="profileUpdateForm" action="../../actions/update_assistant_profile.php" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="firstName" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                <input type="text" id="firstName" name="firstName"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500"
                       value="<?php echo htmlspecialchars($assistant_data['FirstName'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="lastName" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                <input type="text" id="lastName" name="lastName"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500"
                       value="<?php echo htmlspecialchars($assistant_data['LastName'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="specialization" class="block text-gray-700 text-sm font-medium mb-2">Specialization</label>
                <input type="text" id="specialization" name="specialization"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500"
                       value="<?php echo htmlspecialchars($assistant_data['Specialization'] ?? ''); ?>">
            </div>
            <div>
                <label for="sessionFee" class="block text-gray-700 text-sm font-medium mb-2">Session Fee (₱)</label>
                <input type="number" step="0.01" id="sessionFee" name="sessionFee"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500"
                       value="<?php echo htmlspecialchars($assistant_data['SessionFee'] ?? ''); ?>" min="0">
            </div>
            <div class="md:col-span-2">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" id="email" name="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                       value="<?php echo htmlspecialchars($assistant_data['Email'] ?? ''); ?>" readonly>
                <p class="text-xs text-gray-500 mt-1">Email cannot be changed here.</p>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <button type="submit"
                    class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
