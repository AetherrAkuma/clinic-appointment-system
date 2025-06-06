<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/config.php';

// --- Security Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'assistant') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
// --- End Security Check ---

$assistant_id = $_SESSION['user_id'];
$doctor_assigned_id = null;
$doctor_name = "N/A";
$message = '';
$message_type = '';

// Fetch the doctor ID linked to this assistant
$stmt_doctor_link = $conn->prepare("SELECT doctor_id FROM assistants WHERE assistant_id = ?");
$stmt_doctor_link->bind_param("i", $assistant_id);
$stmt_doctor_link->execute();
$result_doctor_link = $stmt_doctor_link->get_result();

if ($result_doctor_link->num_rows > 0) {
    $row = $result_doctor_link->fetch_assoc();
    $doctor_assigned_id = $row['doctor_id'];

    if ($doctor_assigned_id) {
        $stmt_doctor_name = $conn->prepare("SELECT first_name, last_name FROM doctors WHERE doctor_id = ?");
        $stmt_doctor_name->bind_param("i", $doctor_assigned_id);
        $stmt_doctor_name->execute();
        $result_doctor_name = $stmt_doctor_name->get_result();
        if ($result_doctor_name->num_rows > 0) {
            $doctor_row = $result_doctor_name->fetch_assoc();
            $doctor_name = $doctor_row['first_name'] . " " . $doctor_row['last_name'];
        }
        $stmt_doctor_name->close();
    } else {
        $message = "You are not assigned to a doctor. Please contact an administrator.";
        $message_type = "danger";
    }
} else {
    $message = "Assistant record not found. Please contact an administrator.";
    $message_type = "danger";
}
$stmt_doctor_link->close();


// Handle Appointment Creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_appointment']) && $doctor_assigned_id) {
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $appointment_time = $conn->real_escape_string($_POST['appointment_time']);
    $notes = $conn->real_escape_string($_POST['notes']);

    // --- Automatic Room Assignment Logic ---
    $assigned_room_id = null;

    // Find available rooms that are not in 'Maintenance'
    $stmt_available_rooms = $conn->prepare("SELECT room_id FROM rooms WHERE status = 'Available'");
    $stmt_available_rooms->execute();
    $available_rooms_result = $stmt_available_rooms->get_result();
    $available_room_ids = [];
    while($row = $available_rooms_result->fetch_assoc()) {
        $available_room_ids[] = $row['room_id'];
    }
    $stmt_available_rooms->close();

    if (empty($available_room_ids)) {
        $message = "No available rooms for the appointment. Please check room status.";
        $message_type = "danger";
    } else {
        // Check each available room for conflicts
        foreach ($available_room_ids as $room_id) {
            $stmt_check_conflict = $conn->prepare("
                SELECT appointment_id FROM appointments
                WHERE room_id = ? AND appointment_date = ? AND appointment_time = ? AND status = 'Scheduled'
            ");
            $stmt_check_conflict->bind_param("iss", $room_id, $appointment_date, $appointment_time);
            $stmt_check_conflict->execute();
            $conflict_result = $stmt_check_conflict->get_result();

            if ($conflict_result->num_rows == 0) {
                // This room is free at the requested time! Assign it.
                $assigned_room_id = $room_id;
                break; // Found a room, no need to check others
            }
            $stmt_check_conflict->close(); // Close inside loop if not breaking
        }
        if (isset($stmt_check_conflict)) $stmt_check_conflict->close(); // Close if loop completed without break
    }


    if ($assigned_room_id !== null) {
        // Proceed with appointment creation now that a room is assigned
        $stmt_insert_appointment = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, room_id, status, notes) VALUES (?, ?, ?, ?, ?, 'Scheduled', ?)");
        $stmt_insert_appointment->bind_param("iisiss", $patient_id, $doctor_assigned_id, $appointment_date, $appointment_time, $assigned_room_id, $notes);

        if ($stmt_insert_appointment->execute()) {
            $message = "Appointment created successfully for Dr. " . htmlspecialchars($doctor_name) . " with room ID: " . htmlspecialchars($assigned_room_id) . ".";
            $message_type = "success";
            // Optional: Clear form fields after successful submission
            $_POST = array(); // Clear POST data to reset form
        } else {
            $message = "Error creating appointment: " . $stmt_insert_appointment->error;
            $message_type = "danger";
        }
        $stmt_insert_appointment->close();
    } elseif (empty($message)) { // Only set this if no other message (e.g., no available rooms) has been set
        $message = "No available room found for the selected date and time.";
        $message_type = "danger";
    }
}


