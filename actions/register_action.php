<?php
// actions/register_action.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

$redirect_url = '../register.php'; // Default redirect for registration page

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = htmlspecialchars(trim($_POST['role'] ?? ''));
    $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Basic validation
    if (empty($role) || empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['registration_message'] = 'All required fields must be filled.';
        $_SESSION['registration_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Password validation
    if ($password !== $confirmPassword) {
        $_SESSION['registration_message'] = 'Passwords do not match.';
        $_SESSION['registration_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }
    if (strlen($password) < 8) {
        $_SESSION['registration_message'] = 'Password must be at least 8 characters long.';
        $_SESSION['registration_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['registration_message'] = 'Invalid email format.';
        $_SESSION['registration_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    // Check if email already exists in any user table (Patient, Assistant, Admin)
    $email_exists = false;
    $check_tables = ['PatientTBL', 'AssistantTBL', 'AdminTBL'];
    foreach ($check_tables as $table) {
        $stmt_check_email = $conn->prepare("SELECT COUNT(*) FROM " . $table . " WHERE Email = ?");
        if ($stmt_check_email) {
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            $result_check_email = $stmt_check_email->get_result();
            $row_check_email = $result_check_email->fetch_row();
            if ($row_check_email[0] > 0) {
                $email_exists = true;
                break;
            }
            $stmt_check_email->close();
        } else {
            error_log("Failed to prepare email check statement for table " . $table . ": " . $conn->error);
        }
    }

    if ($email_exists) {
        $_SESSION['registration_message'] = 'Email already registered. Please use a different email or login.';
        $_SESSION['registration_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $registration_success = false;
    $insert_message = 'Error during registration.';

    switch ($role) {
        case 'patient':
            $age = filter_var($_POST['age'] ?? '', FILTER_VALIDATE_INT);
            $gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
            $address = htmlspecialchars(trim($_POST['address'] ?? ''));
            $contactNumber = htmlspecialchars(trim($_POST['contactNumber'] ?? ''));
            $medicalHistory = htmlspecialchars(trim($_POST['medicalHistory'] ?? ''));

            // Ensure gender is one of the allowed ENUM values or NULL
            $allowed_genders = ['Male', 'Female', 'Other'];
            if (!in_array($gender, $allowed_genders) && !empty($gender)) {
                $gender = null;
            } elseif (empty($gender)) {
                $gender = null;
            }

            $stmt = $conn->prepare("INSERT INTO PatientTBL (FirstName, LastName, Email, Password, Age, Gender, Address, ContactNumber, MedicalHistory) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssssissss", $firstName, $lastName, $email, $hashed_password, $age, $gender, $address, $contactNumber, $medicalHistory);
                if ($stmt->execute()) {
                    $registration_success = true;
                    $insert_message = 'Patient account created successfully! You can now log in.';
                } else {
                    $insert_message = 'Error creating patient account: ' . $stmt->error;
                    error_log("Error creating patient: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $insert_message = 'Database query preparation failed for patient registration: ' . $conn->error;
                error_log("Failed to prepare patient registration: " . $conn->error);
            }
            break;
        case 'assistant':
            $specialization = htmlspecialchars(trim($_POST['specialization'] ?? ''));
            $sessionFee = filter_var($_POST['sessionFee'] ?? '', FILTER_VALIDATE_FLOAT);

            if ($sessionFee === false || $sessionFee === null) {
                $sessionFee = 0.00; // Default or handle as error
            }

            $stmt = $conn->prepare("INSERT INTO AssistantTBL (FirstName, LastName, Email, Password, Specialization, SessionFee) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssssd", $firstName, $lastName, $email, $hashed_password, $specialization, $sessionFee);
                if ($stmt->execute()) {
                    $registration_success = true;
                    $insert_message = 'Assistant account created successfully! You can now log in.';
                } else {
                    $insert_message = 'Error creating assistant account: ' . $stmt->error;
                    error_log("Error creating assistant: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $insert_message = 'Database query preparation failed for assistant registration: ' . $conn->error;
                error_log("Failed to prepare assistant registration: " . $conn->error);
            }
            break;
        default:
            $_SESSION['registration_message'] = 'Invalid user role selected for registration.';
            $_SESSION['registration_message_type'] = 'error';
            $conn->close();
            header("Location: " . $redirect_url);
            exit();
    }

    $_SESSION['registration_message'] = $insert_message;
    $_SESSION['registration_message_type'] = $registration_success ? 'success' : 'error';
    $conn->close();

    // Redirect to login page on successful registration, else back to register page
    if ($registration_success) {
        header("Location: ../index.php");
    } else {
        header("Location: " . $redirect_url);
    }
    exit();

} else {
    $_SESSION['registration_message'] = 'Invalid request method.';
    $_SESSION['registration_message_type'] = 'error';
    $conn->close();
    header("Location: " . $redirect_url);
    exit();
}
?>
