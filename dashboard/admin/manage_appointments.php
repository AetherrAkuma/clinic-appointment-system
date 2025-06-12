<?php
// dashboard/admin/manage_appointments.php
$page_title = "Manage Appointments";
include_once '../../includes/header.php'; // Use the unified header

// Check if the logged-in user is an admin
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /clinic-management/index.php"); // Redirect if not admin
    exit();
}

$appointments = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from actions/admin_update_appointment.php
if (isset($_SESSION['admin_appointment_message'])) {
    $message = $_SESSION['admin_appointment_message'];
    $message_type = $_SESSION['admin_appointment_message_type'];
    unset($_SESSION['admin_appointment_message']);
    unset($_SESSION['admin_appointment_message_type']);
}

// Fetch ALL appointments with patient and assistant details
$stmt = $conn->prepare("
    SELECT
        a.AppointmentID,
        a.AppointmentSchedule,
        a.RoomNumber,
        a.Status,
        a.PaymentMethod,
        a.ReasonForAppointment,
        p.PatientID,
        p.FirstName AS PatientFirstName,
        p.LastName AS PatientLastName,
        p.ContactNumber AS PatientContactNumber,
        ast.AssistantID,
        ast.FirstName AS AssistantFirstName,
        ast.LastName AS AssistantLastName,
        ast.Specialization
    FROM
        AppointmentTBL a
    JOIN
        PatientTBL p ON a.PatientID = p.PatientID
    JOIN
        AssistantTBL ast ON a.AssistantID = ast.AssistantID
    ORDER BY
        a.AppointmentSchedule DESC
");

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    }
    $stmt->close();
} else {
    error_log("Failed to prepare statement for admin appointments: " . $conn->error);
    $message = "Error retrieving appointments.";
    $message_type = 'error';
}

