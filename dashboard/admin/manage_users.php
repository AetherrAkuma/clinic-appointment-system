<?php
// dashboard/admin/manage_users.php
$page_title = "Manage Users";
include_once '../../includes/header.php'; // Use the unified header

// Check if the logged-in user is an admin
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /clinic-management/index.php"); // Redirect if not admin
    exit();
}

$users = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from actions/manage_users_action.php
if (isset($_SESSION['user_management_message'])) {
    $message = $_SESSION['user_management_message'];
    $message_type = $_SESSION['user_management_message_type'];
    unset($_SESSION['user_management_message']);
    unset($_SESSION['user_management_message_type']);
}

// Fetch all Patients
$stmt_patients = $conn->prepare("SELECT PatientID AS UserID, FirstName, LastName, Email, 'patient' AS Role FROM PatientTBL");
if ($stmt_patients) {
    $stmt_patients->execute();
    $result_patients = $stmt_patients->get_result();
    while ($row = $result_patients->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt_patients->close();
} else {
    error_log("Failed to fetch patients for user management: " . $conn->error);
}

// Fetch all Assistants
$stmt_assistants = $conn->prepare("SELECT AssistantID AS UserID, FirstName, LastName, Email, 'assistant' AS Role FROM AssistantTBL");
if ($stmt_assistants) {
    $stmt_assistants->execute();
    $result_assistants = $stmt_assistants->get_result();
    while ($row = $result_assistants->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt_assistants->close();
} else {
    error_log("Failed to fetch assistants for user management: " . $conn->error);
}

// Fetch all Admins (now uncommented)
$stmt_admins = $conn->prepare("SELECT AdminID AS UserID, FirstName, LastName, Email, 'admin' AS Role FROM AdminTBL");
if ($stmt_admins) {
    $stmt_admins->execute();
    $result_admins = $stmt_admins->get_result();
    while ($row = $result_admins->fetch_assoc()) {
        // We still don't want the logged-in admin to be able to delete themselves from the list
        // but they should appear on the list to be managed (e.g., view/edit)
        // The delete button will be disabled for their own account via a condition in the loop.
        $users[] = $row;
    }
    $stmt_admins->close();
} else {
    error_log("Failed to fetch admins for user management: " . $conn->error);
}


// Sort users by role and then by last name, first name
usort($users, function($a, $b) {
    $roleOrder = ['admin' => 1, 'assistant' => 2, 'patient' => 3]; // Define desired order
    $roleComparison = $roleOrder[$a['Role']] <=> $roleOrder[$b['Role']];
    if ($roleComparison !== 0) {
        return $roleComparison;
    }
    $lastNameComparison = strcmp($a['LastName'], $b['LastName']);
    if ($lastNameComparison !== 0) {
        return $lastNameComparison;
    }
    return strcmp($a['FirstName'], $b['FirstName']);
});

?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">Manage Clinic Users</h1>
    <p class="text-gray-700 text-lg">Oversee all patient, assistant, and admin accounts.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<div class="mb-6 flex justify-end">
    <a href="add_user.php"
       class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
        Add New User
    </a>
</div>

<div class="user-list">
    <?php if (empty($users)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded-md shadow-sm" role="alert">
            <p class="font-bold">No users found.</p>
            <p>There are no users registered in the system yet. Add one using the button above.</p>
        </div>
    <?php else: ?>
        <!-- Table for larger screens (sm:breakpoint and up) -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md hidden sm:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                            User ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($user['UserID']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($user['Email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">
                                <?php echo htmlspecialchars($user['Role']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium flex flex-col space-y-1 items-start">
                                <a href="edit_user.php?id=<?php echo htmlspecialchars($user['UserID']); ?>&role=<?php echo htmlspecialchars($user['Role']); ?>"
                                   class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300 ease-in-out w-full text-center">
                                    View/Edit
                                </a>
                                <?php if ($user['Role'] !== 'admin' || $user['UserID'] !== $_SESSION['user_id']): // Prevent admin from deleting themselves ?>
                                <button onclick="confirmDeleteUser(<?php echo htmlspecialchars($user['UserID']); ?>, '<?php echo htmlspecialchars($user['Role']); ?>')"
                                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-xs w-full transition duration-300 ease-in-out">
                                    Delete
                                </button>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs block text-center w-full mt-2">Cannot Delete Self</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Card layout for smaller screens (hidden sm: below breakpoint) -->
        <div class="sm:hidden grid grid-cols-1 gap-4">
            <?php foreach ($users as $user): ?>
                <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-bold text-lg text-purple-700 capitalize"><?php echo htmlspecialchars($user['Role']); ?> ID: <?php echo htmlspecialchars($user['UserID']); ?></div>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 capitalize">
                            <?php echo htmlspecialchars($user['Role']); ?>
                        </span>
                    </div>
                    <div class="text-gray-700 mb-1">
                        <span class="font-semibold">Name:</span> <?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?>
                    </div>
                    <div class="text-gray-600 mb-4">
                        <span class="font-semibold">Email:</span> <?php echo htmlspecialchars($user['Email']); ?>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <a href="edit_user.php?id=<?php echo htmlspecialchars($user['UserID']); ?>&role=<?php echo htmlspecialchars($user['Role']); ?>"
                           class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out text-center">
                            View/Edit Details
                        </a>
                        <?php if ($user['Role'] !== 'admin' || $user['UserID'] !== $_SESSION['user_id']): ?>
                        <button onclick="confirmDeleteUser(<?php echo htmlspecialchars($user['UserID']); ?>, '<?php echo htmlspecialchars($user['Role']); ?>')"
                                class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                            Delete User
                        </button>
                        <?php else: ?>
                            <span class="text-gray-400 text-xs block text-center">Cannot Delete Self</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete User Confirmation Modal -->
<div id="deleteUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-sm mx-4 md:mx-auto">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Confirm User Deletion</h3>
        <p class="text-gray-700 mb-6">Are you sure you want to PERMANENTLY DELETE this user account? This action cannot be undone and will delete all associated data (e.g., appointments).</p>
        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4">
            <button id="deleteUserModalClose" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                No, Keep User
            </button>
            <button id="confirmDeleteUserButton" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

<script>
    let userToDeleteId = null;
    let userToDeleteRole = null;

    const deleteUserModal = document.getElementById('deleteUserModal');
    const deleteUserModalClose = document.getElementById('deleteUserModalClose');
    const confirmDeleteUserButton = document.getElementById('confirmDeleteUserButton');

    function confirmDeleteUser(userId, role) {
        userToDeleteId = userId;
        userToDeleteRole = role;
        deleteUserModal.classList.remove('hidden');
    }

    deleteUserModalClose.addEventListener('click', () => {
        deleteUserModal.classList.add('hidden');
        userToDeleteId = null;
        userToDeleteRole = null;
    });

    confirmDeleteUserButton.addEventListener('click', () => {
        if (userToDeleteId && userToDeleteRole) {
            window.location.href = `../../actions/manage_users_action.php?action=delete&id=${userToDeleteId}&role=${userToDeleteRole}`;
        }
    });

    deleteUserModal.addEventListener('click', (event) => {
        if (event.target === deleteUserModal) {
            deleteUserModal.classList.add('hidden');
            userToDeleteId = null;
            userToDeleteRole = null;
        }
    });
</script>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
