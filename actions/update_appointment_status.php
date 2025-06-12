<?php
// actions/update_appointment_status.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

// Check if user is logged in and is an assistant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistant') {
    $_SESSION['appointment_message'] = 'Unauthorized access.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$assistant_id = $_SESSION['user_id'];
$appointment_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$action = htmlspecialchars(trim($_GET['action'] ?? '')); // 'complete', 'cancel', or 'ongoing'

if (empty($appointment_id) || empty($action)) {
    $_SESSION['appointment_message'] = 'Invalid request. Missing appointment ID or action.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close();
    header("Location: ../dashboard/assistant/appointments.php");
    exit();
}

// Determine the new status based on the action
$new_status = '';
$allowed_current_statuses = []; // Statuses from which this action is allowed

if ($action === 'complete') {
    $new_status = 'Completed';
    $allowed_current_statuses = ['Pending', 'OnGoing'];
} elseif ($action === 'cancel') {
    $new_status = 'Cancelled';
    $allowed_current_statuses = ['Pending', 'OnGoing'];
} elseif ($action === 'ongoing') { // New 'ongoing' action
    $new_status = 'OnGoing';
    $allowed_current_statuses = ['Pending']; // Only allow setting to OnGoing from Pending
} else {
    $_SESSION['appointment_message'] = 'Invalid action specified.';
    $_SESSION['appointment_message_type'] = 'error';
    $conn->close();
    header("Location: ../dashboard/assistant/appointments.php");
    exit();
}

// Verify the appointment belongs to this assistant and is in a valid state for update
$stmt = $conn->prepare("SELECT Status FROM AppointmentTBL WHERE AppointmentID = ? AND AssistantID = ?");
if ($stmt) {
    $stmt->bind_param("ii", $appointment_id, $assistant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $current_status = $row['Status'];

        // Check if the current status allows the requested action
        if (in_array($current_status, $allowed_current_statuses)) {
            $update_stmt = $conn->prepare("UPDATE AppointmentTBL SET Status = ? WHERE AppointmentID = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("si", $new_status, $appointment_id);
                if ($update_stmt->execute()) {
                    $_SESSION['appointment_message'] = 'Appointment status updated to ' . $new_status . ' successfully.';
                    $_SESSION['appointment_message_type'] = 'success';
                } else {
                    $_SESSION['appointment_message'] = 'Error updating appointment status: ' . $update_stmt->error;
                    $_SESSION['appointment_message_type'] = 'error';
                    error_log("Error updating assistant appointment status: " . $update_stmt->error);
                }
                $update_stmt->close();
            } else {
                $_SESSION['appointment_message'] = 'Database update preparation failed: ' . $conn->error;
                $_SESSION['appointment_message_type'] = 'error';
                error_log("Failed to prepare update statement for assistant: " . $conn->error);
            }
        } else {
            $_SESSION['appointment_message'] = 'Appointment cannot be updated from status "' . htmlspecialchars($current_status) . '" to "' . htmlspecialchars($new_status) . '".';
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
    error_log("Failed to prepare select statement for assistant appointment check: " . $conn->error);
}

$conn->close();
header("Location: ../dashboard/assistant/appointments.php");
exit();
?>
