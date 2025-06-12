<?php
// dashboard/patient/appointments.php
$page_title = "My Appointments";
include_once '../../includes/header.php'; // Include the common header

// Fetch patient's appointments
$patient_id = $_SESSION['user_id'];
$appointments = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from cancel_appointment.php or other actions
if (isset($_SESSION['appointment_message'])) {
    $message = $_SESSION['appointment_message'];
    $message_type = $_SESSION['appointment_message_type'];
    unset($_SESSION['appointment_message']); // Clear the message after displaying
    unset($_SESSION['appointment_message_type']);
}

// Prepare the SQL query to fetch appointments along with assistant's name
$stmt = $conn->prepare("
    SELECT
        a.AppointmentID,
        a.AppointmentSchedule,
        a.RoomNumber,
        a.Status,
        a.PaymentMethod,
        ast.FirstName AS AssistantFirstName,
        ast.LastName AS AssistantLastName,
        ast.Specialization
    FROM
        AppointmentTBL a
    JOIN
        AssistantTBL ast ON a.AssistantID = ast.AssistantID
    WHERE
        a.PatientID = ?
    ORDER BY
        a.AppointmentSchedule DESC
");

if ($stmt) {
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    }
    $stmt->close();
} else {
    // Handle SQL preparation error
    error_log("Failed to prepare statement for appointments: " . $conn->error);
    $message = "Error retrieving appointments.";
    $message_type = 'error';
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-blue-800 mb-4">My Appointments</h1>
    <p class="text-gray-700 text-lg">Here you can view the details of all your scheduled appointments.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<div class="appointment-list">
    <?php if (empty($appointments)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded-md shadow-sm" role="alert">
            <p class="font-bold">No appointments found.</p>
            <p>It looks like you don't have any appointments scheduled yet.</p>
        </div>
    <?php else: ?>
        <!-- Table for larger screens (sm:breakpoint and up) -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md hidden sm:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                            Appointment ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Doctor
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Specialization
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Schedule
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Room
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment Method
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($appointment['AppointmentID']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                Dr. <?php echo htmlspecialchars($appointment['AssistantFirstName'] . ' ' . $appointment['AssistantLastName']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($appointment['Specialization']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('F j, Y, g:i A', strtotime($appointment['AppointmentSchedule'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($appointment['RoomNumber'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php
                                    $status_class = '';
                                    switch ($appointment['Status']) {
                                        case 'Pending':
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'Completed':
                                            $status_class = 'bg-green-100 text-green-800';
                                            break;
                                        case 'OnGoing':
                                            $status_class = 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'Cancelled':
                                            $status_class = 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $status_class = 'bg-gray-100 text-gray-800';
                                            break;
                                    }
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($appointment['Status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($appointment['PaymentMethod']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($appointment['Status'] === 'Pending' || $appointment['Status'] === 'OnGoing'): ?>
                                    <button onclick="confirmCancel(<?php echo $appointment['AppointmentID']; ?>)"
                                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300 ease-in-out">
                                        Cancel
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Card layout for smaller screens (hidden sm: below breakpoint) -->
        <div class="sm:hidden grid grid-cols-1 gap-4">
            <?php foreach ($appointments as $appointment): ?>
                <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-bold text-lg text-blue-700">Appointment ID: <?php echo htmlspecialchars($appointment['AppointmentID']); ?></div>
                        <?php
                            $status_class = '';
                            switch ($appointment['Status']) {
                                case 'Pending':
                                    $status_class = 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'Completed':
                                    $status_class = 'bg-green-100 text-green-800';
                                    break;
                                case 'OnGoing':
                                    $status_class = 'bg-blue-100 text-blue-800';
                                    break;
                                case 'Cancelled':
                                    $status_class = 'bg-red-100 text-red-800';
                                    break;
                                default:
                                    $status_class = 'bg-gray-100 text-gray-800';
                                    break;
                            }
                        ?>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($appointment['Status']); ?>
                        </span>
                    </div>
                    <div class="text-gray-700 mb-1">
                        <span class="font-semibold">Doctor:</span> Dr. <?php echo htmlspecialchars($appointment['AssistantFirstName'] . ' ' . $appointment['LastName']); ?>
                    </div>
                    <div class="text-gray-600 mb-1">
                        <span class="font-semibold">Specialization:</span> <?php echo htmlspecialchars($appointment['Specialization']); ?>
                    </div>
                    <div class="text-gray-700 mb-1">
                        <span class="font-semibold">Schedule:</span> <?php echo date('F j, Y, g:i A', strtotime($appointment['AppointmentSchedule'])); ?>
                    </div>
                    <div class="text-gray-600 mb-1">
                        <span class="font-semibold">Room:</span> <?php echo htmlspecialchars($appointment['RoomNumber'] ?? 'N/A'); ?>
                    </div>
                    <div class="text-gray-600 mb-4">
                        <span class="font-semibold">Payment:</span> <?php echo htmlspecialchars($appointment['PaymentMethod']); ?>
                    </div>
                    <div class="text-right">
                        <?php if ($appointment['Status'] === 'Pending' || $appointment['Status'] === 'OnGoing'): ?>
                            <button onclick="confirmCancel(<?php echo $appointment['AppointmentID']; ?>)"
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300 ease-in-out">
                                Cancel Appointment
                            </button>
                        <?php else: ?>
                            <span class="text-gray-400 text-xs">Action Not Available</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl w-96">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Confirm Cancellation</h3>
        <p class="text-gray-700 mb-6">Are you sure you want to cancel this appointment?</p>
        <div class="flex justify-end space-x-4">
            <button id="cancelModalClose" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                No, Keep It
            </button>
            <button id="confirmCancelButton" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                Yes, Cancel
            </button>
        </div>
    </div>
</div>

<script>
    let appointmentToCancelId = null;
    const cancelModal = document.getElementById('cancelModal');
    const cancelModalClose = document.getElementById('cancelModalClose');
    const confirmCancelButton = document.getElementById('confirmCancelButton');

    function confirmCancel(appointmentId) {
        appointmentToCancelId = appointmentId;
        cancelModal.classList.remove('hidden');
    }

    cancelModalClose.addEventListener('click', () => {
        cancelModal.classList.add('hidden');
        appointmentToCancelId = null;
    });

    confirmCancelButton.addEventListener('click', () => {
        if (appointmentToCancelId) {
            window.location.href = `../../actions/cancel_appointment.php?id=${appointmentToCancelId}`;
        }
    });

    // Close modal if user clicks outside of it
    cancelModal.addEventListener('click', (event) => {
        if (event.target === cancelModal) {
            cancelModal.classList.add('hidden');
            appointmentToCancelId = null;
        }
    });
</script>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
