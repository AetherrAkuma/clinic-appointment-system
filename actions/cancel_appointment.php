<?php
// actions/cancel_appointment.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    $_SESSION['appointment_message'] = 'Unauthorized access.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$appointment_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if (empty($appointment_id)) {
    $_SESSION['appointment_message'] = 'Invalid appointment ID provided.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close();
    header("Location: ../dashboard/patient/appointments.php");
    exit();
}

// Check if the appointment belongs to the logged-in patient and is not already cancelled/completed
$stmt = $conn->prepare("SELECT Status FROM AppointmentTBL WHERE AppointmentID = ? AND PatientID = ?");
if ($stmt) {
    $stmt->bind_param("ii", $appointment_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $current_status = $row['Status'];

        if ($current_status === 'Pending' || $current_status === 'OnGoing') {
            // Update the appointment status to 'Cancelled'
            $update_stmt = $conn->prepare("UPDATE AppointmentTBL SET Status = 'Cancelled' WHERE AppointmentID = ? AND PatientID = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("ii", $appointment_id, $patient_id);
                if ($update_stmt->execute()) {
                    $_SESSION['appointment_message'] = 'Appointment successfully cancelled.';
                    $_SESSION['appointment_message_type'] = 'success';
                } else {
                    $_SESSION['appointment_message'] = 'Error cancelling appointment: ' . $update_stmt->error;
                    $_SESSION['appointment_message_type'] = 'error';
                    error_log("Error cancelling appointment: " . $update_stmt->error);
                }
                $update_stmt->close();
            } else {
                $_SESSION['appointment_message'] = 'Database update preparation failed: ' . $conn->error;
                $_SESSION['appointment_message_type'] = 'error';
                error_log("Failed to prepare update statement for cancellation: " . $conn->error);
            }
        } else {
            $_SESSION['appointment_message'] = 'Appointment cannot be cancelled as its status is ' . htmlspecialchars($current_status) . '.';
            $_SESSION['appointment_message_type'] = 'error';
        }
    } else {
        $_SESSION['appointment_message'] = 'Appointment not found or does not belong to you.';
        $_SESSION['appointment_message_type'] = 'error';
    }
    $stmt->close();
} else {
    $_SESSION['appointment_message'] = 'Database query preparation failed: ' . $conn->error;
    $_SESSION['appointment_message_type'] = 'error';
    error_log("Failed to prepare select statement for cancellation check: " . $conn->error);
}

$conn->close();
header("Location: ../dashboard/patient/appointments.php");
exit();
?>
