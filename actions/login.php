<?php
// actions/login.php
require_once '../config/db_connection.php'; // Include the database connection

header('Content-Type: application/json'); // Ensure JSON response

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response['message'] = 'Please enter both email and password.';
        echo json_encode($response);
        exit();
    }

    // Try to authenticate as Patient
    $stmt = $conn->prepare("SELECT PatientID AS user_id, Email, Password FROM PatientTBL WHERE Email = ?");
    if (!$stmt) {
        $response['message'] = 'Database query preparation failed: ' . $conn->error;
        echo json_encode($response);
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = 'patient';
            $response['success'] = true;
            $response['message'] = 'Login successful as patient.';
            $response['redirect_url'] = 'dashboard/patient/index.php';
            echo json_encode($response);
            exit();
        }
    }
    $stmt->close();

    // Try to authenticate as Assistant
    $stmt = $conn->prepare("SELECT AssistantID AS user_id, Email, Password FROM AssistantTBL WHERE Email = ?");
    if (!$stmt) {
        $response['message'] = 'Database query preparation failed: ' . $conn->error;
        echo json_encode($response);
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = 'assistant';
            $response['success'] = true;
            $response['message'] = 'Login successful as assistant.';
            $response['redirect_url'] = 'dashboard/assistant/index.php'; // Will create this later
            echo json_encode($response);
            exit();
        }
    }
    $stmt->close();

    // Try to authenticate as Admin
    $stmt = $conn->prepare("SELECT AdminID AS user_id, Email, Password FROM AdminTBL WHERE Email = ?");
    if (!$stmt) {
        $response['message'] = 'Database query preparation failed: ' . $conn->error;
        echo json_encode($response);
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = 'admin';
            $response['success'] = true;
            $response['message'] = 'Login successful as admin.';
            $response['redirect_url'] = 'dashboard/admin/index.php'; // Will create this later
            echo json_encode($response);
            exit();
        }
    }
    $stmt->close();

    // If no user found or password incorrect
    $response['message'] = 'Invalid email or password.';
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
$conn->close();
?>
