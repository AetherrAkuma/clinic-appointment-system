<?php
session_start(); // Start session to allow redirection after successful registration
require_once 'includes/db_connection.php';
require_once 'includes/config.php';

$message = ''; // Initialize message variable for feedback

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize user input for account creation
    $username = $conn->real_escape_string($_POST['username']);
    // Hash the password for security. NEVER store plain text passwords.
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);
    $role = 'patient'; // New registrations are always for patients

    // Collect and sanitize patient's personal details
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $date_of_birth = $conn->real_escape_string($_POST['date_of_birth']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $email = $conn->real_escape_string($_POST['email']);

    // Collect and sanitize medical history details
    $allergies = $conn->real_escape_string($_POST['allergies']);
    $past_illnesses = $conn->real_escape_string($_POST['past_illnesses']);
    $medications = $conn->real_escape_string($_POST['medications']);
    $surgical_history = $conn->real_escape_string($_POST['surgical_history']);
    $family_medical_history = $conn->real_escape_string($_POST['family_medical_history']);
    $notes = $conn->real_escape_string($_POST['notes']);

    // Start a database transaction. This ensures that either ALL inserts succeed, or NONE do.
    // This is crucial for data integrity, so a user account, patient record, and medical history
    // are created together as a single, atomic operation.
    $conn->begin_transaction();

    try {
        // 1. Insert into the `users` table first
        $stmt_user = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt_user->bind_param("sss", $username, $password, $role);
        $stmt_user->execute();

        // Get the ID of the newly inserted user. This ID will be used as patient_id.
        $patient_id = $conn->insert_id;

        // 2. Insert into the `patients` table
        $stmt_patient = $conn->prepare("INSERT INTO patients (patient_id, first_name, last_name, date_of_birth, gender, address, phone_number, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_patient->bind_param("isssssss", $patient_id, $first_name, $last_name, $date_of_birth, $gender, $address, $phone_number, $email);
        $stmt_patient->execute();

        // 3. Insert into the `medical_history` table
        $stmt_history = $conn->prepare("INSERT INTO medical_history (patient_id, allergies, past_illnesses, medications, surgical_history, family_medical_history, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_history->bind_param("issssss", $patient_id, $allergies, $past_illnesses, $medications, $surgical_history, $family_medical_history, $notes);
        $stmt_history->execute();

        // If all inserts are successful, commit the transaction
        $conn->commit();
        $message = "Registration successful! You can now log in.";
        // Redirect to login page with a success message
        header("Location: " . BASE_URL . "login.php?registration=success");
        exit();

    } catch (mysqli_sql_exception $e) {
        // If any insert fails, rollback the transaction to undo all changes
        $conn->rollback();
        $message = "Registration failed: " . $e->getMessage(); // Display the specific error
    } finally {
        // Always close prepared statements if they were created
        if (isset($stmt_user)) $stmt_user->close();
        if (isset($stmt_patient)) $stmt_patient->close();
        if (isset($stmt_history)) $stmt_history->close();
    }
}
$conn->close(); // Close the database connection
?>

// mga boss hanggang dito lang di nyo gagalawin. sa baba neto yun lang gagalawin nyo
// This is the HTML form for patient registration


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 800px; margin: 20px auto; }
        h2 { text-align: center; color: #333; }
        h3 { color: #555; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="password"], input[type="date"], input[type="email"], input[type="tel"], select, textarea {
            width: calc(100% - 22px); /* Adjust for padding and border */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding/border in element's total width/height */
            font-size: 16px;
        }
        textarea { resize: vertical; min-height: 80px; }
        button {
            width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 18px; font-weight: bold;
            transition: background-color 0.3s ease;
        }
        button:hover { background-color: #0056b3; }
        .message { text-align: center; margin-bottom: 20px; padding: 10px; border-radius: 4px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .login-link { text-align: center; margin-top: 20px; font-size: 15px; }
        .login-link a { color: #007bff; text-decoration: none; font-weight: bold; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Patient Registration</h2>
        <?php if (!empty($message)): ?>
            <p class="message <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <h3>Account Information</h3>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <h3>Personal Information</h3>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address">
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input type="tel" id="phone_number" name="phone_number">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email">
            </div>

            <h3>Medical History</h3>
            <div class="form-group">
                <label for="allergies">Allergies:</label>
                <textarea id="allergies" name="allergies"></textarea>
            </div>
            <div class="form-group">
                <label for="past_illnesses">Past Illnesses:</label>
                <textarea id="past_illnesses" name="past_illnesses"></textarea>
            </div>
            <div class="form-group">
                <label for="medications">Current Medications:</label>
                <textarea id="medications" name="medications"></textarea>
            </div>
            <div class="form-group">
                <label for="surgical_history">Surgical History:</label>
                <textarea id="surgical_history" name="surgical_history"></textarea>
            </div>
            <div class="form-group">
                <label for="family_medical_history">Family Medical History:</label>
                <textarea id="family_medical_history" name="family_medical_history"></textarea>
            </div>
            <div class="form-group">
                <label for="notes">Additional Medical Notes:</label>
                <textarea id="notes" name="notes"></textarea>
            </div>

            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="<?php echo BASE_URL; ?>login.php">Login here</a>.
        </div>
    </div>
</body>
</html>