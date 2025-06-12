<?php
// dashboard/patient/history.php
$page_title = "Medical History";
include_once '../../includes/header.php'; // Include the common header

// Fetch patient's medical history
$patient_id = $_SESSION['user_id'];
$medical_history = "No medical history recorded."; // Default message

// Prepare the SQL query to fetch the MedicalHistory from PatientTBL
$stmt = $conn->prepare("SELECT MedicalHistory FROM PatientTBL WHERE PatientID = ?");

if ($stmt) {
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Use nl2br to convert newlines to <br /> for proper display of multiline text
        $medical_history = !empty($row['MedicalHistory']) ? nl2br(htmlspecialchars($row['MedicalHistory'])) : "No medical history recorded.";
    }
    $stmt->close();
} else {
    // Handle SQL preparation error
    error_log("Failed to prepare statement for medical history: " . $conn->error);
    $medical_history = "Error retrieving medical history.";
}
?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-extrabold text-blue-800 mb-4">My Medical History</h1>
    <p class="text-gray-700 text-lg">Here you can review your past medical records and notes.</p>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <?php if ($medical_history === "No medical history recorded." || $medical_history === "Error retrieving medical history."): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded-md shadow-sm" role="alert">
            <p class="font-bold">Information not available.</p>
            <p><?php echo $medical_history; ?></p>
        </div>
    <?php else: ?>
        <div class="prose max-w-none text-gray-800 leading-relaxed">
            <?php echo $medical_history; ?>
        </div>
    <?php endif; ?>
</div>

<?php
include_once '../../includes/footer.php'; // Include the common footer
?>
