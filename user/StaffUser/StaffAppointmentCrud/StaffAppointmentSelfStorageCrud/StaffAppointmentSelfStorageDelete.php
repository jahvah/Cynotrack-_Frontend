<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Require ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid appointment.";
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);

if ($appointment_id <= 0) {
    $_SESSION['error'] = "Invalid appointment ID.";
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

// Verify it exists and is a storage appointment
$check = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_id = ? AND user_type = 'storage'");
$check->bind_param("i", $appointment_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $_SESSION['error'] = "Appointment not found or is not a self-storage appointment.";
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

// Delete
$stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ? AND user_type = 'storage'");
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Self-storage appointment deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete appointment.";
}

header("Location: ../StaffAppointmentIndex.php");
exit();
?>
