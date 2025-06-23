<?php
// dashboard/admin/reports.php
$page_title = "View Reports";
include_once '../../includes/header.php'; // Use the unified header

// Check if the logged-in user is an admin
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /clinic-management/index.php"); // Redirect if not admin
    exit();
}

// --- Data Fetching for Reports ---
$total_appointments = 0;
$appointments_by_status = [
    'Pending' => 0,
    'OnGoing' => 0,
    'Completed' => 0,
    'Cancelled' => 0
];
$total_patients = 0;
$total_assistants = 0;
$appointments_by_payment = [
    'Cash' => 0,
    'Online' => 0
];
$completed_sessions = []; // Array to store completed sessions

// Get search parameter from GET request
$search_query = htmlspecialchars(trim($_GET['search_query'] ?? ''));

// Get sorting parameters from GET request (new approach for table headers)
$sort_patient = $_GET['sort_patient'] ?? '';
$sort_doctor = $_GET['sort_doctor'] ?? '';
$sort_room = $_GET['sort_room'] ?? '';
$sort_payment = $_GET['sort_payment'] ?? '';
$sort_fee = $_GET['sort_fee'] ?? '';

// Build the base query for completed sessions
$sql_completed_sessions = "
    SELECT
        a.AppointmentID,
        a.AppointmentSchedule,
        a.RoomNumber,
        a.PaymentMethod,
        a.ReasonForAppointment,
        p.FirstName AS PatientFirstName,
        p.LastName AS PatientLastName,
        p.ContactNumber AS PatientContactNumber,
        ast.FirstName AS AssistantFirstName,
        ast.LastName AS AssistantLastName,
        ast.Specialization,
        ast.SessionFee
    FROM
        AppointmentTBL a
    JOIN
        PatientTBL p ON a.PatientID = p.PatientID
    JOIN
        AssistantTBL ast ON a.AssistantID = ast.AssistantID
    WHERE
        a.Status = 'Completed'
";

$params = [];
$param_types = "";

// Add search condition (enhanced to use CONCAT for full name search, including reverse order)
if (!empty($search_query)) {
    $search_term_like = '%' . $search_query . '%';
    $keywords_arr = explode(' ', $search_query);
    $reverse_search_query_like = '';

    // If there's more than one word, create a reverse order search term
    if (count($keywords_arr) > 1) {
        $reverse_search_query_like = '%' . implode(' ', array_reverse($keywords_arr)) . '%';
    } else {
        // If only one word, the reverse search is the same as the regular search
        $reverse_search_query_like = $search_term_like;
    }

    $sql_completed_sessions .= " AND (";
    // Patient name search: "FirstName LastName" OR "LastName FirstName"
    $sql_completed_sessions .= "(CONCAT(p.FirstName, ' ', p.LastName) LIKE ? OR CONCAT(p.LastName, ' ', p.FirstName) LIKE ?)";
    $param_types .= "ss";
    $params[] = $search_term_like;
    $params[] = $reverse_search_query_like;

    // Assistant name search: "FirstName LastName" OR "LastName FirstName"
    $sql_completed_sessions .= " OR (CONCAT(ast.FirstName, ' ', ast.LastName) LIKE ? OR CONCAT(ast.LastName, ' ', ast.FirstName) LIKE ?)";
    $param_types .= "ss";
    $params[] = $search_term_like;
    $params[] = $reverse_search_query_like;

    $sql_completed_sessions .= ")";
}

// Add ORDER BY clauses based on new header sorting parameters
$order_by_clauses = [];

if ($sort_patient === 'asc') {
    $order_by_clauses[] = "p.LastName ASC, p.FirstName ASC";
} elseif ($sort_patient === 'desc') {
    $order_by_clauses[] = "p.LastName DESC, p.FirstName DESC";
}

if ($sort_doctor === 'asc') {
    $order_by_clauses[] = "ast.LastName ASC, ast.FirstName ASC";
} elseif ($sort_doctor === 'desc') {
    $order_by_clauses[] = "ast.LastName DESC, ast.FirstName DESC";
}

