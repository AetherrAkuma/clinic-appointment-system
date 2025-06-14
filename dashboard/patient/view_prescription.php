<?php
// dashboard/patient/view_prescription.php
$page_title = "View Prescription";
include_once '../../includes/header.php'; // Use the unified header

// Check if the logged-in user is a patient
if ($_SESSION['user_role'] !== 'patient') {
    header("Location: /clinic-management/index.php"); // Redirect if not patient
    exit();
}

$patient_id = $_SESSION['user_id'];
$appointment_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$prescription_details = null;
$message = '';
$message_type = '';

if (empty($appointment_id)) {
    $message = "Invalid appointment ID provided.";
    $message_type = 'error';
} else {
    // Fetch appointment details including prescription and quantity for the specific patient
    $stmt = $conn->prepare("
        SELECT
            a.AppointmentID,
            a.AppointmentSchedule,
            a.RoomNumber,
            a.Status,
            a.ReasonForAppointment,
            a.Prescription,
            a.Quantity,
            ast.FirstName AS AssistantFirstName,
            ast.LastName AS AssistantLastName,
            ast.Specialization,
            ast.SessionFee
        FROM
            AppointmentTBL a
        JOIN
            AssistantTBL ast ON a.AssistantID = ast.AssistantID
        WHERE
            a.AppointmentID = ? AND a.PatientID = ? AND a.Status = 'Completed'
    ");

    if ($stmt) {
        $stmt->bind_param("ii", $appointment_id, $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $prescription_details = $result->fetch_assoc();
        } else {
            $message = "Prescription not found or appointment is not completed.";
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = "Database query preparation failed: " . $conn->error;
        $message_type = 'error';
        error_log("Failed to prepare statement for viewing prescription: " . $conn->error);
    }
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">View Prescription</h1>
    <p class="text-gray-700 text-lg">Details of your completed appointment and received prescription.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<?php if ($prescription_details): ?>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Appointment Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
            <div><span class="font-semibold">Appointment ID:</span> <?php echo htmlspecialchars($prescription_details['AppointmentID']); ?></div>
            <div><span class="font-semibold">Doctor:</span> Dr. <?php echo htmlspecialchars($prescription_details['AssistantFirstName'] . ' ' . $prescription_details['AssistantLastName']); ?> (<?php echo htmlspecialchars($prescription_details['Specialization']); ?>)</div>
            <div><span class="font-semibold">Scheduled:</span> <?php echo date('F j, Y, g:i A', strtotime($prescription_details['AppointmentSchedule'])); ?></div>
            <div><span class="font-semibold">Room Number:</span> <?php echo htmlspecialchars($prescription_details['RoomNumber'] ?? 'N/A'); ?></div>
            <div class="md:col-span-2"><span class="font-semibold">Reason for Appointment:</span> <?php echo htmlspecialchars($prescription_details['ReasonForAppointment'] ?? 'N/A'); ?></div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Prescription Details</h2>
        <?php if (!empty($prescription_details['Prescription'])): ?>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-medium mb-2">Prescription:</p>
                <div class="p-3 bg-gray-50 border border-gray-200 rounded-md text-gray-800 whitespace-pre-wrap">
                    <?php echo htmlspecialchars(trim($prescription_details['Prescription'])); ?>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 text-sm font-medium mb-2">Quantity/Dosage:</p>
                <div class="p-3 bg-gray-50 border border-gray-200 rounded-md text-gray-800">
                    <?php echo htmlspecialchars(trim($prescription_details['Quantity'] ?? 'N/A')); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-4 rounded-md shadow-sm" role="alert">
                <p class="font-bold">No Prescription Issued</p>
                <p>The doctor did not issue a specific prescription for this appointment.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-6 text-center">
        <a href="appointments.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-300 ease-in-out">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Appointments
        </a>
    </div>

<?php endif; ?>

<?php include_once '../../includes/footer.php'; ?>
