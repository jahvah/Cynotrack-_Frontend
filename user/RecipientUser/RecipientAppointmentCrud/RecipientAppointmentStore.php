<?php
session_start();
include('../../../includes/config.php');

// Admin protection
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if (!isset($_POST['action'])) {
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

// =============================================
// CREATE APPOINTMENT
// =============================================
if ($_POST['action'] === 'RecipientAppointmentStore') {

    $recipient_id     = intval($_POST['recipient_id']);
    $appointment_date = trim($_POST['appointment_date']);
    $type             = trim($_POST['type']);
    $status           = trim($_POST['status']);

    // Validate recipient exists
    $checkRecipient = $conn->prepare("SELECT recipient_id FROM recipients_users WHERE recipient_id=?");
    $checkRecipient->bind_param("i", $recipient_id);
    $checkRecipient->execute();
    $checkRecipient->store_result();

    if ($checkRecipient->num_rows === 0) {
        $_SESSION['error'] = "Selected recipient does not exist.";
        header("Location: RecipientAppointmentCreate.php");
        exit();
    }

    // Validate appointment date is not in the past
    if (strtotime($appointment_date) < time()) {
        $_SESSION['error'] = "Appointment date cannot be in the past.";
        header("Location: RecipientAppointmentCreate.php");
        exit();
    }

    // Validate type
    $allowed_types = ['consultation', 'release', 'donation', 'storage'];
    if (!in_array($type, $allowed_types)) {
        $_SESSION['error'] = "Invalid appointment type.";
        header("Location: RecipientAppointmentCreate.php");
        exit();
    }

    // Validate status
    $allowed_statuses = ['scheduled', 'completed', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        $_SESSION['error'] = "Invalid appointment status.";
        header("Location: RecipientAppointmentCreate.php");
        exit();
    }

    // Insert appointment
    $stmt = $conn->prepare("
        INSERT INTO appointments (user_type, user_id, appointment_date, type, status)
        VALUES ('recipient', ?, ?, ?, ?)
    ");
    $stmt->bind_param("isss", $recipient_id, $appointment_date, $type, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment scheduled successfully.";
        header("Location: RecipientAppointmentIndex.php?success=appointment_created");
    } else {
        $_SESSION['error'] = "Failed to schedule appointment: " . $stmt->error;
        header("Location: RecipientAppointmentCreate.php");
    }
    exit();
}

// =============================================
// UPDATE APPOINTMENT
// =============================================
if ($_POST['action'] === 'RecipientAppointmentUpdate') {

    $appointment_id   = intval($_POST['appointment_id']);
    $appointment_date = trim($_POST['appointment_date']);
    $type             = trim($_POST['type']);
    $status           = trim($_POST['status']);
    $redirect         = "RecipientAppointmentUpdate.php?id=" . $appointment_id;

    // Fetch current values
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id=? AND user_type='recipient'");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();

    if (!$current) {
        $_SESSION['error'] = "Appointment not found.";
        header("Location: RecipientAppointmentIndex.php");
        exit();
    }

    $updated = false;

    // Update appointment date
    if (!empty($appointment_date) && $appointment_date !== date('Y-m-d\TH:i', strtotime($current['appointment_date']))) {
        $stmt = $conn->prepare("UPDATE appointments SET appointment_date=? WHERE appointment_id=?");
        $stmt->bind_param("si", $appointment_date, $appointment_id);
        $stmt->execute();
        $updated = true;
    }

    // Update type
    $allowed_types = ['consultation', 'release', 'donation', 'storage'];
    if (!empty($type) && in_array($type, $allowed_types) && $type !== $current['type']) {
        $stmt = $conn->prepare("UPDATE appointments SET type=? WHERE appointment_id=?");
        $stmt->bind_param("si", $type, $appointment_id);
        $stmt->execute();
        $updated = true;
    }

    // Update status
    $allowed_statuses = ['scheduled', 'completed', 'cancelled'];
    if (!empty($status) && in_array($status, $allowed_statuses) && $status !== $current['status']) {
        $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE appointment_id=?");
        $stmt->bind_param("si", $status, $appointment_id);
        $stmt->execute();
        $updated = true;
    }

    if ($updated) {
        $_SESSION['success'] = "Appointment updated successfully!";
    } else {
        $_SESSION['error'] = "No changes detected.";
    }

    header("Location: $redirect");
    exit();
}

// Fallback
header("Location: RecipientAppointmentIndex.php");
exit();
?>