if ($sort_room === 'asc') {
    $order_by_clauses[] = "a.RoomNumber ASC";
} elseif ($sort_room === 'desc') {
    $order_by_clauses[] = "a.RoomNumber DESC";
}

if ($sort_payment === 'asc') {
    $order_by_clauses[] = "a.PaymentMethod ASC";
} elseif ($sort_payment === 'desc') {
    $order_by_clauses[] = "a.PaymentMethod DESC";
}

if ($sort_fee === 'asc') {
    $order_by_clauses[] = "ast.SessionFee ASC";
} elseif ($sort_fee === 'desc') {
    $order_by_clauses[] = "ast.SessionFee DESC";
}

// Default order if no specific sorting is applied
if (empty($order_by_clauses)) {
    $order_by_clauses[] = "a.AppointmentSchedule DESC"; // Default sort by latest
}

$sql_completed_sessions .= " ORDER BY " . implode(", ", $order_by_clauses);

// Fetch Completed Sessions with Patient and Assistant Details
$stmt = $conn->prepare($sql_completed_sessions);
if ($stmt) {
    if (!empty($params)) {
        // Use call_user_func_array to bind parameters dynamically
        $bind_names = [$param_types];
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $completed_sessions[] = $row;
    }
    $stmt->close();
} else {
    error_log("Failed to fetch completed sessions with filters and search: " . $conn->error);
}

// Fetch other statistics (these don't require filtering by patient/doctor/etc.)
// Fetch Total Appointments
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM AppointmentTBL");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_appointments = $row['total'];
    $stmt->close();
}

// Fetch Appointments by Status
$stmt = $conn->prepare("SELECT Status, COUNT(*) AS count FROM AppointmentTBL GROUP BY Status");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (isset($appointments_by_status[$row['Status']])) {
            $appointments_by_status[$row['Status']] = $row['count'];
        }
    }
    $stmt->close();
}

// Fetch Total Patients
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM PatientTBL");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_patients = $row['total'];
    $stmt->close();
}

// Fetch Total Assistants
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM AssistantTBL");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_assistants = $row['total'];
    $stmt->close();
}

// Fetch Appointments by Payment Method
$stmt = $conn->prepare("SELECT PaymentMethod, COUNT(*) AS count FROM AppointmentTBL GROUP BY PaymentMethod");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (isset($appointments_by_payment[$row['PaymentMethod']])) {
            $appointments_by_payment[$row['PaymentMethod']] = $row['count'];
        }
    }
    $stmt->close();
}

