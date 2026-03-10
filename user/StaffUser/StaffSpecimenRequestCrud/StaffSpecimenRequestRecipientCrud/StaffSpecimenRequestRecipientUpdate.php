<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Get request_id
$request_id = intval($_GET['id'] ?? 0);
if ($request_id <= 0) {
    $_SESSION['error'] = "Invalid request ID.";
    header("Location: ../StaffSpecimenRequestIndex.php");
    exit();
}

// Fetch request
$query = "
SELECT sr.request_id, sr.recipient_id, sr.specimen_id, sr.requested_quantity,
       sr.status, sr.payment_status, sr.receipt_image,
       ru.first_name AS recipient_first, ru.last_name AS recipient_last,
       s.unique_code,
       s.specimen_owner_type, s.specimen_owner_id,
       du.first_name AS donor_first, du.last_name AS donor_last
FROM specimen_requests sr
INNER JOIN recipients_users ru ON sr.recipient_id = ru.recipient_id
INNER JOIN specimens s ON sr.specimen_id = s.specimen_id
LEFT JOIN donors_users du ON s.specimen_owner_type = 'donor' AND s.specimen_owner_id = du.donor_id
WHERE sr.request_id = ?
LIMIT 1
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request) {
    $_SESSION['error'] = "Request not found.";
    header("Location: ../StaffSpecimenRequestIndex.php");
    exit();
}

// Status options
$status_options = ['pending', 'approved', 'rejected', 'fulfilled'];
$payment_options = ['unpaid', 'paid', 'refunded'];
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
button { padding: 10px 15px; background: blue; color: white; border: none; cursor: pointer; }
.message { padding: 12px; margin-bottom: 15px; border-radius: 5px; }
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }
.back-btn { display: inline-block; padding: 8px 15px; background: #555; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 15px; }
.back-btn:hover { background: #333; }
</style>

<div class="container">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <a href="../StaffSpecimenRequestIndex.php" class="back-btn">← Back to Request Dashboard</a>
    <h2>Update Specimen Request</h2>

<form action="StaffSpecimenRequestRecipientStore.php" method="POST">
    <input type="hidden" name="request_id" value="<?= $request['request_id']; ?>">
    <input type="hidden" name="action" value="update_specimen_request">

        <!-- Recipient -->
        <label>Recipient</label>
        <input type="text" value="<?= $request['recipient_first'] . ' ' . $request['recipient_last']; ?>" disabled>

        <!-- Donor -->
        <label>Donor</label>
        <input type="text" value="<?= $request['donor_first'] . ' ' . $request['donor_last']; ?>" disabled>

        <!-- Specimen -->
        <label>Specimen</label>
        <input type="text" value="<?= $request['unique_code']; ?>" disabled>

        <!-- Requested Quantity -->
        <label>Requested Quantity</label>
        <input type="number" value="<?= $request['requested_quantity']; ?>" disabled>

        <!-- Request Status -->
        <label>Request Status</label>
        <select name="status" required>
            <?php foreach ($status_options as $status): ?>
                <option value="<?= $status; ?>" <?= $request['status'] === $status ? 'selected' : ''; ?>>
                    <?= ucfirst($status); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Payment Status -->
        <label>Payment Status</label>
        <select name="payment_status" required>
            <?php foreach ($payment_options as $payment): ?>
                <option value="<?= $payment; ?>" <?= $request['payment_status'] === $payment ? 'selected' : ''; ?>>
                    <?= ucfirst($payment); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Update Request</button>
    </form>
</div>

<?php include('../../../../includes/footer.php'); ?>