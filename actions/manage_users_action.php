<?php
// actions/manage_users_action.php
session_start();
require_once '../config/db_connection.php'; // Include the database connection

// Ensure only admins can access most actions on this page for managing users.
// For profile updates, we need to ensure the logged-in user is updating their own profile.
// The specific role checks are done within each action block.

$redirect_url_manage_users = '../dashboard/admin/manage_users.php'; // Default redirect for success/unhandled

$action = $_POST['action'] ?? $_GET['action'] ?? null; // Get action from POST or GET
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method === 'POST') {
    // Handle POST requests (add, edit, profile_update, or change_password)
    if ($action === 'add') {
        // Only allow admin to add users
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['user_management_message'] = 'Unauthorized access.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: ../index.php");
            exit();
        }

        $role = htmlspecialchars(trim($_POST['role'] ?? ''));
        $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
        $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? ''; // Plain password, will be hashed

        if (empty($role) || empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            $_SESSION['user_management_message'] = 'First Name, Last Name, Email, Password, and Role are required.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: ../dashboard/admin/add_user.php");
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['user_management_message'] = 'Invalid email format.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: ../dashboard/admin/add_user.php");
            exit();
        }

        // Check if email already exists in any table
        $email_exists = false;
        $check_stmts = [
            'PatientTBL' => 'SELECT COUNT(*) FROM PatientTBL WHERE Email = ?',
            'AssistantTBL' => 'SELECT COUNT(*) FROM AssistantTBL WHERE Email = ?',
            'AdminTBL' => 'SELECT COUNT(*) FROM AdminTBL WHERE Email = ?'
        ];

        foreach ($check_stmts as $tbl => $sql) {
            $check_stmt = $conn->prepare($sql);
            if ($check_stmt) {
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $row = $check_result->fetch_row();
                if ($row[0] > 0) {
                    $email_exists = true;
                    break;
                }
                $check_stmt->close();
            } else {
                error_log("Failed to prepare email existence check for " . $tbl . ": " . $conn->error);
            }
        }

        if ($email_exists) {
            $_SESSION['user_management_message'] = 'Error: Email already exists. Please use a different email.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: ../dashboard/admin/add_user.php");
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_success = false;
        $insert_message = 'Error adding user.';
        $redirect_on_error = '../dashboard/admin/add_user.php'; // Specific redirect for 'add' action errors

        switch ($role) {
            case 'patient':
                $age = filter_var($_POST['age'] ?? '', FILTER_VALIDATE_INT);
                $gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
                $address = htmlspecialchars(trim($_POST['address'] ?? ''));
                $contactNumber = htmlspecialchars(trim($_POST['contactNumber'] ?? ''));
                $medicalHistory = htmlspecialchars(trim($_POST['medicalHistory'] ?? ''));

                $stmt = $conn->prepare("INSERT INTO PatientTBL (FirstName, LastName, Email, Password, Age, Gender, Address, ContactNumber, MedicalHistory) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssssissss", $firstName, $lastName, $email, $hashed_password, $age, $gender, $address, $contactNumber, $medicalHistory);
                    if ($stmt->execute()) {
                        $insert_success = true;
                        $insert_message = 'Patient account created successfully!';
                    } else {
                        $insert_message = 'Error creating patient account: ' . $stmt->error;
                        error_log("Error creating patient: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    $insert_message = 'Database query preparation failed for patient: ' . $conn->error;
                    error_log("Failed to prepare patient insert: " . $conn->error);
                }
                break;
            case 'assistant':
                $specialization = htmlspecialchars(trim($_POST['specialization'] ?? ''));
                $sessionFee = filter_var($_POST['sessionFee'] ?? '', FILTER_VALIDATE_FLOAT);

                $stmt = $conn->prepare("INSERT INTO AssistantTBL (FirstName, LastName, Email, Password, Specialization, SessionFee) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sssssd", $firstName, $lastName, $email, $hashed_password, $specialization, $sessionFee);
                    if ($stmt->execute()) {
                        $insert_success = true;
                        $insert_message = 'Assistant account created successfully!';
                    } else {
                        $insert_message = 'Error creating assistant account: ' . $stmt->error;
                        error_log("Error creating assistant: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    $insert_message = 'Database query preparation failed for assistant: ' . $conn->error;
                    error_log("Failed to prepare assistant insert: " . $conn->error);
                }
                break;
            case 'admin':
                $stmt = $conn->prepare("INSERT INTO AdminTBL (FirstName, LastName, Email, Password) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashed_password);
                    if ($stmt->execute()) {
                        $insert_success = true;
                        $insert_message = 'Admin account created successfully!';
                    } else {
                        $insert_message = 'Error creating admin account: ' . $stmt->error;
                        error_log("Error creating admin: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    $insert_message = 'Database query preparation failed for admin: ' . $conn->error;
                    error_log("Failed to prepare admin insert: " . $conn->error);
                }
                break;
            default:
                $_SESSION['user_management_message'] = 'Invalid user role selected.';
                $_SESSION['user_management_message_type'] = 'error';
                $conn->close();
                header("Location: ../dashboard/admin/add_user.php");
                exit();
        }

        $_SESSION['user_management_message'] = $insert_message;
        $_SESSION['user_management_message_type'] = $insert_success ? 'success' : 'error';
        $conn->close();
        header("Location: " . ($insert_success ? $redirect_url_manage_users : $redirect_on_error));
        exit();

    } elseif ($action === 'edit') {
        // Only allow admin to edit other users
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['user_management_message'] = 'Unauthorized access.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: ../index.php");
            exit();
        }

        // --- Handle Edit User Action ---
        $userId = filter_var($_POST['userId'] ?? '', FILTER_VALIDATE_INT);
        $role = htmlspecialchars(trim($_POST['role'] ?? ''));
        $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
        $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));

        if (empty($userId) || empty($role) || empty($firstName) || empty($lastName)) {
            $_SESSION['user_management_message'] = 'Missing required fields for user update.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: ../dashboard/admin/edit_user.php?id=" . $userId . "&role=" . $role); // Redirect back to edit page
            exit();
        }

        $update_success = false;
        $update_message = 'Error updating user.';
        $stmt = null; // Initialize statement variable
        $redirect_on_error = '../dashboard/admin/edit_user.php?id=' . $userId . '&role=' . $role;

        switch ($role) {
            case 'patient':
                $age = filter_var($_POST['age'] ?? '', FILTER_VALIDATE_INT);
                $gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
                $address = htmlspecialchars(trim($_POST['address'] ?? ''));
                $contactNumber = htmlspecialchars(trim($_POST['contactNumber'] ?? ''));
                $medicalHistory = htmlspecialchars(trim($_POST['medicalHistory'] ?? ''));

                $stmt = $conn->prepare("UPDATE PatientTBL SET FirstName = ?, LastName = ?, Age = ?, Gender = ?, Address = ?, ContactNumber = ?, MedicalHistory = ? WHERE PatientID = ?");
                if ($stmt) {
                    $stmt->bind_param("ssissssi", $firstName, $lastName, $age, $gender, $address, $contactNumber, $medicalHistory, $userId);
                }
                break;
            case 'assistant':
                $specialization = htmlspecialchars(trim($_POST['specialization'] ?? ''));
                $sessionFee = filter_var($_POST['sessionFee'] ?? '', FILTER_VALIDATE_FLOAT);

                $stmt = $conn->prepare("UPDATE AssistantTBL SET FirstName = ?, LastName = ?, Specialization = ?, SessionFee = ? WHERE AssistantID = ?");
                if ($stmt) {
                    // CORRECTED: 's' for specialization (string), 'd' for sessionFee (double)
                    $stmt->bind_param("sssid", $firstName, $lastName, $specialization, $sessionFee, $userId);
                }
                break;
            case 'admin':
                $stmt = $conn->prepare("UPDATE AdminTBL SET FirstName = ?, LastName = ? WHERE AdminID = ?");
                if ($stmt) {
                    $stmt->bind_param("ssi", $firstName, $lastName, $userId);
                }
                break;
            default:
                $_SESSION['user_management_message'] = 'Invalid user role selected for update.';
                $_SESSION['user_management_message_type'] = 'error';
                $conn->close();
                header("Location: " . $redirect_on_error);
                exit();
        }

        if ($stmt) {
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $update_success = true;
                    $update_message = ucfirst($role) . ' profile updated successfully!';
                } else {
                    $update_message = 'No changes made to ' . $role . ' profile or user not found.';
                }
            } else {
                $update_message = 'Error updating ' . $role . ' profile: ' . $stmt->error;
                error_log("Error updating user profile (admin edit): " . $stmt->error);
            }
            $stmt->close();
        } else {
            $update_message = 'Database query preparation failed for update: ' . $conn->error;
            error_log("Failed to prepare update statement for user: " . $conn->error);
        }

        $_SESSION['user_management_message'] = $update_message;
        $_SESSION['user_management_message_type'] = $update_success ? 'success' : 'error';
        $conn->close();
        header("Location: " . $redirect_on_error); // Redirect back to edit page with message
        exit();

    } elseif ($action === 'profile_update') {
        // --- Handle Admin Self-Profile Update ---
        $userId = filter_var($_POST['userId'] ?? '', FILTER_VALIDATE_INT);
        $role = htmlspecialchars(trim($_POST['role'] ?? '')); // Should be 'admin'
        $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
        $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));

        // Basic validation for profile update
        if (empty($userId) || $role !== 'admin' || $userId !== $_SESSION['user_id'] || empty($firstName) || empty($lastName)) {
            $_SESSION['user_management_message'] = 'Invalid request or unauthorized profile update.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: ../dashboard/admin/profile.php");
            exit();
        }

        $update_success = false;
        $update_message = 'Error updating profile.';
        $redirect_to_profile = '../dashboard/admin/profile.php';

        $stmt = $conn->prepare("UPDATE AdminTBL SET FirstName = ?, LastName = ? WHERE AdminID = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $firstName, $lastName, $userId);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $update_success = true;
                    $update_message = 'Your profile has been updated successfully!';
                } else {
                    $update_message = 'No changes were made to your profile.';
                }
            } else {
                $update_message = 'Error updating your profile: ' . $stmt->error;
                error_log("Error updating admin's own profile: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $update_message = 'Database query preparation failed for profile update: ' . $conn->error;
            error_log("Failed to prepare admin profile update: " . $conn->error);
        }

        $_SESSION['user_management_message'] = $update_message;
        $_SESSION['user_management_message_type'] = $update_success ? 'success' : 'error';
        $conn->close();
        header("Location: " . $redirect_to_profile);
        exit();

    } elseif ($action === 'change_password') {
        // --- Handle Password Change Action ---
        $userId = filter_var($_POST['userId'] ?? '', FILTER_VALIDATE_INT);
        $role = htmlspecialchars(trim($_POST['role'] ?? ''));
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmNewPassword = $_POST['confirmNewPassword'] ?? '';
        $isSelfChange = filter_var($_POST['isSelfChange'] ?? '', FILTER_VALIDATE_BOOLEAN);

        // Determine redirect URL based on whether it's a self-change or admin editing another user
        $redirect_after_password_change = $redirect_url_manage_users; // Default to manage_users
        if ($isSelfChange) {
            $redirect_after_password_change = '../dashboard/admin/profile.php';
        } else {
            // If admin is changing another user's password, redirect back to their edit page
            $redirect_after_password_change = '../dashboard/admin/edit_user.php?id=' . $userId . '&role=' . $role;
        }

        // Authorization check
        if ($isSelfChange) {
            // If self-change, ensure the logged-in user matches the userId and role
            if ($userId !== $_SESSION['user_id'] || $role !== $_SESSION['user_role']) {
                $_SESSION['user_management_message'] = 'Unauthorized attempt to change another user\'s profile password.';
                $_SESSION['user_management_message_type'] = 'error';
                $conn->close();
                header("Location: " . $redirect_after_password_change);
                exit();
            }
        } else {
            // If admin is changing another user's password, ensure the logged-in user is an admin
            if ($_SESSION['user_role'] !== 'admin') {
                $_SESSION['user_management_message'] = 'Unauthorized access to change user password.';
                $_SESSION['user_management_message_type'] = 'error';
                $conn->close();
                header("Location: ../index.php");
                exit();
            }
        }

        // Validation for new password
        if (empty($newPassword) || empty($confirmNewPassword)) {
            $_SESSION['user_management_message'] = 'New password and confirmation are required.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: " . $redirect_after_password_change);
            exit();
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['user_management_message'] = 'New password must be at least 8 characters long.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: " . $redirect_after_password_change);
            exit();
        }

        if ($newPassword !== $confirmNewPassword) {
            $_SESSION['user_management_message'] = 'New password and confirmation do not match.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: " . $redirect_after_password_change);
            exit();
        }

        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
        $update_success = false;
        $update_message = 'Error changing password.';
        $table = '';
        $id_column = '';

        switch ($role) {
            case 'patient':
                $table = 'PatientTBL';
                $id_column = 'PatientID';
                break;
            case 'assistant':
                $table = 'AssistantTBL';
                $id_column = 'AssistantID';
                break;
            case 'admin':
                $table = 'AdminTBL';
                $id_column = 'AdminID';
                break;
            default:
                $_SESSION['user_management_message'] = 'Invalid user role for password change.';
                $_SESSION['user_management_message_type'] = 'error';
                $conn->close();
                header("Location: " . $redirect_after_password_change);
                exit();
        }

        $stmt = $conn->prepare("UPDATE " . $table . " SET Password = ? WHERE " . $id_column . " = ?");
        if ($stmt) {
            $stmt->bind_param("si", $hashed_password, $userId);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $update_success = true;
                    $update_message = 'Password changed successfully!';
                } else {
                    $update_message = 'Failed to change password: User not found or no changes made.';
                }
            } else {
                $update_message = 'Database error changing password: ' . $stmt->error;
                error_log("Database error changing password for " . $role . " ID " . $userId . ": " . $stmt->error);
            }
            $stmt->close();
        } else {
            $update_message = 'Database query preparation failed for password change: ' . $conn->error;
            error_log("Failed to prepare password change statement: " . $conn->error);
        }

        $_SESSION['user_management_message'] = $update_message;
        $_SESSION['user_management_message_type'] = $update_success ? 'success' : 'error';
        $conn->close();

        // If it's a self-password change and successful, destroy session for re-login
        if ($isSelfChange && $update_success) {
            session_unset();
            session_destroy();
            // Redirect to login page for re-authentication
            header("Location: ../index.php?message=Password changed. Please log in again.&type=success");
            exit();
        } else {
            header("Location: " . $redirect_after_password_change);
            exit();
        }

    } else {
        // Fallback for POST request with unknown action
        $_SESSION['user_management_message'] = 'Invalid POST action specified.';
        $_SESSION['user_management_message_type'] = 'error';
        $conn->close();
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? $redirect_url_manage_users)); // Try to redirect to referring page
        exit();
    }

} elseif ($request_method === 'GET') {
    // Handle GET requests (delete)
    if ($action === 'delete') {
        // Only allow admin to delete users
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['user_management_message'] = 'Unauthorized access.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: ../index.php");
            exit();
        }

        $user_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
        $user_role = htmlspecialchars(trim($_GET['role'] ?? ''));

        if (empty($user_id) || empty($user_role)) {
            $_SESSION['user_management_message'] = 'Invalid request: Missing user ID or role.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: " . $redirect_url_manage_users);
            exit();
        }

        // Prevent an admin from deleting their own account
        if ($user_role === 'admin' && $user_id === $_SESSION['user_id']) {
            $_SESSION['user_management_message'] = 'Error: You cannot delete your own admin account.';
            $_SESSION['user_management_message_type'] = 'error';
            $conn->close();
            header("Location: " . $redirect_url_manage_users);
            exit();
        }

        $table = '';
        $id_column = '';

        switch ($user_role) {
            case 'patient':
                $table = 'PatientTBL';
                $id_column = 'PatientID';
                break;
            case 'assistant':
                $table = 'AssistantTBL';
                $id_column = 'AssistantID';
                break;
            case 'admin':
                $table = 'AdminTBL';
                $id_column = 'AdminID';
                break;
            default:
                $_SESSION['user_management_message'] = 'Invalid user role specified for deletion.';
                $_SESSION['user_management_message_type'] = 'error';
                $conn->close();
                header("Location: " . $redirect_url_manage_users);
                exit();
        }

        $stmt = $conn->prepare("DELETE FROM " . $table . " WHERE " . $id_column . " = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $_SESSION['user_management_message'] = ucfirst($user_role) . ' account deleted successfully.';
                    $_SESSION['user_management_message_type'] = 'success';
                } else {
                    $_SESSION['user_management_message'] = ucfirst($user_role) . ' account not found or already deleted.';
                    $_SESSION['user_management_message_type'] = 'error';
                }
            } else {
                $_SESSION['user_management_message'] = 'Error deleting ' . $user_role . ' account: ' . $stmt->error;
                $_SESSION['user_management_message_type'] = 'error';
                error_log("Error deleting user: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $_SESSION['user_management_message'] = 'Database query preparation failed for deletion: ' . $conn->error;
            $_SESSION['user_management_message_type'] = 'error';
            error_log("Failed to prepare statement for user deletion: " . $conn->error);
        }
        $conn->close();
        header("Location: " . $redirect_url_manage_users);
        exit();

    } else {
        // Fallback for GET request with unknown action
        $_SESSION['user_management_message'] = 'Invalid GET action specified.';
        $_SESSION['user_management_message_type'] = 'error';
        $conn->close();
        header("Location: " . $redirect_url_manage_users);
        exit();
    }

} else {
    // Neither POST nor GET, or direct access without action
    $_SESSION['user_management_message'] = 'Invalid request method.';
    $_SESSION['user_management_message_type'] = 'error';
    $conn->close();
    header("Location: " . $redirect_url_manage_users);
    exit();
}
?>
