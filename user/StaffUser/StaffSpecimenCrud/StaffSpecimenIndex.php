<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Fetch donor specimens
$donor_query = "
    SELECT s.*,
           d.first_name,
           d.last_name
    FROM specimens s
    JOIN donors_users d
         ON s.specimen_owner_type = 'donor' AND s.specimen_owner_id = d.donor_id
    ORDER BY s.specimen_id DESC
";
$donor_result = mysqli_query($conn, $donor_query);

// Fetch self-storage specimens
$storage_query = "
    SELECT s.*,
           su.first_name,
           su.last_name
    FROM specimens s
    JOIN self_storage_users su
         ON s.specimen_owner_type = 'storage' AND s.specimen_owner_id = su.storage_user_id
    ORDER BY s.specimen_id DESC
";
$storage_result = mysqli_query($conn, $storage_query);
?>

<style>

.container{
max-width:1200px;
margin:auto;
padding:30px;
}

.card{
background:white;
padding:25px;
border-radius:10px;
box-shadow:0 3px 10px rgba(0,0,0,0.08);
margin-bottom:30px;
}

.top-bar{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.button-group{
display:flex;
gap:10px;
}

.create-btn{
padding:10px 18px;
background:#28a745;
color:white;
text-decoration:none;
border-radius:6px;
font-weight:600;
}

.create-btn:hover{
background:#218838;
}

.back-btn{
padding:10px 18px;
background:#6c757d;
color:white;
text-decoration:none;
border-radius:6px;
font-weight:600;
}

.back-btn:hover{
background:#5a6268;
}

table{
width:100%;
border-collapse:collapse;
font-size:14px;
}

th{
background:#007bff;
color:white;
padding:10px;
}

td{
padding:10px;
border-bottom:1px solid #eee;
text-align:center;
}

tr:hover{
background:#f9f9f9;
}

.action-btn{
padding:6px 10px;
border-radius:5px;
color:white;
text-decoration:none;
font-size:12px;
font-weight:600;
}

.edit-btn{
background:#f0ad4e;
}

.edit-btn:hover{
background:#ec971f;
}

.delete-btn{
background:#dc3545;
}

.delete-btn:hover{
background:#c82333;
}

.badge{
padding:5px 9px;
border-radius:5px;
color:white;
font-size:12px;
font-weight:600;
}

.green{ background:#28a745; }
.red{ background:#dc3545; }
.yellow{ background:#f0ad4e; }

.message{
padding:12px;
margin-bottom:15px;
border-radius:6px;
}

.error{
background:#f8d7da;
color:#721c24;
}

.success{
background:#d4edda;
color:#155724;
}

.section-title{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:10px;
}

h2,h3{
margin:0;
}

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

<div class="card">

<div class="top-bar">
<h2>Specimen Management</h2>

<div class="button-group">
<a href="../StaffDashboard.php" class="back-btn">← Dashboard</a>
<a href="StaffSpecimenDonorCrud/StaffSpecimenDonorCreate.php" class="create-btn">+ Donor Specimen</a>
</div>
</div>

<table>
<tr>
<th>ID</th>
<th>Unique Code</th>
<th>Donor Name</th>
<th>Quantity</th>
<th>Price</th>
<th>Status</th>
<th>Location</th>
<th>Expiration</th>
<th>Actions</th>
</tr>

<?php if ($donor_result && mysqli_num_rows($donor_result) > 0): ?>
<?php while ($row = mysqli_fetch_assoc($donor_result)): ?>

<?php
$display_status = $row['status'];
$disable_actions = false;

$class='yellow';
if($display_status=='approved'||$display_status=='stored') $class='green';
if($display_status=='expired'||$display_status=='disposed'||$display_status=='disapproved'||$display_status=='used') $class='red';
?>

<tr>
<td><?= $row['specimen_id']; ?></td>

<td><strong><?= htmlspecialchars($row['unique_code']); ?></strong></td>

<td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>

<td><?= $row['quantity']; ?></td>

<td>₱<?= isset($row['price']) ? number_format($row['price'],2) : '0.00'; ?></td>

<td>
<span class="badge <?= $class ?>">
<?= ucfirst($display_status) ?>
</span>
</td>

<td><?= htmlspecialchars($row['storage_location'] ?? 'N/A'); ?></td>

<td>
<?= $row['expiration_date'] ? date("M d, Y",strtotime($row['expiration_date'])) : 'N/A'; ?>
</td>

<td>

<?php if(!$disable_actions): ?>

<a href="StaffSpecimenDonorCrud/StaffSpecimenDonorUpdate.php?id=<?= $row['specimen_id']; ?>" class="action-btn edit-btn">Edit</a>

<a href="StaffSpecimenDonorCrud/StaffSpecimenDonorDelete.php?type=donor&id=<?= $row['specimen_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?');">Delete</a>

<?php else: ?>

<span style="color:gray;font-size:12px;">No actions</span>

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="9">No donor specimens found.</td>
</tr>

<?php endif; ?>

</table>

</div>

<div class="card">

<div class="section-title">
<h3>Self-Storage Specimens</h3>

<a href="StaffSpecimenSelfStorageCrud/StaffSpecimenSelfStorageCreate.php" class="create-btn">
+ Storage Specimen
</a>
</div>

<table>

<tr>
<th>ID</th>
<th>Unique Code</th>
<th>User Name</th>
<th>Quantity</th>
<th>Price</th>
<th>Status</th>
<th>Location</th>
<th>Expiration</th>
<th>Actions</th>
</tr>

<?php if ($storage_result && mysqli_num_rows($storage_result) > 0): ?>

<?php while ($row = mysqli_fetch_assoc($storage_result)): ?>

<?php

$display_status = $row['status'];
$disable_actions=false;

if((int)$row['quantity']===0){
$display_status='used';
$disable_actions=true;
}

if($row['status']==='disposed'){
$disable_actions=true;
}

$class='yellow';
if($display_status=='stored'||$display_status=='approved') $class='green';
if($display_status=='used'||$display_status=='expired'||$display_status=='disposed'||$display_status=='disapproved') $class='red';

?>

<tr>

<td><?= $row['specimen_id']; ?></td>

<td><strong><?= htmlspecialchars($row['unique_code']); ?></strong></td>

<td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>

<td><?= $row['quantity']; ?></td>

<td>₱<?= isset($row['price']) ? number_format($row['price'],2) : '0.00'; ?></td>

<td>
<span class="badge <?= $class ?>">
<?= ucfirst($display_status) ?>
</span>
</td>

<td><?= htmlspecialchars($row['storage_location'] ?? 'N/A'); ?></td>

<td>
<?= $row['expiration_date'] ? date("M d, Y",strtotime($row['expiration_date'])) : 'N/A'; ?>
</td>

<td>

<?php if(!$disable_actions): ?>

<a href="StaffSpecimenSelfStorageCrud/StaffSpecimenSelfStorageUpdate.php?id=<?= $row['specimen_id']; ?>" class="action-btn edit-btn">Edit</a>

<a href="StaffSpecimenSelfStorageCrud/StaffSpecimenSelfStorageDelete.php?type=storage&id=<?= $row['specimen_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?');">Delete</a>

<?php else: ?>

<span style="color:gray;font-size:12px;">No actions</span>

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="9">No storage specimens found.</td>
</tr>

<?php endif; ?>

</table>

</div>

</div>

<?php include('../../../includes/footer.php'); ?>
