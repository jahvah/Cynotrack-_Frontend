<style>
.container {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
    font-family: Arial, sans-serif;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

h2 { margin: 0; }

.back-btn, .create-btn {
    padding: 10px 18px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    font-weight: 600;
    transition: 0.2s;
}

.back-btn { background: #6c757d; }
.back-btn:hover { background: #5a6268; }

.create-btn { background: #28a745; }
.create-btn:hover { background: #218838; }

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    background: white;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

th {
    background: #007bff;
    color: white;
    font-weight: 600;
}

tr:last-child td {
    border-bottom: none;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    color: white;
    font-size: 12px;
    font-weight: 600;
}

.green { background: #28a745; }
.red { background: #dc3545; }
.yellow { background: #ffc107; color: #212529; }
.blue { background: #17a2b8; }

.action-btn {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    color: white;
    text-decoration: none;
    margin: 2px;
    display: inline-block;
}

.edit-btn { background: #fd7e14; }
.edit-btn:hover { background: #e66a0d; }

.delete-btn { background: #dc3545; }
.delete-btn:hover { background: #c82333; }

.message {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-weight: 500;
}

.error { background: #f8d7da; color: #721c24; }
.success { background: #d4edda; color: #155724; }

@media(max-width:1024px){
    table { font-size: 12px; }
}

@media(max-width:768px){
    table, th, td { font-size: 11px; }
    .top-bar { flex-direction: column; gap: 10px; }
}
</style>

<div class="container">

<?php if(isset($_SESSION['error'])): ?>
<div class="message error"><?= $_SESSION['error']; ?></div>
<?php unset($_SESSION['error']); endif; ?>

<?php if(isset($_SESSION['success'])): ?>
<div class="message success"><?= $_SESSION['success']; ?></div>
<?php unset($_SESSION['success']); endif; ?>

<div class="top-bar">
    <h2>Recipient Specimen Requests</h2>
    <div>
        <a href="../StaffDashboard.php" class="back-btn">← Back to Dashboard</a>
        <a href="StaffSpecimenRequestRecipientCrud/StaffSpecimenRequestRecipientCreate.php" class="create-btn">+ Create Request</a>
    </div>
</div>

<table>
<tr>
<th>ID</th>
<th>Recipient</th>
<th>Specimen Code</th>
<th>Requested Qty</th>
<th>Request Date</th>
<th>Status</th>
<th>Fulfilled Date</th>
<th>Unit Price</th>
<th>Total Price</th>
<th>Payment</th>
<th>Receipt</th>
<th>Actions</th>
</tr>

<?php if($request_result_recipient && mysqli_num_rows($request_result_recipient) > 0): ?>
<?php while($row = mysqli_fetch_assoc($request_result_recipient)): ?>
<tr>
<td><?= $row['request_id']; ?></td>
<td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
<td><?= htmlspecialchars($row['unique_code']); ?></td>
<td><?= $row['requested_quantity']; ?></td>
<td><?= date("M d, Y h:i A", strtotime($row['request_date'])); ?></td>
<td>
<?php
$status = $row['status'];
$class = ($status=='approved')?'green':(($status=='rejected')?'red':(($status=='fulfilled')?'blue':'yellow'));
echo "<span class='badge $class'>".ucfirst($status)."</span>";
?>
</td>
<td><?= !empty($row['fulfilled_date'])?date("M d, Y h:i A",strtotime($row['fulfilled_date'])):'-'; ?></td>
<td><?= number_format($row['unit_price'],2); ?></td>
<td><?= number_format($row['total_price'],2); ?></td>
<td>
<?php
$payment = $row['payment_status'];
$pclass = ($payment=='paid')?'green':(($payment=='refunded')?'blue':(($payment=='waiting_payment')?'yellow':'red'));
echo "<span class='badge $pclass'>".ucfirst(str_replace('_',' ',$payment))."</span>";
?>
</td>
<td>
<?php if(!empty($row['receipt_image'])): ?>
<a href="../../../<?= $row['receipt_image']; ?>" target="_blank">View</a>
<?php else: ?> - <?php endif; ?>
</td>
<td>
<?php if($status=='pending' || $status=='approved'): ?>
<a href="StaffSpecimenRequestRecipientCrud/StaffSpecimenRequestRecipientUpdate.php?id=<?= $row['request_id']; ?>" class="action-btn edit-btn">Edit</a>
<a href="StaffSpecimenRequestRecipientCrud/StaffSpecimenRequestRecipientDelete.php?id=<?= $row['request_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?');">Delete</a>
<?php else: ?> - <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="12">No recipient specimen requests found.</td></tr>
<?php endif; ?>
</table>

</div>
