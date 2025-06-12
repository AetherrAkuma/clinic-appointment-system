<?php
// dashboard/assistant/appointments.php
$page_title = "My Appointments";
include_once '../../includes/header.php'; // Use the unified header

// Fetch assistant's ID from session
$assistant_id = $_SESSION['user_id'];
$appointments = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from other actions (e.g., updating appointment status)
if (isset($_SESSION['appointment_message'])) {
    $message = $_SESSION['appointment_message'];
    $message_type = $_SESSION['appointment_message_type'];
    unset($_SESSION['appointment_message']); // Clear the message after displaying
    unset($_SESSION['appointment_message_type']);
}

// Prepare the SQL query to fetch appointments for the logged-in assistant,
// including patient details and the new ReasonForAppointment.
$stmt = $conn->prepare("
    SELECT
        a.AppointmentID,
        a.AppointmentSchedule,
        a.RoomNumber,
        a.Status,
        a.PaymentMethod,
        a.ReasonForAppointment, -- Added this field
        p.PatientID, -- Added PatientID to fetch patient details later
        p.FirstName AS PatientFirstName,
        p.LastName AS PatientLastName,
        p.ContactNumber AS PatientContactNumber
    FROM
        AppointmentTBL a
    JOIN
        PatientTBL p ON a.PatientID = p.PatientID
    WHERE
        a.AssistantID = ?
    ORDER BY
        a.AppointmentSchedule DESC
");

if ($stmt) {
    $stmt->bind_param("i", $assistant_id);
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
    error_log("Failed to prepare statement for assistant appointments: " . $conn->error);
    $message = "Error retrieving appointments.";
    $message_type = 'error';
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-teal-800 mb-4">My Patient Appointments</h1>
    <p class="text-gray-700 text-lg">Here you can view and manage all appointments scheduled with you.</p>
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
            <p>It looks like you don't have any appointments scheduled with you yet.</p>
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
                            Patient Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact
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
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($appointment['PatientContactNumber']); ?>
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
                            <td class="px-6 py-4 text-sm font-medium flex flex-col space-y-1 items-start"> <!-- Flex column for stacking buttons -->
                                <button onclick="viewPatientDetails(<?php echo $appointment['PatientID']; ?>, <?php echo $appointment['AppointmentID']; ?>)"
                                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-md text-xs mb-1 transition duration-300 ease-in-out w-full">
                                    View Patient Info
                                </button>
                                <?php if ($appointment['Status'] === 'Pending'): ?>
                                    <button onclick="confirmAction(<?php echo $appointment['AppointmentID']; ?>, 'ongoing')"
                                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded-md text-xs w-full transition duration-300 ease-in-out">
                                        Mark OnGoing
                                    </button>
                                    <button onclick="confirmAction(<?php echo $appointment['AppointmentID']; ?>, 'cancel')"
                                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-xs w-full transition duration-300 ease-in-out">
                                        Cancel
                                    </button>
                                <?php elseif ($appointment['Status'] === 'OnGoing'): ?>
                                    <button onclick="confirmAction(<?php echo $appointment['AppointmentID']; ?>, 'complete')"
                                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-md text-xs w-full transition duration-300 ease-in-out">
                                        Complete
                                    </button>
                                    <button onclick="confirmAction(<?php echo $appointment['AppointmentID']; ?>, 'cancel')"
                                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-md text-xs w-full transition duration-300 ease-in-out">
                                        Cancel
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs block text-center w-full mt-2">N/A</span>
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
                        <div class="font-bold text-lg text-teal-700">Appt ID: <?php echo htmlspecialchars($appointment['AppointmentID']); ?></div>
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
                    </div>
                    <div class="text-gray-600 mb-1">
                        <span class="font-semibold">Contact:</span> <?php echo htmlspecialchars($appointment['PatientContactNumber']); ?>
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
                    <div class="flex flex-col space-y-2"> <!-- Changed to flex-col for stacked buttons -->
                        <button onclick="viewPatientDetails(<?php echo $appointment['PatientID']; ?>, <?php echo $appointment['AppointmentID']; ?>)"
                                class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                            View Patient Info
                        </button>
                        <?php if ($appointment['Status'] === 'Pending'): ?>
                            <button onclick="confirmAction(<?php echo $appointment['AppointmentID']; ?>, 'ongoing')"
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                                Mark OnGoing
                            </button>
                            <button onclick="confirmAction(<?php echo $appointment['AppointmentID']; ?>, 'cancel')"
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                                Cancel
                            </button>
                        <?php elseif ($appointment['Status'] === 'OnGoing'): ?>
                            <button onclick="confirmAction(<?php echo $appointment['AppointmentID']; ?>, 'complete')"
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                                Complete
                            </button>
                            <button onclick="confirmAction(<?php echo $appointment['AppointmentID']; ?>, 'cancel')"
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded-md text-sm transition duration-300 ease-in-out">
                                Cancel
                            </button>
                        <?php else: ?>
                            <span class="text-gray-400 text-xs block text-center">Action Not Available</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal for Confirming Actions (Complete/Cancel/OnGoing) -->
<div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-sm mx-4 md:mx-auto">
        <h3 id="modalTitle" class="text-xl font-bold mb-4 text-gray-800">Confirm Action</h3>
        <p id="modalMessage" class="text-gray-700 mb-6">Are you sure?</p>
        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4">
            <button id="actionModalClose" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                No
            </button>
            <button id="confirmActionButton" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
                Yes
            </button>
        </div>
    </div>
</div>

<!-- Modal for Patient Details -->
<div id="patientDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md mx-4 md:mx-auto overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-teal-800">Patient Information</h3>
            <button id="closePatientDetailsModal" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="patientDetailsContent" class="text-gray-700 space-y-3">
            <!-- Patient details will be loaded here via JavaScript -->
        </div>
    </div>
</div>

<script>
    // --- Action Confirmation Modal (Existing) ---
    let currentAppointmentId = null;
    let currentActionType = null; // 'complete', 'cancel', or 'ongoing'
    const actionModal = document.getElementById('actionModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const actionModalClose = document.getElementById('actionModalClose');
    const confirmActionButton = document.getElementById('confirmActionButton');

    function confirmAction(appointmentId, actionType) {
        currentAppointmentId = appointmentId;
        currentActionType = actionType;

        if (actionType === 'complete') {
            modalTitle.textContent = "Complete Appointment";
            modalMessage.textContent = "Are you sure you want to mark this appointment as Completed?";
            confirmActionButton.className = "bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out";
        } else if (actionType === 'cancel') {
            modalTitle.textContent = "Cancel Appointment";
            modalMessage.textContent = "Are you sure you want to cancel this appointment?";
            confirmActionButton.className = "bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out";
        } else if (actionType === 'ongoing') { // New 'ongoing' action
            modalTitle.textContent = "Mark OnGoing";
            modalMessage.textContent = "Are you sure you want to mark this appointment as OnGoing?";
            confirmActionButton.className = "bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out";
        }
        actionModal.classList.remove('hidden');
    }

    actionModalClose.addEventListener('click', () => {
        actionModal.classList.add('hidden');
        currentAppointmentId = null;
        currentActionType = null;
    });

    confirmActionButton.addEventListener('click', () => {
        if (currentAppointmentId && currentActionType) {
            window.location.href = `../../actions/update_appointment_status.php?id=${currentAppointmentId}&action=${currentActionType}`;
        }
    });

    // Close modal if user clicks outside of it
    actionModal.addEventListener('click', (event) => {
        if (event.target === actionModal) {
            actionModal.classList.add('hidden');
            currentAppointmentId = null;
            currentActionType = null;
        }
    });

    // --- Patient Details Modal (New) ---
    const patientDetailsModal = document.getElementById('patientDetailsModal');
    const closePatientDetailsModal = document.getElementById('closePatientDetailsModal');
    const patientDetailsContent = document.getElementById('patientDetailsContent');

    async function viewPatientDetails(patientId, appointmentId) { // Added appointmentId parameter
        // Clear previous content
        patientDetailsContent.innerHTML = '<p class="text-center py-4">Loading patient details...</p>';
        patientDetailsModal.classList.remove('hidden');

        try {
            // Pass appointmentId to fetch_patient_details.php
            const response = await fetch(`../../actions/fetch_patient_details.php?patient_id=${patientId}&appointment_id=${appointmentId}`);
            const data = await response.json();

            if (data.success) {
                const patient = data.patient;
                const appointmentReason = data.appointment_reason; // Get the new field

                patientDetailsContent.innerHTML = `
                    <p><span class="font-semibold">Name:</span> ${patient.FirstName} ${patient.LastName}</p>
                    <p><span class="font-semibold">Age:</span> ${patient.Age ?? 'N/A'}</p>
                    <p><span class="font-semibold">Gender:</span> ${patient.Gender ?? 'N/A'}</p>
                    <p><span class="font-semibold">Address:</span> ${patient.Address ?? 'N/A'}</p>
                    <p><span class="font-semibold">Contact:</span> ${patient.ContactNumber ?? 'N/A'}</p>
                    <p><span class="font-semibold">Email:</span> ${patient.Email ?? 'N/A'}</p>
                    <div class="pt-2">
                        <p class="font-semibold mb-1">Medical History:</p>
                        <p class="bg-gray-50 p-3 rounded-md border border-gray-200 text-sm leading-relaxed">
                            ${patient.MedicalHistory ? patient.MedicalHistory.replace(/\n/g, '<br>') : 'No medical history recorded.'}
                        </p>
                    </div>
                    <div class="pt-2">
                        <p class="font-semibold mb-1">Reason for This Appointment:</p>
                        <p class="bg-gray-50 p-3 rounded-md border border-gray-200 text-sm leading-relaxed">
                            ${appointmentReason ? appointmentReason.replace(/\n/g, '<br>') : 'No specific reason provided for this appointment.'}
                        </p>
                    </div>
                `;
            } else {
                patientDetailsContent.innerHTML = `<p class="text-red-600 text-center">${data.message || 'Failed to load patient details.'}</p>`;
            }
        } catch (error) {
            console.error('Error fetching patient details:', error);
            patientDetailsContent.innerHTML = '<p class="text-red-600 text-center">An error occurred while fetching patient details.</p>';
        }
    }

    closePatientDetailsModal.addEventListener('click', () => {
        patientDetailsModal.classList.add('hidden');
    });

    patientDetailsModal.addEventListener('click', (event) => {
        if (event.target === patientDetailsModal) {
            patientDetailsModal.classList.add('hidden');
        }
    });
</script>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
