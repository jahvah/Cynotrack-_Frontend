<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'create_specimen_request') {

    $recipient_id = intval($_POST['recipient_id'] ?? 0);
    $specimen_id  = intval($_POST['specimen_id'] ?? 0);
    $requested_quantity = intval($_POST['requested_quantity'] ?? 0);

    if ($recipient_id <= 0 || $specimen_id <= 0 || $requested_quantity <= 0) {
        $_SESSION['error'] = "Invalid input.";
        header("Location: StaffSpecimenRequestRecipientCreate.php");
        exit();
    }

    // 1️⃣ Check specimen quantity
    $stmt = $conn->prepare("
        SELECT quantity
        FROM specimens
        WHERE specimen_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $specimen_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Specimen not found.";
        header("Location: StaffSpecimenRequestRecipientCreate.php");
        exit();
    }

    $specimen = $result->fetch_assoc();

    if ($requested_quantity > $specimen['quantity']) {
        $_SESSION['error'] = "Not enough specimen quantity available.";
        header("Location: StaffSpecimenRequestRecipientCreate.php");
        exit();
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 2️⃣ Insert request as fulfilled & paid
        $stmt = $conn->prepare("
            INSERT INTO specimen_requests 
            (recipient_id, specimen_id, requested_quantity, status, payment_status, fulfilled_date)
            VALUES (?, ?, ?, 'fulfilled', 'paid', NOW())
        ");
        $stmt->bind_param("iii", $recipient_id, $specimen_id, $requested_quantity);
        $stmt->execute();

        $request_id = $stmt->insert_id; // get the new request ID

        // 3️⃣ Decrease specimen quantity
        $stmt = $conn->prepare("
            UPDATE specimens
            SET quantity = quantity - ?
            WHERE specimen_id = ?
        ");
        $stmt->bind_param("ii", $requested_quantity, $specimen_id);
        $stmt->execute();

        // 4️⃣ Create inventory log
        $stmt = $conn->prepare("
            INSERT INTO inventory_logs (specimen_id, action, quantity)
            VALUES (?, 'used', ?)
        ");
        $stmt->bind_param("ii", $specimen_id, $requested_quantity);
        $stmt->execute();

        // 5️⃣ Create transaction record
        $stmt = $conn->prepare("
            INSERT INTO transactions (request_id, status)
            VALUES (?, 'completed')
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "Specimen request created, fulfilled, and transaction completed successfully.";
        header("Location: StaffSpecimenRequestRecipientCreate.php");
        exit();

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Failed to create specimen request: " . $e->getMessage();
            header("Location: StaffSpecimenRequestRecipientCreate.php");
            exit();
        }
    }   