<?php
// actions/update_profile.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    $_SESSION['profile_message'] = 'Unauthorized access.';
    $_SESSION['profile_message_type'] = 'error';
    header("Location: ../index.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
    $age = filter_var($_POST['age'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
    $address = htmlspecialchars(trim($_POST['address'] ?? ''));
    $contactNumber = htmlspecialchars(trim($_POST['contactNumber'] ?? ''));
    $medicalHistory = htmlspecialchars(trim($_POST['medicalHistory'] ?? ''));

    // Basic validation
    if (empty($firstName) || empty($lastName)) {
        $_SESSION['profile_message'] = 'First Name and Last Name are required.';
        $_SESSION['profile_message_type'] = 'error';
        header("Location: ../dashboard/patient/profile.php");
        exit();
    }

    // Ensure gender is one of the allowed ENUM values or NULL
    $allowed_genders = ['Male', 'Female', 'Other'];
    if (!in_array($gender, $allowed_genders) && !empty($gender)) {
        $gender = null; // Set to null if invalid, or handle as error
    } elseif (empty($gender)) {
        $gender = null; // Store as null if not provided
    }

    // Prepare update statement
    $stmt = $conn->prepare("UPDATE PatientTBL SET FirstName = ?, LastName = ?, Age = ?, Gender = ?, Address = ?, ContactNumber = ?, MedicalHistory = ? WHERE PatientID = ?");

    if ($stmt) {
        // Bind parameters
        // 'ssissssi' for string, string, int, string, string, string, string, int
        $stmt->bind_param("ssissssi", $firstName, $lastName, $age, $gender, $address, $contactNumber, $medicalHistory, $patient_id);

        if ($stmt->execute()) {
            $_SESSION['profile_message'] = 'Profile updated successfully!';
            $_SESSION['profile_message_type'] = 'success';
        } else {
            $_SESSION['profile_message'] = 'Error updating profile: ' . $stmt->error;
            $_SESSION['profile_message_type'] = 'error';
            error_log("Error updating patient profile: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['profile_message'] = 'Database query preparation failed: ' . $conn->error;
        $_SESSION['profile_message_type'] = 'error';
        error_log("Failed to prepare statement for profile update: " . $conn->error);
    }
} else {
    $_SESSION['profile_message'] = 'Invalid request method.';
    $_SESSION['profile_message_type'] = 'error';
}

$conn->close();
header("Location: ../dashboard/patient/profile.php");
exit();
?>
