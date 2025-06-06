<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/config.php';

// --- Security Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
// --- End Security Check ---

$message = '';
$message_type = ''; // success or danger

// Handle Add/Edit Doctor Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_doctor'])) {
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $specialization = $conn->real_escape_string($_POST['specialization']);

        $stmt = $conn->prepare("INSERT INTO doctors (first_name, last_name, specialization) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $first_name, $last_name, $specialization);

        if ($stmt->execute()) {
            $message = "Doctor '" . htmlspecialchars($first_name . " " . $last_name) . "' added successfully.";
            $message_type = "success";
        } else {
            $message = "Error adding doctor: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();

    } elseif (isset($_POST['edit_doctor'])) {
        $doctor_id = $conn->real_escape_string($_POST['doctor_id']);
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $specialization = $conn->real_escape_string($_POST['specialization']);

        $stmt = $conn->prepare("UPDATE doctors SET first_name = ?, last_name = ?, specialization = ? WHERE doctor_id = ?");
        $stmt->bind_param("sssi", $first_name, $last_name, $specialization, $doctor_id);

        if ($stmt->execute()) {
            $message = "Doctor '" . htmlspecialchars($first_name . " " . $last_name) . "' updated successfully.";
            $message_type = "success";
        } else {
            $message = "Error updating doctor: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

// Handle Delete Doctor
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $doctor_id = $conn->real_escape_string($_GET['id']);

    // Start transaction for deletion, especially if related data might be affected (like assistants)
    $conn->begin_transaction();
    try {
        // Option 1: Set doctor_id in assistants to NULL (as per FOREIGN KEY ON DELETE SET NULL)
        // This is handled by the FK constraint, but good to be aware.
        // You might want to explicitly update relevant tables if you didn't set ON DELETE SET NULL
        // For appointments, we set ON DELETE CASCADE, so appointments linked to this doctor will be deleted.
        // If you want to reassign them, you'd need more complex logic here.

        $stmt = $conn->prepare("DELETE FROM doctors WHERE doctor_id = ?");
        $stmt->bind_param("i", $doctor_id);

        if ($stmt->execute()) {
            $conn->commit();
            $message = "Doctor deleted successfully.";
            $message_type = "success";
        } else {
            throw new mysqli_sql_exception("Error deleting doctor: " . $stmt->error);
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $message = "Error deleting doctor: " . $e->getMessage();
        $message_type = "danger";
    }
}


// Fetch all doctors for display
$doctors = [];
$result = $conn->query("SELECT doctor_id, first_name, last_name, specialization FROM doctors ORDER BY last_name, first_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
} else {
    $message = "Error fetching doctors: " . $conn->error;
    $message_type = "danger";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors - Admin Panel</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; }
        .navbar { background-color: #2c3e50; padding: 10px 20px; }
        .navbar-brand, .nav-link { color: #ecf0f1 !important; }
        .navbar-brand:hover, .nav-link:hover { color: #fff !important; }
        .sidebar {
            height: 100vh; width: 250px; position: fixed; top: 0; left: 0;
            background-color: #34495e; padding-top: 66px; color: #ecf0f1; box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .list-group-item {
            background-color: transparent; color: #ecf0f1; padding: 15px 20px; border: none;
            border-left: 5px solid transparent; transition: background-color 0.3s, border-left-color 0.3s;
        }
        .sidebar .list-group-item:hover {
            background-color: #2c3e50;
            border-left-color: #1abc9c;
            color: #fff;
        }
        .sidebar .list-group-item.active {
            background-color: #1abc9c;
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
        .form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
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
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>admin/manage_users.php" class="list-group-item list-group-item-action">
                <i class="fas fa-users-cog"></i> Manage Users
            </a>
            <a href="<?php echo BASE_URL; ?>admin/manage_doctors.php" class="list-group-item list-group-item-action active">
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
            <h1 class="mb-4 text-dark">Manage Doctors</h1>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card-custom mb-4">
                <h4>Add New Doctor</h4>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="specialization">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization">
                        </div>
                    </div>
                    <button type="submit" name="add_doctor" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Doctor</button>
                </form>
            </div>

            <div class="card-custom">
                <h4>Existing Doctors</h4>
                <?php if (!empty($doctors)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Specialization</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doctor['doctor_id']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning edit-btn"
                                            data-id="<?php echo $doctor['doctor_id']; ?>"
                                            data-first_name="<?php echo htmlspecialchars($doctor['first_name']); ?>"
                                            data-last_name="<?php echo htmlspecialchars($doctor['last_name']); ?>"
                                            data-specialization="<?php echo htmlspecialchars($doctor['specialization']); ?>"
                                            data-toggle="modal" data-target="#editDoctorModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>admin/manage_doctors.php?action=delete&id=<?php echo htmlspecialchars($doctor['doctor_id']); ?>"
                                       class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this doctor? This will also delete related appointments and set assistant doctor_id to NULL.');">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-info">No doctors found. Add a new doctor above.</p>
                <?php endif; ?>
            </div>

            <div class="modal fade" id="editDoctorModal" tabindex="-1" role="dialog" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editDoctorModalLabel">Edit Doctor</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <div class="modal-body">
                                <input type="hidden" id="edit_doctor_id" name="doctor_id">
                                <div class="form-group">
                                    <label for="edit_first_name">First Name</label>
                                    <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_last_name">Last Name</label>
                                    <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_specialization">Specialization</label>
                                    <input type="text" class="form-control" id="edit_specialization" name="specialization">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" name="edit_doctor" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // JavaScript to populate the edit modal when the edit button is clicked
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var firstName = $(this).data('first_name');
            var lastName = $(this).data('last_name');
            var specialization = $(this).data('specialization');

            $('#edit_doctor_id').val(id);
            $('#edit_first_name').val(firstName);
            $('#edit_last_name').val(lastName);
            $('#edit_specialization').val(specialization);
        });
    </script>
</body>
</html>