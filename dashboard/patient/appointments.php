<?php
// dashboard/patient/appointments.php
$page_title = "My Appointments";
include_once '../../includes/header.php';

if ($_SESSION['user_role'] !== 'patient') {
    header("Location: /clinic-management/index.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$appointments = [];
$message = '';
$message_type = '';

// Check for messages from actions/appointment_action.php
if (isset($_SESSION['appointment_message'])) {
    $message = $_SESSION['appointment_message'];
    $message_type = $_SESSION['appointment_message_type'];
    unset($_SESSION['appointment_message']);
    unset($_SESSION['appointment_message_type']);
}

// Fetch appointments for the logged-in patient, including assistant details
$stmt = $conn->prepare("
    SELECT
        a.AppointmentID,
        a.AppointmentSchedule,
        a.RoomNumber,
        a.Status,
        a.PaymentMethod,
        a.ReasonForAppointment,
        ast.FirstName AS AssistantFirstName,
        ast.LastName AS AssistantLastName,
        ast.Specialization,
        ast.SessionFee
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
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $stmt->close();
} else {
    $message = "Database query preparation failed: " . $conn->error;
    $message_type = 'error';
    error_log("Error fetching patient appointments: " . $conn->error);
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">My Appointments</h1>
    <p class="text-gray-700 text-lg">View and manage your upcoming and past appointments.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<div class="appointments-list">
    <?php if (empty($appointments)): ?>
        <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-4 rounded-md shadow-sm" role="alert">
            <p class="font-bold">No appointments found.</p>
            <p>You currently do not have any appointments scheduled.</p>
            <a href="book_appointment.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                Book a New Appointment
            </a>
        </div>
    <?php else: ?>
        <!-- Table for larger screens -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md hidden sm:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                            Appt ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Doctor
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Schedule
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Room
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reason
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($appointment['AppointmentID']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Dr. <?php echo htmlspecialchars($appointment['AssistantFirstName'] . ' ' . $appointment['AssistantLastName']); ?>
                                <br><span class="text-xs text-gray-500"><?php echo htmlspecialchars($appointment['Specialization']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('F j, Y, g:i A', strtotime($appointment['AppointmentSchedule'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($appointment['RoomNumber'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs overflow-hidden text-ellipsis">
                                <?php echo htmlspecialchars($appointment['ReasonForAppointment'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php
                                $status_class = '';
                                switch ($appointment['Status']) {
                                    case 'Pending': $status_class = 'bg-yellow-100 text-yellow-800'; break;
                                    case 'OnGoing': $status_class = 'bg-blue-100 text-blue-800'; break;
                                    case 'Completed': $status_class = 'bg-green-100 text-green-800'; break;
                                    case 'Cancelled': $status_class = 'bg-red-100 text-red-800'; break;
                                }
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($appointment['Status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex flex-col space-y-1 items-start">
                                <?php if ($appointment['Status'] === 'Pending'): ?>
                                    <button onclick="openCancelAppointmentModal(<?php echo htmlspecialchars($appointment['AppointmentID']); ?>)"
                                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300 ease-in-out w-full text-center">
                                        Cancel Appointment
                                    </button>
                                <?php elseif ($appointment['Status'] === 'Completed'): ?>
                                    <a href="view_prescription.php?id=<?php echo htmlspecialchars($appointment['AppointmentID']); ?>"
                                       class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-1 px-3 rounded-md text-xs transition duration-300 ease-in-out w-full text-center">
                                        View Prescription
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-500 text-xs text-center w-full">No Actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Card layout for smaller screens -->
        <div class="sm:hidden grid grid-cols-1 gap-4">
            <?php foreach ($appointments as $appointment): ?>
                <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-bold text-lg text-purple-700">Appt ID: <?php echo htmlspecialchars($appointment['AppointmentID']); ?></div>
                        <?php
                        $status_class = '';
                        switch ($appointment['Status']) {
                            case 'Pending': $status_class = 'bg-yellow-100 text-yellow-800'; break;
                            case 'OnGoing': $status_class = 'bg-blue-100 text-blue-800'; break;
                            case 'Completed': $status_class = 'bg-green-100 text-green-800'; break;
                            case 'Cancelled': $status_class = 'bg-red-100 text-red-800'; break;
                        }
                        ?>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($appointment['Status']); ?>
                        </span>
                    </div>
                    <div class="text-gray-700 mb-1">
                        <span class="font-semibold">Doctor:</span> Dr. <?php echo htmlspecialchars($appointment['AssistantFirstName'] . ' ' . $appointment['AssistantLastName']); ?>
                        <span class="text-xs text-gray-500">(<?php echo htmlspecialchars($appointment['Specialization']); ?>)</span>
                    </div>
                    <div class="text-gray-700 mb-1">
                        <span class="font-semibold">Schedule:</span> <?php echo date('F j, Y, g:i A', strtotime($appointment['AppointmentSchedule'])); ?>
                    </div>
                    <div class="text-gray-600 mb-1">
                        <span class="font-semibold">Room:</span> <?php echo htmlspecialchars($appointment['RoomNumber'] ?? 'N/A'); ?>
                    </div>
                    <div class="text-gray-600 mb-4">
                        <span class="font-semibold">Reason:</span> <?php echo htmlspecialchars($appointment['ReasonForAppointment'] ?? 'N/A'); ?>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <?php if ($appointment['Status'] === 'Pending'): ?>
                            <button onclick="openCancelAppointmentModal(<?php echo htmlspecialchars($appointment['AppointmentID']); ?>)"
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out text-center">
                                Cancel Appointment
                            </button>
                        <?php elseif ($appointment['Status'] === 'Completed'): ?>
                            <a href="view_prescription.php?id=<?php echo htmlspecialchars($appointment['AppointmentID']); ?>"
                               class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out text-center">
                                View Prescription
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500 text-sm text-center w-full">No Actions</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Cancel Appointment Modal (existing logic) -->
<div id="cancelAppointmentModal" class="fixed inset-0 bg-gray-600 bg-opacity50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-sm mx-4 md:mx-auto">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Confirm Cancellation</h3>
        <p class="text-gray-700 mb-6">Are you sure you want to cancel this appointment? This action cannot be undone.</p>
        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4">
            <button id="cancelAppointmentModalClose" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                No, Keep Appointment
            </button>
            <button id="confirmCancelAppointmentButton" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                Yes, Cancel It
            </button>
        </div>
    </div>
</div>

<script>
    let appointmentToCancelId = null;

    // Cancel Appointment Modal Elements
    const cancelAppointmentModal = document.getElementById('cancelAppointmentModal');
    const cancelAppointmentModalCloseBtn = document.getElementById('cancelAppointmentModalClose');
    const confirmCancelAppointmentButton = document.getElementById('confirmCancelAppointmentButton');

    function openCancelAppointmentModal(appointmentId) {
        appointmentToCancelId = appointmentId;
        cancelAppointmentModal.classList.remove('hidden');
    }

    cancelAppointmentModalCloseBtn.addEventListener('click', () => {
        cancelAppointmentModal.classList.add('hidden');
        appointmentToCancelId = null;
    });

    confirmCancelAppointmentButton.addEventListener('click', () => {
        if (appointmentToCancelId) {
            window.location.href = `../../actions/patient_appointment_action.php?action=cancel&id=${appointmentToCancelId}`;
        }
    });

    cancelAppointmentModal.addEventListener('click', (event) => {
        if (event.target === cancelAppointmentModal) {
            cancelAppointmentModal.classList.add('hidden');
            appointmentToCancelId = null;
        }
    });
</script>

<?php include_once '../../includes/footer.php'; ?>
