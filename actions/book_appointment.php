<?php
// actions/book_appointment.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    $_SESSION['appointment_message'] = 'Unauthorized access.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close(); // Close connection before redirecting
    header("Location: ../index.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $assistant_id = filter_var($_POST['assistant_id'] ?? '', FILTER_VALIDATE_INT);
    $appointment_schedule = htmlspecialchars(trim($_POST['appointment_schedule'] ?? ''));
    $reason_for_appointment = htmlspecialchars(trim($_POST['reason_for_appointment'] ?? '')); // New field
    $payment_method = htmlspecialchars(trim($_POST['payment_method'] ?? ''));


    // Basic validation
    if (empty($assistant_id) || empty($appointment_schedule) || empty($payment_method)) {
        $_SESSION['appointment_message'] = 'Doctor, Appointment Schedule, and Payment Method are required to book an appointment.';
        $_SESSION['appointment_message_type'] = 'error';
        $conn->close(); // Close connection before redirecting
        header("Location: ../dashboard/patient/create_appointment.php");
        exit();
    }

    // Validate appointment schedule is a future date/time
    $current_datetime = new DateTime();
    $chosen_datetime = new DateTime($appointment_schedule);

    if ($chosen_datetime <= $current_datetime) {
        $_SESSION['appointment_message'] = 'Appointment must be scheduled for a future date and time.';
        $_SESSION['appointment_message_type'] = 'error';
        $conn->close(); // Close connection before redirecting
        header("Location: ../dashboard/patient/create_appointment.php");
        exit();
    }

    // Validate payment method against ENUM values
    $allowed_payment_methods = ['Cash', 'Online'];
    if (!in_array($payment_method, $allowed_payment_methods)) {
        $_SESSION['appointment_message'] = 'Invalid payment method selected.';
        $_SESSION['appointment_message_type'] = 'error';
        $conn->close(); // Close connection before redirecting
        header("Location: ../dashboard/patient/create_appointment.php");
        exit();
    }

    // Default status for new appointments is 'Pending'
    $status = 'Pending';
    $room_number = 'Room 101'; // Default room assignment

    // Prepare insert statement - Added ReasonForAppointment
    $stmt = $conn->prepare("INSERT INTO AppointmentTBL (PatientID, AssistantID, RoomNumber, AppointmentSchedule, Status, PaymentMethod, ReasonForAppointment) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        // Bind parameters: 'iisssss' for int, int, string, string, string, string, string
        $stmt->bind_param("iisssss", $patient_id, $assistant_id, $room_number, $appointment_schedule, $status, $payment_method, $reason_for_appointment);

        if ($stmt->execute()) {
            $_SESSION['appointment_message'] = 'Appointment booked successfully! Your appointment is pending confirmation.';
            $_SESSION['appointment_message_type'] = 'success';
            $stmt->close();
            $conn->close(); // Close connection before redirecting
            header("Location: ../dashboard/patient/appointments.php"); // Redirect to My Appointments page
            exit();
        } else {
            $_SESSION['appointment_message'] = 'Error booking appointment: ' . $stmt->error;
            $_SESSION['appointment_message_type'] = 'error';
            error_log("Error booking patient appointment: " . $stmt->error);
            $stmt->close();
            $conn->close(); // Close connection before redirecting
            header("Location: ../dashboard/patient/create_appointment.php"); // Redirect back to form
            exit();
        }
    } else {
        $_SESSION['appointment_message'] = 'Database query preparation failed: ' . $conn->error;
        $_SESSION['appointment_message_type'] = 'error';
        error_log("Failed to prepare statement for booking appointment: " . $conn->error);
        $conn->close(); // Close connection before redirecting
        header("Location: ../dashboard/patient/create_appointment.php"); // Redirect back to form
        exit();
    }
} else {
    $_SESSION['appointment_message'] = 'Invalid request method.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close(); // Close connection before redirecting
    header("Location: ../dashboard/patient/create_appointment.php");
    exit();
}
?>