// Fetch all patients for the dropdown list
$patients = [];
$stmt_patients = $conn->prepare("SELECT patient_id, first_name, last_name FROM patients ORDER BY last_name, first_name");
$stmt_patients->execute();
$result_patients = $stmt_patients->get_result();
while ($row = $result_patients->fetch_assoc()) {
    $patients[] = $row;
}
$stmt_patients->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Appointment - Assistant Panel</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; }
        .navbar { background-color: #5d6d7e; padding: 10px 20px; }
        .navbar-brand, .nav-link { color: #ecf0f1 !important; }
        .navbar-brand:hover, .nav-link:hover { color: #fff !important; }
        .sidebar {
            height: 100vh; width: 250px; position: fixed; top: 0; left: 0;
            background-color: #7f8c8d; padding-top: 66px; color: #ecf0f1; box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .list-group-item {
            background-color: transparent; color: #ecf0f1; padding: 15px 20px; border: none;
            border-left: 5px solid transparent; transition: background-color 0.3s, border-left-color 0.3s;
        }
        .sidebar .list-group-item:hover {
            background-color: #5d6d7e;
            border-left-color: #27ae60;
            color: #fff;
        }
        .sidebar .list-group-item.active {
            background-color: #27ae60;
            color: #fff; font-weight: bold; border-left-color: #fff;
        }
        .sidebar .list-group-item i { margin-right: 10px; }
        .main-content { margin-left: 250px; padding: 30px; padding-top: 80px; }
        .card-custom {
            background-color: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 30px; margin-bottom: 30px;
        }
        .form-control:focus {
            border-color: #27ae60;
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>assistants/dashboard.php">
            <i class="fas fa-user-nurse"></i> Assistant Panel
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="nav-link">Welcome, **<?php echo htmlspecialchars($_SESSION['username']); ?>**</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="sidebar">
        <div class="list-group list-group-flush">
            <a href="<?php echo BASE_URL; ?>assistants/dashboard.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>assistants/manage_doctor_schedule.php" class="list-group-item list-group-item-action">
                <i class="fas fa-calendar-alt"></i> Doctor's Schedule
            </a>
            <a href="<?php echo BASE_URL; ?>assistants/create_appointment.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-plus-circle"></i> Create Appointment
            </a>
            <a href="<?php echo BASE_URL; ?>assistants/view_appointments.php" class="list-group-item list-group-item-action">
                <i class="fas fa-clipboard-list"></i> View Appointments
            </a>
            <a href="<?php echo BASE_URL; ?>assistants/patient_medical_history.php" class="list-group-item list-group-item-action">
                <i class="fas fa-notes-medical"></i> Patient Medical History
            </a>
            <a href="<?php echo BASE_URL; ?>logout.php" class="list-group-item list-group-item-action">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4 text-dark">Create New Appointment</h1>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (!$doctor_assigned_id): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> You are not assigned to a doctor. Please contact an administrator to be assigned before creating appointments.
                </div>
            <?php else: ?>
                <div class="card-custom">
                    <h4>Book Appointment for Dr. <?php echo htmlspecialchars($doctor_name); ?></h4>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <div class="form-group">
                            <label for="patient_id">Select Patient:</label>
                            <select class="form-control" id="patient_id" name="patient_id" required>
                                <option value="">-- Select Patient --</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>">
                                        <?php echo htmlspecialchars($patient['first_name'] . " " . $patient['last_name'] . " (ID: " . $patient['patient_id'] . ")"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($patients)): ?>
                                <small class="form-text text-muted">No patients found. Please ask admin to register patients first.</small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="appointment_date">Appointment Date:</label>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="appointment_time">Appointment Time:</label>
                            <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes (Optional):</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>

                        <button type="submit" name="create_appointment" class="btn btn-success"><i class="fas fa-plus-circle"></i> Create Appointment</button>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>