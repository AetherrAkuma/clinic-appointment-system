<?php
session_start(); // Always start the session at the very beginning
require_once '../includes/db_connection.php'; // Notice the '../' to go up one directory from 'admin/'
require_once '../includes/config.php';   // Notice the '../' to go up one directory from 'admin/'

// --- Security Check ---
// This is crucial! Redirects to login if user is not logged in or is not an admin.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
// --- End Security Check ---

$admin_username = $_SESSION['username']; // Get the logged-in admin's username

// --- Fetching Dashboard Statistics ---
$total_patients = 0;
$total_assistants = 0;
$total_doctors = 0;
$total_appointments_scheduled = 0;
$total_appointments_completed = 0;
$total_appointments_canceled = 0;

// Get total patients count
$result_patients = $conn->query("SELECT COUNT(*) AS total FROM patients");
if ($result_patients) {
    $total_patients = $result_patients->fetch_assoc()['total'];
}

// Get total assistants count
$result_assistants = $conn->query("SELECT COUNT(*) AS total FROM assistants");
if ($result_assistants) {
    $total_assistants = $result_assistants->fetch_assoc()['total'];
}

// Get total doctors count
$result_doctors = $conn->query("SELECT COUNT(*) AS total FROM doctors");
if ($result_doctors) {
    $total_doctors = $result_doctors->fetch_assoc()['total'];
}

// Get appointment counts by status
// We group by status to get counts for 'Scheduled', 'Completed', 'Canceled' in one query
$result_appointments = $conn->query("SELECT status, COUNT(*) AS count FROM appointments GROUP BY status");
if ($result_appointments) {
    while ($row = $result_appointments->fetch_assoc()) {
        if ($row['status'] == 'Scheduled') {
            $total_appointments_scheduled = $row['count'];
        } elseif ($row['status'] == 'Completed') {
            $total_appointments_completed = $row['count'];
        } elseif ($row['status'] == 'Canceled') {
            $total_appointments_canceled = $row['count'];
        }
    }
}

$conn->close(); // Close the database connection once all queries are done
?>

// Hanggang dito lang ang code. The HTML part will be below this PHP code.

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Clinic Appointment System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5; /* Light background for the page */
        }
        .navbar {
            background-color: #2c3e50; /* Dark blue-grey header */
            padding: 10px 20px;
        }
        .navbar-brand, .nav-link {
            color: #ecf0f1 !important; /* Light text for navbar */
        }
        .navbar-brand:hover, .nav-link:hover {
            color: #fff !important;
        }
        .sidebar {
            height: 100vh; /* Full height sidebar */
            width: 250px;
            position: fixed; /* Fixed position for scrolling content */
            top: 0;
            left: 0;
            background-color: #34495e; /* Slightly lighter dark blue-grey */
            padding-top: 66px; /* Space for fixed navbar */
            color: #ecf0f1;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .list-group-item {
            background-color: transparent;
            color: #ecf0f1;
            padding: 15px 20px;
            border: none;
            border-left: 5px solid transparent;
            transition: background-color 0.3s, border-left-color 0.3s;
        }
        .sidebar .list-group-item:hover {
            background-color: #2c3e50;
            border-left-color: #1abc9c; /* Turquoise on hover */
            color: #fff;
        }
        .sidebar .list-group-item.active {
            background-color: #1abc9c; /* Turquoise for active link */
            color: #fff;
            font-weight: bold;
            border-left-color: #fff;
        }
        .sidebar .list-group-item i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px; /* Offset for the sidebar */
            padding: 30px;
            padding-top: 80px; /* Space for fixed navbar */
        }
        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 25px;
            text-align: center;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            height: 100%; /* Ensures cards in a row have equal height */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .stat-card .icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .stat-card .value {
            font-size: 2.8rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1; /* Adjust line height for better spacing */
        }
        .stat-card .label {
            color: #7f8c8d;
            font-size: 1rem;
            margin-top: 5px;
        }
        /* Specific colors for different card types */
        .stat-card.patients .icon { color: #2ecc71; /* Emerald green */ }
        .stat-card.assistants .icon { color: #f39c12; /* Orange */ }
        .stat-card.doctors .icon { color: #e74c3c; /* Alizarin red */ }
        .stat-card.scheduled .icon { color: #3498db; /* Peter River blue */ }
        .stat-card.completed .icon { color: #27ae60; /* Nephritis green */ }
        .stat-card.canceled .icon { color: #c0392b; /* Pomegranate red */ }

        .welcome-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 30px;
            margin-top: 30px;
        }
        .welcome-card h4 {
            color: #34495e;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>admin/dashboard.php">
            <i class="fas fa-clinic-medical"></i> Clinic Admin
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="nav-link">Welcome, **<?php echo htmlspecialchars($admin_username); ?>**</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="sidebar">
        <div class="list-group list-group-flush">
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>admin/manage_users.php" class="list-group-item list-group-item-action">
                <i class="fas fa-users-cog"></i> Manage Users
            </a>
            <a href="<?php echo BASE_URL; ?>admin/manage_doctors.php" class="list-group-item list-group-item-action">
                <i class="fas fa-user-md"></i> Manage Doctors
            </a>
            <a href="<?php echo BASE_URL; ?>admin/manage_rooms.php" class="list-group-item list-group-item-action">
                <i class="fas fa-door-open"></i> Manage Rooms
            </a>
            <a href="<?php echo BASE_URL; ?>admin/view_appointments.php" class="list-group-item list-group-item-action">
                <i class="fas fa-calendar-alt"></i> View Appointments
            </a>
            <a href="<?php echo BASE_URL; ?>admin/reports.php" class="list-group-item list-group-item-action">
                <i class="fas fa-chart-line"></i> Reports
            </a>
            <a href="<?php echo BASE_URL; ?>logout.php" class="list-group-item list-group-item-action">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4 text-dark">Admin Dashboard</h1>

            <div class="row dashboard-stats">
                <div class="col-md-4">
                    <div class="stat-card patients">
                        <div class="icon"><i class="fas fa-user-injured"></i></div>
                        <div class="value"><?php echo $total_patients; ?></div>
                        <div class="label">Total Patients</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card assistants">
                        <div class="icon"><i class="fas fa-user-nurse"></i></div>
                        <div class="value"><?php echo $total_assistants; ?></div>
                        <div class="label">Total Assistants</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card doctors">
                        <div class="icon"><i class="fas fa-stethoscope"></i></div>
                        <div class="value"><?php echo $total_doctors; ?></div>
                        <div class="label">Total Doctors</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stat-card scheduled">
                        <div class="icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="value"><?php echo $total_appointments_scheduled; ?></div>
                        <div class="label">Scheduled Appointments</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card completed">
                        <div class="icon"><i class="fas fa-calendar-plus"></i></div>
                        <div class="value"><?php echo $total_appointments_completed; ?></div>
                        <div class="label">Completed Appointments</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card canceled">
                        <div class="icon"><i class="fas fa-calendar-times"></i></div>
                        <div class="value"><?php echo $total_appointments_canceled; ?></div>
                        <div class="label">Canceled Appointments</div>
                    </div>
                </div>
            </div>

            <div class="welcome-card">
                <h4>Welcome to the Admin Panel!</h4>
                <p class="text-secondary mb-0">This dashboard provides a quick overview of key metrics. Use the sidebar to navigate and manage different aspects of the clinic system.</p>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>