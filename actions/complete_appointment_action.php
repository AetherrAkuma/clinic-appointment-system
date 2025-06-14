<?php
// actions/complete_appointment_action.php
session_start();
require_once '../config/db_connection.php';

// Ensure user is logged in and is an assistant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistant') {
    $_SESSION['appointment_message'] = 'Unauthorized access.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$redirect_url = '../dashboard/assistant/appointments.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointmentId'])) {
    $appointmentId = filter_var($_POST['appointmentId'], FILTER_VALIDATE_INT);
    $prescription = htmlspecialchars(trim($_POST['prescription'] ?? ''));
    $quantity = htmlspecialchars(trim($_POST['quantity'] ?? ''));
    $noPrescription = isset($_POST['noPrescription']) ? true : false; // Check if the "No Prescription" checkbox was ticked

    if (empty($appointmentId)) {
        $_SESSION['appointment_message'] = 'Invalid Appointment ID.';
        $_SESSION['appointment_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Handle "No Prescription" option
    if ($noPrescription) {
        // If "No Prescription" is checked, set prescription and quantity to NULL in DB
        $prescription = NULL;
        $quantity = NULL;
    } else {
        // If not checked, but fields are empty, still set to NULL or give a warning
        // For this implementation, if not explicitly "No Prescription" and fields are empty, treat as NULL
        if (empty($prescription)) {
            $prescription = NULL;
        }
        if (empty($quantity)) {
            $quantity = NULL;
        }
    }

    // Update appointment status to 'Completed' and add prescription/quantity
    $stmt = $conn->prepare("UPDATE AppointmentTBL SET Status = 'Completed', Prescription = ?, Quantity = ? WHERE AppointmentID = ? AND AssistantID = ?");
    if ($stmt) {
        // Note: 's' for string (Prescription), 's' for string (Quantity), 'i' for int (AppointmentID), 'i' for int (AssistantID)
        // If $prescription or $quantity are NULL, PDO will handle it correctly with 's' type.
        $stmt->bind_param("ssii", $prescription, $quantity, $appointmentId, $_SESSION['user_id']);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['appointment_message'] = 'Appointment marked as Completed and prescription details saved!';
                $_SESSION['appointment_message_type'] = 'success';
            } else {
                $_SESSION['appointment_message'] = 'Appointment not found, not ongoing, or no changes made.';
                $_SESSION['appointment_message_type'] = 'error';
            }
        } else {
            $_SESSION['appointment_message'] = 'Error completing appointment: ' . $stmt->error;
            $_SESSION['appointment_message_type'] = 'error';
            error_log("Error completing appointment and saving prescription: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['appointment_message'] = 'Database query preparation failed: ' . $conn->error;
        $_SESSION['appointment_message_type'] = 'error';
        error_log("Failed to prepare statement for completing appointment: " . $conn->error);
    }
} else {
    $_SESSION['appointment_message'] = 'Invalid request to complete appointment.';
    $_SESSION['appointment_message_type'] = 'error';
}

$conn->close();
header("Location: " . $redirect_url);
exit();
?>
