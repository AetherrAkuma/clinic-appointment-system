<?php
// dashboard/patient/create_appointment.php
$page_title = "Book Appointment";
include_once '../../includes/header.php'; // Include the common header

$doctors = [];
$message = '';
$message_type = ''; // 'success' or 'error'

// Check for messages from book_appointment.php
if (isset($_SESSION['appointment_message'])) {
    $message = $_SESSION['appointment_message'];
    $message_type = $_SESSION['appointment_message_type'];
    unset($_SESSION['appointment_message']); // Clear the message after displaying
    unset($_SESSION['appointment_message_type']);
}

// Fetch available doctors
$stmt = $conn->prepare("SELECT AssistantID, FirstName, LastName, Specialization, SessionFee FROM AssistantTBL ORDER BY LastName, FirstName");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    $stmt->close();
} else {
    error_log("Failed to prepare statement for fetching doctors: " . $conn->error);
    $message = "Error fetching doctor list.";
    $message_type = 'error';
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-blue-800 mb-4">Book a New Appointment</h1>
    <p class="text-gray-700 text-lg">Fill out the form below to schedule your appointment.</p>
</div>

<?php if (!empty($message)): ?>
    <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400'; ?> border-l-4 shadow-sm" role="alert">
        <p class="font-bold"><?php echo htmlspecialchars($message_type === 'success' ? 'Success!' : 'Error!'); ?></p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form id="bookAppointmentForm" action="../../actions/book_appointment.php" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"> <!-- Using grid-cols-1 for mobile, md:grid-cols-2 for wider screens -->
            <div>
                <label for="doctor" class="block text-gray-700 text-sm font-medium mb-2">Select Doctor</label>
                <select id="doctor" name="assistant_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Please select a doctor --</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?php echo htmlspecialchars($doctor['AssistantID']); ?>">
                            Dr. <?php echo htmlspecialchars($doctor['FirstName'] . ' ' . $doctor['LastName']); ?> (<?php echo htmlspecialchars($doctor['Specialization']); ?>) - ₱<?php echo number_format($doctor['SessionFee'], 2); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="appointmentSchedule" class="block text-gray-700 text-sm font-medium mb-2">Appointment Date & Time</label>
                <input type="datetime-local" id="appointmentSchedule" name="appointment_schedule"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <p class="text-xs text-gray-500 mt-1">Please select a future date and time.</p>
            </div>
            <div class="md:col-span-2"> <!-- Make this field span two columns on medium screens and up -->
                <label for="reasonForAppointment" class="block text-gray-700 text-sm font-medium mb-2">Reason for Appointment</label>
                <textarea id="reasonForAppointment" name="reason_for_appointment" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"
                          placeholder="Briefly describe the reason for your visit."></textarea>
            </div>
            <div>
                <label for="paymentMethod" class="block text-gray-700 text-sm font-medium mb-2">Payment Method</label>
                <select id="paymentMethod" name="payment_method"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Select Payment Method --</option>
                    <option value="Cash">Cash</option>
                    <option value="Online">Online</option>
                </select>
            </div>
            <!-- Room Number is typically assigned by admin, so not included in patient booking -->
        </div>

        <div class="flex justify-end space-x-4">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                Confirm Booking
            </button>
        </div>
    </form>
</div>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
