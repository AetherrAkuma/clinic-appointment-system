<?php
// actions/manage_schedule.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

// Check if user is logged in and is an assistant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistant') {
    $_SESSION['schedule_message'] = 'Unauthorized access.';
    $_SESSION['schedule_message_type'] = 'error';
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$assistant_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? ''; // Get action from POST or GET

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    // --- Add Schedule Slot ---
    $available_date = htmlspecialchars(trim($_POST['available_date'] ?? ''));
    $start_time = htmlspecialchars(trim($_POST['start_time'] ?? ''));
    $end_time = htmlspecialchars(trim($_POST['end_time'] ?? ''));

    if (empty($available_date) || empty($start_time) || empty($end_time)) {
        $_SESSION['schedule_message'] = 'All date and time fields are required to add a schedule slot.';
        $_SESSION['schedule_message_type'] = 'error';
        $conn->close();
        header("Location: ../dashboard/assistant/schedule.php");
        exit();
    }

    // Validate date is not in the past
    $current_date = new DateTime();
    $chosen_date = new DateTime($available_date);
    if ($chosen_date < $current_date->setTime(0, 0, 0)) { // Compare only dates
        $_SESSION['schedule_message'] = 'Cannot add schedule for a past date.';
        $_SESSION['schedule_message_type'] = 'error';
        $conn->close();
        header("Location: ../dashboard/assistant/schedule.php");
        exit();
    }

    // Validate start time is before end time
    if ($start_time >= $end_time) {
        $_SESSION['schedule_message'] = 'Start time must be before end time.';
        $_SESSION['schedule_message_type'] = 'error';
        $conn->close();
        header("Location: ../dashboard/assistant/schedule.php");
        exit();
    }

    // Combine date and time for full datetime comparison if needed for overlap logic
    // For now, we rely on the UNIQUE constraint for preventing exact duplicates

    $stmt = $conn->prepare("INSERT INTO AssistantScheduleTBL (AssistantID, AvailableDate, StartTime, EndTime) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isss", $assistant_id, $available_date, $start_time, $end_time);
        if ($stmt->execute()) {
            $_SESSION['schedule_message'] = 'Availability slot added successfully!';
            $_SESSION['schedule_message_type'] = 'success';
        } else {
            // Check for duplicate entry error specifically (MySQL error code 1062)
            if ($conn->errno === 1062) {
                $_SESSION['schedule_message'] = 'Error: This exact time slot is already added for you on this date.';
                $_SESSION['schedule_message_type'] = 'error';
            } else {
                $_SESSION['schedule_message'] = 'Error adding availability: ' . $stmt->error;
                $_SESSION['schedule_message_type'] = 'error';
                error_log("Error adding assistant schedule: " . $stmt->error);
            }
        }
        $stmt->close();
    } else {
        $_SESSION['schedule_message'] = 'Database query preparation failed: ' . $conn->error;
        $_SESSION['schedule_message_type'] = 'error';
        error_log("Failed to prepare statement for adding schedule: " . $conn->error);
    }

    $conn->close();
    header("Location: ../dashboard/assistant/schedule.php");
    exit();

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    // --- Delete Schedule Slot ---
    $schedule_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

    if (empty($schedule_id)) {
        $_SESSION['schedule_message'] = 'Invalid schedule ID provided for deletion.';
        $_SESSION['schedule_message_type'] = 'error';
        $conn->close();
        header("Location: ../dashboard/assistant/schedule.php");
        exit();
    }

    // Ensure the slot belongs to the logged-in assistant before deleting
    $stmt = $conn->prepare("DELETE FROM AssistantScheduleTBL WHERE ScheduleID = ? AND AssistantID = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $schedule_id, $assistant_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['schedule_message'] = 'Schedule slot deleted successfully.';
                $_SESSION['schedule_message_type'] = 'success';
            } else {
                $_SESSION['schedule_message'] = 'Schedule slot not found or you do not have permission to delete it.';
                $_SESSION['schedule_message_type'] = 'error';
            }
        } else {
            $_SESSION['schedule_message'] = 'Error deleting schedule slot: ' . $stmt->error;
            $_SESSION['schedule_message_type'] = 'error';
            error_log("Error deleting assistant schedule: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['schedule_message'] = 'Database query preparation failed: ' . $conn->error;
        $_SESSION['schedule_message_type'] = 'error';
        error_log("Failed to prepare statement for deleting schedule: " . $conn->error);
    }

    $conn->close();
    header("Location: ../dashboard/assistant/schedule.php");
    exit();

} else {
    // Invalid action or request method
    $_SESSION['schedule_message'] = 'Invalid request.';
    $_SESSION['schedule_message_type'] = 'error';
    $conn->close();
    header("Location: ../dashboard/assistant/schedule.php");
    exit();
}
?>