// Note: $conn is kept open here to be used by the page including this header.
// It will be closed in the footer.php
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-purple-800 mb-4">Clinic Reports</h1>
    <p class="text-gray-700 text-lg">Access various analytical reports on clinic performance and operations.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Total Appointments Card -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-sm font-medium">Total Appointments</p>
            <p class="text-3xl font-bold text-purple-700"><?php echo $total_appointments; ?></p>
        </div>
        <div class="p-3 bg-purple-100 rounded-full text-purple-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
    </div>

    <!-- Total Patients Card -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-sm font-medium">Total Patients</p>
            <p class="text-3xl font-bold text-green-700"><?php echo $total_patients; ?></p>
        </div>
        <div class="p-3 bg-green-100 rounded-full text-green-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354v15.3M12 15.75l-6.5-6.5m6.5 6.5l6.5-6.5"></path></svg>
        </div>
    </div>

    <!-- Total Assistants Card -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-sm font-medium">Total Assistants</p>
            <p class="text-3xl font-bold text-blue-700"><?php echo $total_assistants; ?></p>
        </div>
        <div class="p-3 bg-blue-100 rounded-full text-blue-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354v15.3M12 15.75l-6.5-6.5m6.5 6.5l6.5-6.5"></path></svg>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Appointments by Status Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Appointments by Status</h2>
        <ul class="space-y-2">
            <li class="flex justify-between items-center py-2 px-3 rounded-md bg-yellow-50 border border-yellow-200">
                <span class="font-medium text-yellow-800">Pending:</span>
                <span class="font-bold text-yellow-900"><?php echo $appointments_by_status['Pending']; ?></span>
            </li>
            <li class="flex justify-between items-center py-2 px-3 rounded-md bg-blue-50 border border-blue-200">
                <span class="font-medium text-blue-800">OnGoing:</span>
                <span class="font-bold text-blue-900"><?php echo $appointments_by_status['OnGoing']; ?></span>
            </li>
            <li class="flex justify-between items-center py-2 px-3 rounded-md bg-green-50 border border-green-200">
                <span class="font-medium text-green-800">Completed:</span>
                <span class="font-bold text-green-900"><?php echo $appointments_by_status['Completed']; ?></span>
            </li>
            <li class="flex justify-between items-center py-2 px-3 rounded-md bg-red-50 border border-red-200">
                <span class="font-medium text-red-800">Cancelled:</span>
                <span class="font-bold text-red-900"><?php echo $appointments_by_status['Cancelled']; ?></span>
            </li>
        </ul>
    </div>

    <!-- Appointments by Payment Method Card -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Appointments by Payment Method</h2>
        <ul class="space-y-2">
            <li class="flex justify-between items-center py-2 px-3 rounded-md bg-gray-50 border border-gray-200">
                <span class="font-medium text-gray-800">Cash:</span>
                <span class="font-bold text-gray-900"><?php echo $appointments_by_payment['Cash']; ?></span>
            </li>
            <li class="flex justify-between items-center py-2 px-3 rounded-md bg-gray-50 border border-gray-200">
                <span class="font-medium text-gray-800">Online:</span>
                <span class="font-bold text-gray-900"><?php echo $appointments_by_payment['Online']; ?></span>
            </li>
        </ul>
    </div>
</div>

