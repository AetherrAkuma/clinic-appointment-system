<?php
// dashboard/assistant/schedule.php
$page_title = "Manage Schedule";
include_once '../../includes/header.php'; // Use the unified header

// Fetch assistant's ID and name for display
$assistant_id = $_SESSION['user_id'];
$assistant_name = "Assistant"; // Default name
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from manage_schedule.php
if (isset($_SESSION['schedule_message'])) {
    $message = $_SESSION['schedule_message'];
    $message_type = $_SESSION['schedule_message_type'];
    unset($_SESSION['schedule_message']);
    unset($_SESSION['schedule_message_type']);
}

$stmt = $conn->prepare("SELECT FirstName, LastName FROM AssistantTBL WHERE AssistantID = ?");
if ($stmt) {
    $stmt->bind_param("i", $assistant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $assistant_name = $row['FirstName'] . ' ' . $row['LastName'];
    }
    $stmt->close();
}

// Fetch existing schedule slots for the assistant
$schedule_slots = [];
// Changed 'AvailableDate' to 'DayOfWeek'
$stmt = $conn->prepare("SELECT ScheduleID, DayOfWeek, StartTime, EndTime FROM AssistantScheduleTBL WHERE AssistantID = ? ORDER BY FIELD(DayOfWeek, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), StartTime ASC");
if ($stmt) {
    $stmt->bind_param("i", $assistant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $schedule_slots[] = $row;
    }
    $stmt->close();
} else {
    error_log("Failed to prepare statement for fetching schedule: " . $conn->error);
    $message = "Error retrieving schedule.";
    $message_type = 'error';
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-teal-800 mb-4">Manage Your Schedule</h1>
    <p class="text-gray-700 text-lg">
        Set your available consultation hours for patients to book appointments.
    </p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Add New Schedule Slot Form -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-teal-700 mb-4">Add New Availability</h2>
        <form action="../../actions/manage_schedule.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="mb-4">
                <label for="dayOfWeek" class="block text-gray-700 text-sm font-medium mb-2">Day of Week</label>
                <select id="dayOfWeek" name="day_of_week"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                    <option value="">Select Day</option>
                    <option value="Sunday">Sunday</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="startTime" class="block text-gray-700 text-sm font-medium mb-2">Start Time</label>
                    <input type="time" id="startTime" name="start_time"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>
                <div>
                    <label for="endTime" class="block text-gray-700 text-sm font-medium mb-2">End Time</label>
                    <input type="time" id="endTime" name="end_time"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                Add Availability
            </button>
        </form>
    </div>

    <!-- Existing Schedule Slots -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-teal-700 mb-4">Your Current Schedule</h2>
        <?php if (empty($schedule_slots)): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded-md shadow-sm" role="alert">
                <p class="font-bold">No availability slots added yet.</p>
                <p>Add new slots using the form on the left.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                                Day of Week
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Time Slot
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($schedule_slots as $slot): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($slot['DayOfWeek']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('h:i A', strtotime($slot['StartTime'])) . ' - ' . date('h:i A', strtotime($slot['EndTime'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="confirmDeleteSlot(<?php echo $slot['ScheduleID']; ?>)"
                                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300 ease-in-out">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl w-96">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Confirm Deletion</h3>
        <p class="text-gray-700 mb-6">Are you sure you want to delete this schedule slot?</p>
        <div class="flex justify-end space-x-4">
            <button id="deleteModalClose" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                No, Keep It
            </button>
            <button id="confirmDeleteButton" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

<script>
    let slotToDeleteId = null;
    const deleteModal = document.getElementById('deleteModal');
    const deleteModalClose = document.getElementById('deleteModalClose');
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');

    function confirmDeleteSlot(scheduleId) {
        slotToDeleteId = scheduleId;
        deleteModal.classList.remove('hidden');
    }

    deleteModalClose.addEventListener('click', () => {
        deleteModal.classList.add('hidden');
        slotToDeleteId = null;
    });

    confirmDeleteButton.addEventListener('click', () => {
        if (slotToDeleteId) {
            window.location.href = `../../actions/manage_schedule.php?action=delete&id=${slotToDeleteId}`;
        }
    });

    // Close modal if user clicks outside of it
    deleteModal.addEventListener('click', (event) => {
        if (event.target === deleteModal) {
            deleteModal.classList.add('hidden');
            slotToDeleteId = null;
        }
    });
</script>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
