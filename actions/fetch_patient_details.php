<?php
// actions/fetch_patient_details.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

header('Content-Type: application/json'); // Ensure JSON response

$response = ['success' => false, 'message' => '', 'patient' => null, 'appointment_reason' => null];

// Check if user is logged in and is an assistant (only assistants should view patient details)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistant') {
    $response['message'] = 'Unauthorized access.';
    $conn->close();
    echo json_encode($response);
    exit();
}

$patient_id = filter_var($_GET['patient_id'] ?? '', FILTER_VALIDATE_INT);
$appointment_id = filter_var($_GET['appointment_id'] ?? '', FILTER_VALIDATE_INT); // New: Get appointment ID

if (empty($patient_id)) {
    $response['message'] = 'Invalid patient ID provided.';
    $conn->close();
    echo json_encode($response);
    exit();
}

// Fetch patient details
$stmt = $conn->prepare("SELECT FirstName, LastName, Age, Gender, Address, ContactNumber, Email, MedicalHistory FROM PatientTBL WHERE PatientID = ?");

if ($stmt) {
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $patient_data = $result->fetch_assoc();
        $response['patient'] = $patient_data;
        $response['success'] = true;

        // Fetch ReasonForAppointment if appointment_id is provided
        if (!empty($appointment_id)) {
            $appt_stmt = $conn->prepare("SELECT ReasonForAppointment FROM AppointmentTBL WHERE AppointmentID = ? AND PatientID = ?");
            if ($appt_stmt) {
                $appt_stmt->bind_param("ii", $appointment_id, $patient_id);
                $appt_stmt->execute();
                $appt_result = $appt_stmt->get_result();
                if ($appt_result->num_rows > 0) {
                    $appt_row = $appt_result->fetch_assoc();
                    $response['appointment_reason'] = $appt_row['ReasonForAppointment'];
                }
                $appt_stmt->close();
            } else {
                error_log("Failed to prepare statement for fetching appointment reason: " . $conn->error);
            }
        }

    } else {
        $response['message'] = 'Patient not found.';
    }
    $stmt->close();
} else {
    $response['message'] = 'Database query preparation failed: ' . $conn->error;
    error_log("Failed to prepare statement for fetching patient details: " . $conn->error);
}

$conn->close();
echo json_encode($response);
exit();
?>
