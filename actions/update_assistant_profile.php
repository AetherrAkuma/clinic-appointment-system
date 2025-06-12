<?php
// actions/update_assistant_profile.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

// Check if user is logged in and is an assistant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistant') {
    $_SESSION['profile_message'] = 'Unauthorized access.';
    $_SESSION['profile_message_type'] = 'error';
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$assistant_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
    $specialization = htmlspecialchars(trim($_POST['specialization'] ?? ''));
    $sessionFee = filter_var($_POST['sessionFee'] ?? '', FILTER_VALIDATE_FLOAT); // Allow float for currency

    // Basic validation
    if (empty($firstName) || empty($lastName)) {
        $_SESSION['profile_message'] = 'First Name and Last Name are required.';
        $_SESSION['profile_message_type'] = 'error';
        $conn->close();
        header("Location: ../dashboard/assistant/profile.php");
        exit();
    }

    // Ensure sessionFee is a valid number, default to 0.00 if invalid or empty
    if ($sessionFee === false || $sessionFee === null) {
        $sessionFee = 0.00;
    }

    // Prepare update statement
    $stmt = $conn->prepare("UPDATE AssistantTBL SET FirstName = ?, LastName = ?, Specialization = ?, SessionFee = ? WHERE AssistantID = ?");

    if ($stmt) {
        // Corrected Bind parameters: 'sssdi' for string, string, string, double, int
        // FirstName (s), LastName (s), Specialization (s), SessionFee (d), AssistantID (i)
        $stmt->bind_param("sssdi", $firstName, $lastName, $specialization, $sessionFee, $assistant_id);

        if ($stmt->execute()) {
            $_SESSION['profile_message'] = 'Profile updated successfully!';
            $_SESSION['profile_message_type'] = 'success';
        } else {
            $_SESSION['profile_message'] = 'Error updating profile: ' . $stmt->error;
            $_SESSION['profile_message_type'] = 'error';
            error_log("Error updating assistant profile: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['profile_message'] = 'Database query preparation failed: ' . $conn->error;
        $_SESSION['profile_message_type'] = 'error';
        error_log("Failed to prepare statement for assistant profile update: " . $conn->error);
    }
} else {
    $_SESSION['profile_message'] = 'Invalid request method.';
    $_SESSION['profile_message_type'] = 'error';
}

$conn->close();
header("Location: ../dashboard/assistant/profile.php");
exit();
?>
