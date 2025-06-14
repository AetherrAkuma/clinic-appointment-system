<?php
// actions/assistant_appointment_action.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

// Ensure user is logged in and is an assistant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistant') {
    $_SESSION['appointment_message'] = 'Unauthorized access.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$redirect_url = '../dashboard/assistant/appointments.php'; // Redirect back to assistant's appointments page

$action = $_GET['action'] ?? null;
$appointment_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$assistant_id = $_SESSION['user_id'];

if (empty($action) || empty($appointment_id)) {
    $_SESSION['appointment_message'] = 'Invalid action or appointment ID.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close();
    header("Location: " . $redirect_url);
    exit();
}

$update_success = false;
$update_message = '';
$new_status = '';

switch ($action) {
    case 'start':
        $new_status = 'OnGoing';
        $update_message = 'Appointment started successfully!';
        break;
    case 'cancel':
        $new_status = 'Cancelled';
        $update_message = 'Appointment cancelled successfully!';
        break;
    default:
        $_SESSION['appointment_message'] = 'Invalid action specified.';
        $_SESSION['appointment_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
}

// Update the appointment status
// Ensure only the assistant assigned to the appointment can update it.
$stmt = $conn->prepare("UPDATE AppointmentTBL SET Status = ? WHERE AppointmentID = ? AND AssistantID = ?");
if ($stmt) {
    $stmt->bind_param("sii", $new_status, $appointment_id, $assistant_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['appointment_message'] = $update_message;
            $_SESSION['appointment_message_type'] = 'success';
        } else {
            // No rows affected might mean appointment not found, or not in a state to be updated,
            // or the assistant is not assigned to it.
            $_SESSION['appointment_message'] = 'Failed to update appointment: It might not exist, already be in that status, or you are not assigned to it.';
            $_SESSION['appointment_message_type'] = 'error';
        }
    } else {
        $_SESSION['appointment_message'] = 'Database error: ' . $stmt->error;
        $_SESSION['appointment_message_type'] = 'error';
        error_log("Database error in assistant_appointment_action.php: " . $stmt->error);
    }
    $stmt->close();
} else {
    $_SESSION['appointment_message'] = 'Database query preparation failed: ' . $conn->error;
    $_SESSION['appointment_message_type'] = 'error';
    error_log("Failed to prepare statement in assistant_appointment_action.php: " . $conn->error);
}

$conn->close();
header("Location: " . $redirect_url);
exit();
?>
