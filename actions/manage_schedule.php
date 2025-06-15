<?php
// actions/manage_schedule.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

// Ensure user is logged in and is an assistant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistant') {
    $_SESSION['schedule_message'] = 'Unauthorized access.';
    $_SESSION['schedule_message_type'] = 'error';
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$redirect_url = '../dashboard/assistant/schedule.php'; // Redirect back to assistant's schedule page

$action = $_POST['action'] ?? $_GET['action'] ?? null; // Get action from POST or GET
$assistant_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    // Handle adding a new schedule slot
    $dayOfWeek = htmlspecialchars(trim($_POST['day_of_week'] ?? ''));
    $startTime = htmlspecialchars(trim($_POST['start_time'] ?? ''));
    $endTime = htmlspecialchars(trim($_POST['end_time'] ?? ''));

    if (empty($dayOfWeek) || empty($startTime) || empty($endTime)) {
        $_SESSION['schedule_message'] = 'Day of week, start time, and end time are required.';
        $_SESSION['schedule_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Basic time format validation (can be more robust with regex if needed)
    if (!preg_match("/^([01]\d|2[0-3]):([0-5]\d)$/", $startTime) || !preg_match("/^([01]\d|2[0-3]):([0-5]\d)$/", $endTime)) {
        $_SESSION['schedule_message'] = 'Invalid time format. Please use HH:MM.';
        $_SESSION['schedule_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Convert times to DateTime objects for comparison
    $start_dt = new DateTime($startTime);
    $end_dt = new DateTime($endTime);

    if ($start_dt >= $end_dt) {
        $_SESSION['schedule_message'] = 'End time must be after start time.';
        $_SESSION['schedule_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Check for overlapping schedules for the same assistant on the same day
    $overlap_check_stmt = $conn->prepare("
        SELECT COUNT(*) FROM AssistantScheduleTBL
        WHERE AssistantID = ? AND DayOfWeek = ?
        AND (
            (? < EndTime AND ? > StartTime) OR
            (? = StartTime AND ? = EndTime)
        )
    ");

    if ($overlap_check_stmt) {
        $overlap_check_stmt->bind_param("isssss", $assistant_id, $dayOfWeek, $startTime, $endTime, $startTime, $endTime);
        $overlap_check_stmt->execute();
        $overlap_result = $overlap_check_stmt->get_result();
        $overlap_row = $overlap_result->fetch_row();
        if ($overlap_row[0] > 0) {
            $_SESSION['schedule_message'] = 'This time slot overlaps with an existing schedule for the selected day.';
            $_SESSION['schedule_message_type'] = 'error';
            $overlap_check_stmt->close();
            $conn->close();
            header("Location: " . $redirect_url);
            exit();
        }
        $overlap_check_stmt->close();
    } else {
        error_log("Failed to prepare overlap check statement: " . $conn->error);
        $_SESSION['schedule_message'] = 'Database error during schedule check.';
        $_SESSION['schedule_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }


    $stmt = $conn->prepare("INSERT INTO AssistantScheduleTBL (AssistantID, DayOfWeek, StartTime, EndTime, IsAvailable) VALUES (?, ?, ?, ?, TRUE)");
    if ($stmt) {
        // 'isss' for int, string, string, string
        $stmt->bind_param("isss", $assistant_id, $dayOfWeek, $startTime, $endTime);
        if ($stmt->execute()) {
            $_SESSION['schedule_message'] = 'Availability added successfully!';
            $_SESSION['schedule_message_type'] = 'success';
        } else {
            $_SESSION['schedule_message'] = 'Error adding availability: ' . $stmt->error;
            $_SESSION['schedule_message_type'] = 'error';
            error_log("Error adding assistant schedule: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['schedule_message'] = 'Database query preparation failed: ' . $conn->error;
        $_SESSION['schedule_message_type'] = 'error';
        error_log("Failed to prepare statement for adding schedule: " . $conn->error);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    // Handle deleting a schedule slot
    $schedule_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

    if (empty($schedule_id)) {
        $_SESSION['schedule_message'] = 'Invalid schedule ID provided.';
        $_SESSION['schedule_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Ensure the schedule slot belongs to the logged-in assistant before deleting
    $stmt = $conn->prepare("DELETE FROM AssistantScheduleTBL WHERE ScheduleID = ? AND AssistantID = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $schedule_id, $assistant_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['schedule_message'] = 'Schedule slot deleted successfully.';
                $_SESSION['schedule_message_type'] = 'success';
            } else {
                $_SESSION['schedule_message'] = 'Schedule slot not found or does not belong to you.';
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
} else {
    $_SESSION['schedule_message'] = 'Invalid request.';
    $_SESSION['schedule_message_type'] = 'error';
}

$conn->close();
header("Location: " . $redirect_url);
exit();
?>
