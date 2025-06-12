<?php
// actions/admin_update_appointment.php
session_start();
require_once '../config/db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['admin_appointment_message'] = 'Unauthorized access.';
    $_SESSION['admin_appointment_message_type'] = 'error';
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$redirect_url = '../dashboard/admin/manage_appointments.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handling form submission for editing appointment details
    $appointment_id = filter_var($_POST['appointment_id'] ?? '', FILTER_VALIDATE_INT);
    $assistant_id = filter_var($_POST['assistant_id'] ?? '', FILTER_VALIDATE_INT);
    $appointment_schedule = htmlspecialchars(trim($_POST['appointment_schedule'] ?? ''));
    $room_number = htmlspecialchars(trim($_POST['room_number'] ?? ''));
    $status = htmlspecialchars(trim($_POST['status'] ?? ''));
    $payment_method = htmlspecialchars(trim($_POST['payment_method'] ?? ''));
    $reason_for_appointment = htmlspecialchars(trim($_POST['reason_for_appointment'] ?? ''));

    if (empty($appointment_id) || empty($assistant_id) || empty($appointment_schedule) || empty($status) || empty($payment_method)) {
        $_SESSION['admin_appointment_message'] = 'Missing required fields for appointment update.';
        $_SESSION['admin_appointment_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Validate Status against ENUM values
    $allowed_statuses = ['Pending', 'OnGoing', 'Completed', 'Cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        $_SESSION['admin_appointment_message'] = 'Invalid status selected.';
        $_SESSION['admin_appointment_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Validate PaymentMethod against ENUM values (though readonly on form, still good to validate)
    $allowed_payment_methods = ['Cash', 'Online'];
    if (!in_array($payment_method, $allowed_payment_methods)) {
        $_SESSION['admin_appointment_message'] = 'Invalid payment method provided.';
        $_SESSION['admin_appointment_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Prepare update statement
    $stmt = $conn->prepare("UPDATE AppointmentTBL SET AssistantID = ?, AppointmentSchedule = ?, RoomNumber = ?, Status = ?, PaymentMethod = ?, ReasonForAppointment = ? WHERE AppointmentID = ?");
    if ($stmt) {
        // 'isssssi' for int, string, string, string, string, string, int
        $stmt->bind_param("isssssi", $assistant_id, $appointment_schedule, $room_number, $status, $payment_method, $reason_for_appointment, $appointment_id);

        if ($stmt->execute()) {
            $_SESSION['admin_appointment_message'] = 'Appointment details updated successfully!';
            $_SESSION['admin_appointment_message_type'] = 'success';
        } else {
            $_SESSION['admin_appointment_message'] = 'Error updating appointment: ' . $stmt->error;
            $_SESSION['admin_appointment_message_type'] = 'error';
            error_log("Admin update appointment error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['admin_appointment_message'] = 'Database query preparation failed: ' . $conn->error;
        $_SESSION['admin_appointment_message_type'] = 'error';
        error_log("Admin update appointment prepare error: " . $conn->error);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    // Handling status update OR delete via GET request
    $appointment_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
    $action = htmlspecialchars(trim($_GET['action'] ?? ''));

    if (empty($appointment_id) || empty($action)) {
        $_SESSION['admin_appointment_message'] = 'Invalid request. Missing appointment ID or action.';
        $_SESSION['admin_appointment_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    if ($action === 'delete') {
        // --- Delete Appointment ---
        $stmt = $conn->prepare("DELETE FROM AppointmentTBL WHERE AppointmentID = ?");
        if ($stmt) {
            $stmt->bind_param("i", $appointment_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $_SESSION['admin_appointment_message'] = 'Appointment record deleted successfully.';
                    $_SESSION['admin_appointment_message_type'] = 'success';
                } else {
                    $_SESSION['admin_appointment_message'] = 'Appointment not found or already deleted.';
                    $_SESSION['admin_appointment_message_type'] = 'error';
                }
            } else {
                $_SESSION['admin_appointment_message'] = 'Error deleting appointment: ' . $stmt->error;
                $_SESSION['admin_appointment_message_type'] = 'error';
                error_log("Admin delete appointment error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $_SESSION['admin_appointment_message'] = 'Database query preparation failed for deletion: ' . $conn->error;
            $_SESSION['admin_appointment_message_type'] = 'error';
            error_log("Admin delete appointment prepare error: " . $conn->error);
        }
    } else {
        // --- Handle Status Update (existing logic) ---
        $new_status = '';
        $allowed_current_statuses = ['Pending', 'OnGoing']; // Allowed previous statuses to transition from

        if ($action === 'complete') {
            $new_status = 'Completed';
        } elseif ($action === 'cancel') {
            $new_status = 'Cancelled';
        } else {
            $_SESSION['admin_appointment_message'] = 'Invalid action specified for status update.';
            $_SESSION['admin_appointment_message_type'] = 'error';
            $conn->close();
            header("Location: " . $redirect_url);
            exit();
        }

        // Fetch current status to ensure valid transition
        $stmt = $conn->prepare("SELECT Status FROM AppointmentTBL WHERE AppointmentID = ?");
        if ($stmt) {
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $current_status = $row['Status'];

                if (in_array($current_status, $allowed_current_statuses)) {
                    $update_stmt = $conn->prepare("UPDATE AppointmentTBL SET Status = ? WHERE AppointmentID = ?");
                    if ($update_stmt) {
                        $update_stmt->bind_param("si", $new_status, $appointment_id);
                        if ($update_stmt->execute()) {
                            $_SESSION['admin_appointment_message'] = 'Appointment status updated to ' . $new_status . ' successfully.';
                            $_SESSION['admin_appointment_message_type'] = 'success';
                        } else {
                            $_SESSION['admin_appointment_message'] = 'Error updating status: ' . $update_stmt->error;
                            $_SESSION['admin_appointment_message_type'] = 'error';
                            error_log("Admin status update error: " . $update_stmt->error);
                        }
                        $update_stmt->close();
                    } else {
                        $_SESSION['admin_appointment_message'] = 'Database update preparation failed: ' . $conn->error;
                        $_SESSION['admin_appointment_message_type'] = 'error';
                        error_log("Admin status update prepare error: " . $conn->error);
                    }
                } else {
                     $_SESSION['admin_appointment_message'] = 'Cannot change status from "' . htmlspecialchars($current_status) . '" to "' . htmlspecialchars($new_status) . '".';
                    $_SESSION['admin_appointment_message_type'] = 'error';
                }
            } else {
                $_SESSION['admin_appointment_message'] = 'Appointment not found.';
                $_SESSION['admin_appointment_message_type'] = 'error';
            }
            $stmt->close();
        } else {
            $_SESSION['admin_appointment_message'] = 'Database query preparation failed: ' . $conn->error;
            $_SESSION['admin_appointment_message_type'] = 'error';
            error_log("Admin fetch status prepare error: " . $conn->error);
        }
    }
} else {
    $_SESSION['admin_appointment_message'] = 'Invalid request method or action.';
    $_SESSION['admin_appointment_message_type'] = 'error';
}

$conn->close();
header("Location: " . $redirect_url);
exit();
?>