<!-- Completed Sessions Log -->
<div id="completedSessionsLog" class="bg-white p-8 rounded-lg shadow-md">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 space-y-4 sm:space-y-0">
        <h2 class="text-2xl font-bold text-purple-700">Completed Sessions Log</h2>
        <button onclick="printCompletedSessions()"
                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md inline-flex items-center space-x-2 transition duration-300 ease-in-out">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m0 0l-1-1m1 1l1-1m-1 0V9m0 8h.01M12 10a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
            <span>Print Log</span>
        </button>
    </div>

    <!-- Filter & Search Form -->
    <form action="" method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div>
            <label for="search_query" class="block text-gray-700 text-sm font-medium mb-2">Search by Name</label>
            <input type="text" id="search_query" name="search_query"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                   placeholder="Patient or Doctor Name" value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
        <div class="col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4 flex justify-end">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                Apply Search
            </button>
        </div>
    </form>

    <?php if (empty($completed_sessions)): ?>
        <div id="completedSessionsData" class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded-md shadow-sm" role="alert">
            <p class="font-bold">No completed sessions found yet.</p>
            <p>Once appointments are marked 'Completed', they will appear here, or adjust your filters and search query.</p>
        </div>
    <?php else: ?>
        <div id="completedSessionsData"> <!-- New wrapper div for the data only -->
            <!-- Table for desktop -->
            <div class="overflow-x-auto hidden sm:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                                Appt ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php
                                $next_patient_sort = ($sort_patient === 'asc') ? 'desc' : 'asc';
                                $arrow_patient = '';
                                if ($sort_patient === 'asc') $arrow_patient = '<span class="sort-arrow text-lg font-bold">&#9650;</span>';
                                if ($sort_patient === 'desc') $arrow_patient = '<span class="sort-arrow text-lg font-bold">&#9660;</span>';
                                $patient_sort_url = '?search_query=' . urlencode($search_query) . '&sort_patient=' . $next_patient_sort;
                                ?>
                                <a href="<?php echo htmlspecialchars($patient_sort_url); ?>" class="flex items-center hover:text-gray-700">
                                    Patient <?php echo $arrow_patient; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php
                                $next_doctor_sort = ($sort_doctor === 'asc') ? 'desc' : 'asc';
                                $arrow_doctor = '';
                                if ($sort_doctor === 'asc') $arrow_doctor = '<span class="sort-arrow text-lg font-bold">&#9650;</span>';
                                if ($sort_doctor === 'desc') $arrow_doctor = '<span class="sort-arrow text-lg font-bold">&#9660;</span>';
                                $doctor_sort_url = '?search_query=' . urlencode($search_query) . '&sort_doctor=' . $next_doctor_sort;
                                ?>
                                <a href="<?php echo htmlspecialchars($doctor_sort_url); ?>" class="flex items-center hover:text-gray-700">
                                    Assistant <?php echo $arrow_doctor; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reason
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php
                                $next_room_sort = ($sort_room === 'asc') ? 'desc' : 'asc';
                                $arrow_room = '';
                                if ($sort_room === 'asc') $arrow_room = '<span class="sort-arrow text-lg font-bold">&#9650;</span>';
                                if ($sort_room === 'desc') $arrow_room = '<span class="sort-arrow text-lg font-bold">&#9660;</span>';
                                $room_sort_url = '?search_query=' . urlencode($search_query) . '&sort_room=' . $next_room_sort;
                                ?>
                                <a href="<?php echo htmlspecialchars($room_sort_url); ?>" class="flex items-center hover:text-gray-700">
                                    Room <?php echo $arrow_room; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php
                                $next_payment_sort = ($sort_payment === 'asc') ? 'desc' : 'asc';
                                $arrow_payment = '';
                                if ($sort_payment === 'asc') $arrow_payment = '<span class="sort-arrow text-lg font-bold">&#9650;</span>';
                                if ($sort_payment === 'desc') $arrow_payment = '<span class="sort-arrow text-lg font-bold">&#9660;</span>';
                                $payment_sort_url = '?search_query=' . urlencode($search_query) . '&sort_payment=' . $next_payment_sort;
                                ?>
                                <a href="<?php echo htmlspecialchars($payment_sort_url); ?>" class="flex items-center hover:text-gray-700">
                                    Payment <?php echo $arrow_payment; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                                <?php
                                $next_fee_sort = ($sort_fee === 'asc') ? 'desc' : 'asc';
                                $arrow_fee = '';
                                if ($sort_fee === 'asc') $arrow_fee = '<span class="sort-arrow text-lg font-bold">&#9650;</span>';
                                if ($sort_fee === 'desc') $arrow_fee = '<span class="sort-arrow text-lg font-bold">&#9660;</span>';
                                $fee_sort_url = '?search_query=' . urlencode($search_query) . '&sort_fee=' . $next_fee_sort;
                                ?>
                                <a href="<?php echo htmlspecialchars($fee_sort_url); ?>" class="flex items-center hover:text-gray-700">
                                    Fee <?php echo $arrow_fee; ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($completed_sessions as $session): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($session['AppointmentID']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('F j, Y, g:i A', strtotime($session['AppointmentSchedule'])); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($session['PatientFirstName'] . ' ' . $session['PatientLastName']); ?>
                                    <br><span class="text-xs text-gray-500"><?php echo htmlspecialchars($session['PatientContactNumber']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    Dr. <?php echo htmlspecialchars($session['AssistantFirstName'] . ' ' . $session['AssistantLastName']); ?>
                                    <br><span class="text-xs text-gray-500"><?php echo htmlspecialchars($session['Specialization']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs overflow-hidden text-ellipsis">
                                    <?php echo htmlspecialchars($session['ReasonForAppointment'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo htmlspecialchars($session['RoomNumber'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo htmlspecialchars($session['PaymentMethod']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    ₱<?php echo number_format($session['SessionFee'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Card layout for smaller screens -->
            <div class="sm:hidden grid grid-cols-1 gap-4">
                <?php foreach ($completed_sessions as $session): ?>
                    <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <div class="font-bold text-lg text-purple-700">Appt ID: <?php echo htmlspecialchars($session['AppointmentID']); ?></div>
                            <?php
                                $status_class = '';
                                switch ($session['Status']) {
                                    case 'Pending':
                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'Completed':
                                        $status_class = 'bg-green-100 text-green-800';
                                        break;
                                    case 'OnGoing':
                                        $status_class = 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'Cancelled':
                                        $status_class = 'bg-red-100 text-red-800';
                                        break;
                                    default:
                                        $status_class = 'bg-gray-100 text-gray-800';
                                        break;
                                }
                            ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($session['Status']); ?>
                            </span>
                        </div>
                        <div class="text-gray-700 mb-1">
                            <span class="font-semibold">Date:</span> <?php echo date('F j, Y, g:i A', strtotime($session['AppointmentSchedule'])); ?>
                        </div>
                        <div class="text-gray-700 mb-1">
                            <span class="font-semibold">Patient:</span> <?php echo htmlspecialchars($session['PatientFirstName'] . ' ' . $session['PatientLastName']); ?>
                            <span class="text-xs text-gray-500">(<?php echo htmlspecialchars($session['PatientContactNumber']); ?>)</span>
                        </div>
                        <div class="text-gray-700 mb-1">
                            <span class="font-semibold">Doctor:</span> Dr. <?php echo htmlspecialchars($session['AssistantFirstName'] . ' ' . $session['LastName']); ?>
                            <span class="text-xs text-gray-500">(<?php echo htmlspecialchars($session['Specialization']); ?>)</span>
                        </div>
                        <div class="text-gray-600 mb-1">
                            <span class="font-semibold">Reason:</span> <?php echo htmlspecialchars($session['ReasonForAppointment'] ?? 'N/A'); ?>
                        </div>
                        <div class="text-gray-600 mb-1">
                            <span class="font-semibold">Room:</span> <?php echo htmlspecialchars($session['RoomNumber'] ?? 'N/A'); ?>
                        </div>
                        <div class="text-gray-600 mb-1">
                            <span class="font-semibold">Payment:</span> <?php echo htmlspecialchars($session['PaymentMethod']); ?>
                        </div>
                        <div class="text-gray-800 font-bold mt-2">
                            <span class="font-semibold">Fee:</span> ₱<?php echo number_format($session['SessionFee'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function printCompletedSessions() {
        const printContentDiv = document.getElementById('completedSessionsData');
        if (!printContentDiv) return;

        // Only print the desktop table, not the mobile cards
        const desktopTable = printContentDiv.querySelector('.overflow-x-auto');
        const tableHTML = desktopTable ? desktopTable.innerHTML : printContentDiv.innerHTML;

        const printWindow = window.open('', '_blank');
        printWindow.document.open();
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Completed Sessions Log</title>
                <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        margin: 20px;
                        color: #222;
                    }
                    h1 {
                        font-size: 2em;
                        font-weight: bold;
                        margin-bottom: 20px;
                        color: #4B006E;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    th, td {
                        border: 1px solid #e2e8f0;
                        padding: 8px 10px;
                        text-align: left;
                        vertical-align: top;
                        font-size: 14px;
                        background: #fff;
                        color: #222;
                        word-break: break-word;
                    }
                    th {
                        background: #f3f4f6;
                        font-weight: bold;
                    }
                    /* Custom style for sort arrows in print */
                    .sort-arrow {
                        font-size: 1.2em !important; /* Make arrows larger */
                        font-weight: bold !important; /* Make arrows bold */
                        margin-left: 4px; /* Add some spacing */
                    }
                    /* Ensure table headers repeat on each page */
                    thead { display: table-header-group; }
                    tr { page-break-inside: avoid; }
                    /* Remove text truncation for print */
                    .max-w-xs, .overflow-hidden, .text-ellipsis {
                        max-width: none !important;
                        overflow: visible !important;
                        text-overflow: unset !important;
                        white-space: normal !important;
                    }
                    /* Hide mobile card layout in print */
                    .sm\\\\:hidden, .sm\\\\:hidden * {
                        display: none !important;
                    }
                    @media print {
                        body {
                            margin: 0;
                            padding: 20px;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                    }
                </style>
            </head>
            <body>
                <h1>Completed Sessions Log</h1>
                <div>
                    <table>
                        ${tableHTML}
                    </table>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();

        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };
    }
</script>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
