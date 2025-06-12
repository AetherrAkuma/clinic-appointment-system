<?php
// dashboard/admin/add_user.php
$page_title = "Add New User";
include_once '../../includes/header.php'; // Use the unified header

// Check if the logged-in user is an admin
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /clinic-management/index.php"); // Redirect if not admin
    exit();
}

$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from actions/manage_users_action.php
if (isset($_SESSION['user_management_message'])) {
    $message = $_SESSION['user_management_message'];
    $message_type = $_SESSION['user_management_message_type'];
    unset($_SESSION['user_management_message']);
    unset($_SESSION['user_management_message_type']);
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">Add New User Account</h1>
    <p class="text-gray-700 text-lg">Create new accounts for patients, assistants, or other administrators.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form action="../../actions/manage_users_action.php" method="POST">
        <input type="hidden" name="action" value="add">

        <div class="mb-4">
            <label for="role" class="block text-gray-700 text-sm font-medium mb-2">User Role</label>
            <select id="role" name="role"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                <option value="">-- Select Role --</option>
                <option value="patient">Patient</option>
                <option value="assistant">Assistant</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="firstName" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                <input type="text" id="firstName" name="firstName"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                       placeholder="Enter first name" required>
            </div>
            <div>
                <label for="lastName" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                <input type="text" id="lastName" name="lastName"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                       placeholder="Enter last name" required>
            </div>
            <div>
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                <input type="email" id="email" name="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                       placeholder="Enter email" required>
            </div>
            <div>
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <input type="password" id="password" name="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                       placeholder="Enter password" required>
            </div>

            <!-- Fields specific to Patients (initially hidden) -->
            <div id="patientFields" class="md:col-span-2 hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-medium mb-2">Age</label>
                    <input type="number" id="age" name="age"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Enter age" min="0">
                </div>
                <div>
                    <label for="gender" class="block text-gray-700 text-sm font-medium mb-2">Gender</label>
                    <select id="gender" name="gender"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="address" class="block text-gray-700 text-sm font-medium mb-2">Address</label>
                    <input type="text" id="address" name="address"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Enter address">
                </div>
                <div class="md:col-span-2">
                    <label for="contactNumber" class="block text-gray-700 text-sm font-medium mb-2">Contact Number</label>
                    <input type="text" id="contactNumber" name="contactNumber"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Enter contact number">
                </div>
                <div class="md:col-span-2">
                    <label for="medicalHistory" class="block text-gray-700 text-sm font-medium mb-2">Medical History</label>
                    <textarea id="medicalHistory" name="medicalHistory" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 resize-y"
                              placeholder="Enter medical history"></textarea>
                </div>
            </div>

            <!-- Fields specific to Assistants (initially hidden) -->
            <div id="assistantFields" class="md:col-span-2 hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="specialization" class="block text-gray-700 text-sm font-medium mb-2">Specialization</label>
                    <input type="text" id="specialization" name="specialization"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="e.g., General Physician, Dentist">
                </div>
                <div>
                    <label for="sessionFee" class="block text-gray-700 text-sm font-medium mb-2">Session Fee (₱)</label>
                    <input type="number" step="0.01" id="sessionFee" name="sessionFee"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="e.g., 500.00" min="0">
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <button type="submit"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                Add User
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const patientFields = document.getElementById('patientFields');
        const assistantFields = document.getElementById('assistantFields');

        function toggleFields() {
            patientFields.classList.add('hidden');
            assistantFields.classList.add('hidden');

            // Reset required attributes for hidden fields
            patientFields.querySelectorAll('input, select, textarea').forEach(field => field.removeAttribute('required'));
            assistantFields.querySelectorAll('input, select').forEach(field => field.removeAttribute('required'));


            if (roleSelect.value === 'patient') {
                patientFields.classList.remove('hidden');
                // Set required attributes for patient fields if needed
                // patientFields.querySelector('#age').setAttribute('required', 'required'); // Example
            } else if (roleSelect.value === 'assistant') {
                assistantFields.classList.remove('hidden');
                // Set required attributes for assistant fields if needed
                // assistantFields.querySelector('#specialization').setAttribute('required', 'required'); // Example
            }
        }

        roleSelect.addEventListener('change', toggleFields);

        // Initial call to set fields based on default selection (if any)
        toggleFields();
    });
</script>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
