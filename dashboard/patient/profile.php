<?php
// dashboard/patient/profile.php
$page_title = "My Profile";
include_once '../../includes/header.php'; // Include the common header

// Fetch patient's current details
$patient_id = $_SESSION['user_id'];
$patient_data = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from update_profile.php
if (isset($_SESSION['profile_message'])) {
    $message = $_SESSION['profile_message'];
    $message_type = $_SESSION['profile_message_type'];
    unset($_SESSION['profile_message']); // Clear the message after displaying
    unset($_SESSION['profile_message_type']);
}

$stmt = $conn->prepare("SELECT FirstName, LastName, Age, Gender, Address, ContactNumber, Email, MedicalHistory FROM PatientTBL WHERE PatientID = ?");

if ($stmt) {
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $patient_data = $result->fetch_assoc();
    } else {
        $message = "Patient data not found.";
        $message_type = 'error';
    }
    $stmt->close();
} else {
    $message = "Database query preparation failed: " . $conn->error;
    $message_type = 'error';
    error_log("Failed to prepare statement for patient profile: " . $conn->error);
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-blue-800 mb-4">My Profile</h1>
    <p class="text-gray-700 text-lg">Update your personal information here.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form id="profileUpdateForm" action="../../actions/update_profile.php" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="firstName" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                <input type="text" id="firstName" name="firstName"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($patient_data['FirstName'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="lastName" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                <input type="text" id="lastName" name="lastName"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($patient_data['LastName'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="age" class="block text-gray-700 text-sm font-medium mb-2">Age</label>
                <input type="number" id="age" name="age"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($patient_data['Age'] ?? ''); ?>" min="0">
            </div>
            <div>
                <label for="gender" class="block text-gray-700 text-sm font-medium mb-2">Gender</label>
                <select id="gender" name="gender"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo (isset($patient_data['Gender']) && $patient_data['Gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (isset($patient_data['Gender']) && $patient_data['Gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo (isset($patient_data['Gender']) && $patient_data['Gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="md:col-span-2"> <!-- md:col-span-2 ensures full width on larger screens -->
                <label for="address" class="block text-gray-700 text-sm font-medium mb-2">Address</label>
                <input type="text" id="address" name="address"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($patient_data['Address'] ?? ''); ?>">
            </div>
            <div>
                <label for="contactNumber" class="block text-gray-700 text-sm font-medium mb-2">Contact Number</label>
                <input type="text" id="contactNumber" name="contactNumber"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($patient_data['ContactNumber'] ?? ''); ?>">
            </div>
            <div>
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" id="email" name="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                       value="<?php echo htmlspecialchars($patient_data['Email'] ?? ''); ?>" readonly>
                <p class="text-xs text-gray-500 mt-1">Email cannot be changed here.</p>
            </div>
            <div class="md:col-span-2"> <!-- md:col-span-2 ensures full width on larger screens -->
                <label for="medicalHistory" class="block text-gray-700 text-sm font-medium mb-2">Medical History</label>
                <textarea id="medicalHistory" name="medicalHistory" rows="5"
                          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"><?php echo htmlspecialchars($patient_data['MedicalHistory'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
