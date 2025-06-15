<?php
// register.php
session_start(); // Start the session to use session messages
$page_title = "Register Account"; // Page title for public_header.php

// Check for registration messages
$message = '';
$message_type = '';
if (isset($_SESSION['registration_message'])) {
    $message = $_SESSION['registration_message'];
    $message_type = $_SESSION['registration_message_type'];
    unset($_SESSION['registration_message']);
    unset($_SESSION['registration_message_type']);
}

// Include the new public header
include_once 'includes/public_header.php';
?>

<div class="bg-gray-100 flex items-center justify-center min-h-screen font-inter p-4">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-2xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-blue-800 mb-6">Create a New Account</h2>
        <p class="text-center text-gray-600 mb-6">Register as a Patient.</p>

        <?php if (!empty($message)): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
                <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Patient Registration Form (now the only form) -->
        <div id="patientForm" class="registration-form">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Patient Registration</h3>
            <form action="actions/register_action.php" method="POST">
                <input type="hidden" name="role" value="patient">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="patientFirstName" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                        <input type="text" id="patientFirstName" name="firstName"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter first name" required>
                    </div>
                    <div>
                        <label for="patientLastName" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                        <input type="text" id="patientLastName" name="lastName"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter last name" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="patientEmail" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                    <input type="email" id="patientEmail" name="email"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter email" required>
                </div>
                <div class="mb-4">
                    <label for="patientPassword" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                    <input type="password" id="patientPassword" name="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter password" required minlength="8">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters.</p>
                </div>
                <div class="mb-6">
                    <label for="patientConfirmPassword" class="block text-gray-700 text-sm font-medium mb-2">Confirm Password</label>
                    <input type="password" id="patientConfirmPassword" name="confirmPassword"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Confirm password" required minlength="8">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="patientAge" class="block text-gray-700 text-sm font-medium mb-2">Age (Optional)</label>
                        <input type="number" id="patientAge" name="age"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter age">
                    </div>
                    <div>
                        <label for="patientGender" class="block text-gray-700 text-sm font-medium mb-2">Gender (Optional)</label>
                        <select id="patientGender" name="gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="patientAddress" class="block text-gray-700 text-sm font-medium mb-2">Address (Optional)</label>
                    <input type="text" id="patientAddress" name="address"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter address">
                </div>
                <div class="mb-4">
                    <label for="patientContactNumber" class="block text-gray-700 text-sm font-medium mb-2">Contact Number (Optional)</label>
                    <input type="text" id="patientContactNumber" name="contactNumber"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter contact number">
                </div>
                <div class="mb-6">
                    <label for="patientMedicalHistory" class="block text-gray-700 text-sm font-medium mb-2">Medical History (Optional)</label>
                    <textarea id="patientMedicalHistory" name="medicalHistory" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"
                              placeholder="Any relevant medical history"></textarea>
                </div>
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                    Register as Patient
                </button>
            </form>
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">Already have an account? <a href="index.php" class="text-blue-600 hover:underline">Login here</a></p>
    </div>
</div>

<script>
    // Since only patient registration is allowed, the role selection and form toggling logic is removed.
    // The patientForm is now always visible.
</script>

<?php
// Ensure footer.php is also generalized and includes </body></html> and closes $conn
include_once 'includes/footer.php';
?>