// Fetch all assistants for the room assignment dropdown
$assistants = [];
$stmt_assistants = $conn->prepare("SELECT AssistantID, FirstName, LastName, Specialization FROM AssistantTBL ORDER BY LastName, FirstName");
if ($stmt_assistants) {
    $stmt_assistants->execute();
    $result_assistants = $stmt_assistants->get_result();
    while ($row = $result_assistants->fetch_assoc()) {
        $assistants[] = $row;
    }
    $stmt_assistants->close();
} else {
    error_log("Failed to fetch assistants for room assignment: " . $conn->error);
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">Manage All Appointments</h1>
    <p class="text-gray-700 text-lg">View, update status, and assign rooms for all clinic appointments.</p>
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
            <p>There are no appointments in the system yet.</p>
        </div>
    <?php else: ?>
        <!-- Table for larger screens (sm:breakpoint and up) -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md hidden sm:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                            Appt ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Patient
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
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment
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
                                <?php echo htmlspecialchars($appointment['PatientFirstName'] . ' ' . $appointment['PatientLastName']); ?>
                                <br><span class="text-xs text-gray-500"><?php echo htmlspecialchars($appointment['PatientContactNumber']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                Dr. <?php echo htmlspecialchars($appointment['AssistantFirstName'] . ' ' . $appointment['AssistantLastName']); ?>
                                <br><span class="text-xs text-gray-500"><?php echo htmlspecialchars($appointment['Specialization']); ?></span>
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
                            <td class="px-6 py-4 text-sm font-medium flex flex-col space-y-1 items-start">
                                <button onclick="viewAppointmentDetails(<?php echo htmlspecialchars(json_encode($appointment)); ?>, <?php echo htmlspecialchars(json_encode($assistants)); ?>)"
                                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-md text-xs mb-1 transition duration-300 ease-in-out w-full">
                                    View/Edit
                                </button>
                                <?php if ($appointment['Status'] === 'Pending' || $appointment['Status'] === 'OnGoing'): ?>
                                    <button onclick="confirmStatusUpdate(<?php echo $appointment['AppointmentID']; ?>, 'complete')"
                                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-md text-xs w-full transition duration-300 ease-in-out">
                                        Complete
                                    </button>
                                    <button onclick="confirmStatusUpdate(<?php echo $appointment['AppointmentID']; ?>, 'cancel')"
                                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-xs w-full transition duration-300 ease-in-out">
                                        Cancel
                                    </button>
                                <?php endif; ?>
                                <button onclick="confirmDeleteAppointment(<?php echo $appointment['AppointmentID']; ?>)"
                                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-1 px-3 rounded-md text-xs w-full transition duration-300 ease-in-out <?php echo ($appointment['Status'] === 'Completed' || $appointment['Status'] === 'Cancelled') ? 'mt-1' : ''; ?>">
                                    Delete
                                </button>
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
                        <div class="font-bold text-lg text-purple-700">Appt ID: <?php echo htmlspecialchars($appointment['AppointmentID']); ?></div>
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
                        <span class="font-semibold">Patient:</span> <?php echo htmlspecialchars($appointment['PatientFirstName'] . ' ' . $appointment['PatientLastName']); ?>
                        <span class="text-xs text-gray-500">(<?php echo htmlspecialchars($appointment['PatientContactNumber']); ?>)</span>
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
                     <div class="text-gray-600 mb-1">
                        <span class="font-semibold">Reason:</span> <?php echo htmlspecialchars($appointment['ReasonForAppointment'] ?? 'N/A'); ?>
                    </div>
                    <div class="text-gray-600 mb-4">
                        <span class="font-semibold">Payment:</span> <?php echo htmlspecialchars($appointment['PaymentMethod']); ?>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <button onclick="viewAppointmentDetails(<?php echo htmlspecialchars(json_encode($appointment)); ?>, <?php echo htmlspecialchars(json_encode($assistants)); ?>)"
                                class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                            View/Edit
                        </button>
                        <?php if ($appointment['Status'] === 'Pending' || $appointment['Status'] === 'OnGoing'): ?>
                            <button onclick="confirmStatusUpdate(<?php echo $appointment['AppointmentID']; ?>, 'complete')"
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                                Complete
                            </button>
                            <button onclick="confirmStatusUpdate(<?php echo $appointment['AppointmentID']; ?>, 'cancel')"
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                                Cancel
                            </button>
                        <?php endif; ?>
                        <button onclick="confirmDeleteAppointment(<?php echo $appointment['AppointmentID']; ?>)"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                            Delete Record
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal for Appointment Details (View/Edit) -->
<div id="appointmentDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-lg mx-auto overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-purple-800">Appointment Details</h3>
            <button id="closeAppointmentDetailsModal" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="editAppointmentForm" action="../../actions/admin_update_appointment.php" method="POST">
            <input type="hidden" id="modalAppointmentId" name="appointment_id">

            <div class="mb-4">
                <label for="modalPatientName" class="block text-gray-700 text-sm font-medium mb-2">Patient Name</label>
                <input type="text" id="modalPatientName" class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
            </div>
            <div class="mb-4">
                <label for="modalDoctorName" class="block text-gray-700 text-sm font-medium mb-2">Doctor</label>
                <select id="modalDoctorName" name="assistant_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <!-- Options populated by JS -->
                </select>
            </div>
            <div class="mb-4">
                <label for="modalAppointmentSchedule" class="block text-gray-700 text-sm font-medium mb-2">Schedule</label>
                <input type="datetime-local" id="modalAppointmentSchedule" name="appointment_schedule"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" required>
            </div>
            <div class="mb-4">
                <label for="modalRoomNumber" class="block text-gray-700 text-sm font-medium mb-2">Room Number</label>
                <input type="text" id="modalRoomNumber" name="room_number"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="mb-4">
                <label for="modalStatus" class="block text-gray-700 text-sm font-medium mb-2">Status</label>
                <select id="modalStatus" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                    <option value="Pending">Pending</option>
                    <option value="OnGoing">OnGoing</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="modalPaymentMethod" class="block text-gray-700 text-sm font-medium mb-2">Payment Method</label>
                <select id="modalPaymentMethod" name="payment_method" class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                    <option value="Cash">Cash</option>
                    <option value="Online">Online</option>
                </select>
            </div>
            <div class="mb-6">
                <label for="modalReasonForAppointment" class="block text-gray-700 text-sm font-medium mb-2">Reason for Appointment</label>
                <textarea id="modalReasonForAppointment" name="reason_for_appointment" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 resize-y" readonly></textarea>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Confirming Status Updates -->
<div id="statusUpdateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-sm mx-4 md:mx-auto">
        <h3 id="statusModalTitle" class="text-xl font-bold mb-4 text-gray-800">Confirm Status Update</h3>
        <p id="statusModalMessage" class="text-gray-700 mb-6">Are you sure you want to update this appointment's status?</p>
        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4">
            <button id="statusModalClose" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                No
            </button>
            <button id="confirmStatusUpdateButton" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                Yes, Update
            </button>
        </div>
    </div>
</div>

<!-- Modal for Confirming Delete Appointment -->
<div id="deleteAppointmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-sm mx-4 md:mx-auto">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Confirm Deletion</h3>
        <p class="text-gray-700 mb-6">Are you sure you want to PERMANENTLY DELETE this appointment record? This action cannot be undone.</p>
        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4">
            <button id="deleteAppointmentModalClose" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                No, Keep It
            </button>
            <button id="confirmDeleteAppointmentButton" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

<script>
    let currentAppointmentForStatusUpdateId = null;
    let newAppointmentStatus = null;
    let appointmentToDeleteId = null; // New variable for delete action

    const appointmentDetailsModal = document.getElementById('appointmentDetailsModal');
    const closeAppointmentDetailsModal = document.getElementById('closeAppointmentDetailsModal');
    const modalAppointmentId = document.getElementById('modalAppointmentId');
    const modalPatientName = document.getElementById('modalPatientName');
    const modalDoctorName = document.getElementById('modalDoctorName');
    const modalAppointmentSchedule = document.getElementById('modalAppointmentSchedule');
    const modalRoomNumber = document.getElementById('modalRoomNumber');
    const modalStatus = document.getElementById('modalStatus');
    const modalPaymentMethod = document.getElementById('modalPaymentMethod');
    const modalReasonForAppointment = document.getElementById('modalReasonForAppointment');

    const statusUpdateModal = document.getElementById('statusUpdateModal');
    const statusModalTitle = document.getElementById('statusModalTitle');
    const statusModalMessage = document.getElementById('statusModalMessage');
    const statusModalClose = document.getElementById('statusModalClose');
    const confirmStatusUpdateButton = document.getElementById('confirmStatusUpdateButton');

    // New delete modal elements
    const deleteAppointmentModal = document.getElementById('deleteAppointmentModal');
    const deleteAppointmentModalClose = document.getElementById('deleteAppointmentModalClose');
    const confirmDeleteAppointmentButton = document.getElementById('confirmDeleteAppointmentButton');


    function viewAppointmentDetails(appointment, assistants) {
        modalAppointmentId.value = appointment.AppointmentID;
        modalPatientName.value = `${appointment.PatientFirstName} ${appointment.PatientLastName}`;

        // Populate Doctor dropdown
        modalDoctorName.innerHTML = ''; // Clear previous options
        assistants.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.AssistantID;
            option.textContent = `Dr. ${doctor.FirstName} ${doctor.LastName} (${doctor.Specialization})`;
            if (doctor.AssistantID === appointment.AssistantID) {
                option.selected = true;
            }
            modalDoctorName.appendChild(option);
        });

        // Format datetime-local input
        const apptDateTime = new Date(appointment.AppointmentSchedule);
        const year = apptDateTime.getFullYear();
        const month = String(apptDateTime.getMonth() + 1).padStart(2, '0');
        const day = String(apptDateTime.getDate()).padStart(2, '0');
        const hours = String(apptDateTime.getHours()).padStart(2, '0');
        const minutes = String(apptDateTime.getMinutes()).padStart(2, '0');
        modalAppointmentSchedule.value = `${year}-${month}-${day}T${hours}:${minutes}`;

        modalRoomNumber.value = appointment.RoomNumber || '';
        modalStatus.value = appointment.Status;
        modalPaymentMethod.value = appointment.PaymentMethod;
        modalReasonForAppointment.value = appointment.ReasonForAppointment || '';

        appointmentDetailsModal.classList.remove('hidden');
    }

    closeAppointmentDetailsModal.addEventListener('click', () => {
        appointmentDetailsModal.classList.add('hidden');
    });

    appointmentDetailsModal.addEventListener('click', (event) => {
        if (event.target === appointmentDetailsModal) {
            appointmentDetailsModal.classList.add('hidden');
        }
    });

    function confirmStatusUpdate(appointmentId, actionType) {
        currentAppointmentForStatusUpdateId = appointmentId;
        newAppointmentStatus = actionType; // 'complete' or 'cancel'

        if (actionType === 'complete') {
            statusModalTitle.textContent = "Complete Appointment";
            statusModalMessage.textContent = "Are you sure you want to mark this appointment as Completed?";
            confirmStatusUpdateButton.className = "bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out";
        } else if (actionType === 'cancel') {
            statusModalTitle.textContent = "Cancel Appointment";
            statusModalMessage.textContent = "Are you sure you want to cancel this appointment?";
            confirmStatusUpdateButton.className = "bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out";
        }
        statusUpdateModal.classList.remove('hidden');
    }

    statusModalClose.addEventListener('click', () => {
        statusUpdateModal.classList.add('hidden');
        currentAppointmentForStatusUpdateId = null;
        newAppointmentStatus = null;
    });

    confirmStatusUpdateButton.addEventListener('click', () => {
        if (currentAppointmentForStatusUpdateId && newAppointmentStatus) {
            window.location.href = `../../actions/admin_update_appointment.php?id=${currentAppointmentForStatusUpdateId}&action=${newAppointmentStatus}`;
        }
    });

    statusUpdateModal.addEventListener('click', (event) => {
        if (event.target === statusUpdateModal) {
            statusUpdateModal.classList.add('hidden');
            currentAppointmentForStatusUpdateId = null;
            newAppointmentStatus = null;
        }
    });

    // New functions for Delete Appointment
    function confirmDeleteAppointment(appointmentId) {
        appointmentToDeleteId = appointmentId;
        deleteAppointmentModal.classList.remove('hidden');
    }

    deleteAppointmentModalClose.addEventListener('click', () => {
        deleteAppointmentModal.classList.add('hidden');
        appointmentToDeleteId = null;
    });

    confirmDeleteAppointmentButton.addEventListener('click', () => {
        if (appointmentToDeleteId) {
            window.location.href = `../../actions/admin_update_appointment.php?id=${appointmentToDeleteId}&action=delete`;
        }
    });

    deleteAppointmentModal.addEventListener('click', (event) => {
        if (event.target === deleteAppointmentModal) {
            deleteAppointmentModal.classList.add('hidden');
            appointmentToDeleteId = null;
        }
    });
</script>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
