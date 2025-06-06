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

// Fetch the doctor ID linked to this assistant
$stmt_doctor_link = $conn->prepare("SELECT doctor_id FROM assistants WHERE assistant_id = ?");
$stmt_doctor_link->bind_param("i", $assistant_id);
$stmt_doctor_link->execute();
$result_doctor_link = $stmt_doctor_link->get_result();

if ($result_doctor_link->num_rows > 0) {
    $row = $result_doctor_link->fetch_assoc();
    $doctor_assigned_id = $row['doctor_id'];

    // If a doctor is assigned, fetch their name
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
    }
}
$stmt_doctor_link->close();

$appointments = [];
$message = '';
$message_type = '';

if ($doctor_assigned_id) {
    // Fetch all appointments for the assigned doctor
    $stmt_appointments = $conn->prepare("
        SELECT
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            p.first_name AS patient_first_name,
            p.last_name AS patient_last_name,
            r.room_number
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        LEFT JOIN rooms r ON a.room_id = r.room_id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt_appointments->bind_param("i", $doctor_assigned_id);
    $stmt_appointments->execute();
    $result_appointments = $stmt_appointments->get_result();

    while ($row = $result_appointments->fetch_assoc()) {
        $appointments[] = $row;
    }
    $stmt_appointments->close();
} else {
    $message = "You are not assigned to a doctor. Please contact an administrator.";
    $message_type = "danger";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's Schedule - Assistant Panel</title>
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
        .table thead th { background-color: #f8f9fa; color: #34495e; border-bottom: 2px solid #dee2e6; }
        .table tbody tr:hover { background-color: #f2f2f2; }
        .status-badge {
            padding: .35em .65em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
            color: #fff;
        }
        .status-Scheduled { background-color: #007bff; }
        .status-Completed { background-color: #28a745; }
        .status-Canceled { background-color: #dc3545; }
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
            <a href="<?php echo BASE_URL; ?>assistants/manage_doctor_schedule.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-calendar-alt"></i> Doctor's Schedule
            </a>
            <a href="<?php echo BASE_URL; ?>assistants/create_appointment.php" class="list-group-item list-group-item-action">
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
            <h1 class="mb-4 text-dark">Doctor's Schedule - Dr. <?php echo htmlspecialchars($doctor_name); ?></h1>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card-custom">
                <h4>All Appointments</h4>
                <?php if ($doctor_assigned_id && !empty($appointments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appointment['patient_first_name'] . " " . $appointment['patient_last_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['room_number'] ?: 'N/A'); ?></td>
                                    <td><span class="status-badge status-<?php echo htmlspecialchars($appointment['status']); ?>"><?php echo htmlspecialchars($appointment['status']); ?></span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i></a>
                                        <a href="#" class="btn btn-sm btn-warning" title="Edit Appointment"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($doctor_assigned_id): ?>
                    <p class="text-info">No appointments found for Dr. <?php echo htmlspecialchars($doctor_name); ?>.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>