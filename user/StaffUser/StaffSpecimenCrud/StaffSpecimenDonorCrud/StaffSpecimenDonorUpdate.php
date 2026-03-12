<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: StaffSpecimenIndex.php");
    exit();
}

$specimen_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT unique_code FROM specimens WHERE specimen_id = ? AND specimen_owner_type = 'donor'");
$stmt->bind_param("i", $specimen_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: StaffSpecimenIndex.php");
    exit();
}

$specimen = $result->fetch_assoc();
?>

<style>
.wrapper{
display:flex;
justify-content:center;
padding:40px;
}
.card{
width:600px;
background:white;
padding:30px;
border-radius:10px;
box-shadow:0 4px 15px rgba(0,0,0,0.1);
}
.form-group{margin-bottom:15px;}
label{font-weight:600;}
input,select{
width:100%;
padding:10px;
border:1px solid #ddd;
border-radius:6px;
}
button{
background:#007bff;
color:white;
border:none;
padding:10px 18px;
border-radius:6px;
cursor:pointer;
}
button:hover{background:#0056b3;}

.locked{background:#eee;}
.error{background:#f8d7da;padding:10px;margin-bottom:10px;}
.success{background:#d4edda;padding:10px;margin-bottom:10px;}

.back-btn{
display:inline-block;
margin-top:15px;
background:#6c757d;
color:white;
padding:8px 12px;
border-radius:6px;
text-decoration:none;
}
</style>

<div class="wrapper">
<div class="card">

<h2>Update Donor Specimen</h2>

<?php if (isset($_SESSION['error'])): ?>
<div class="error"><?= $_SESSION['error']; ?></div>
<?php unset($_SESSION['error']); endif; ?>

<?php if (isset($_SESSION['success'])): ?>
<div class="success"><?= $_SESSION['success']; ?></div>
<?php unset($_SESSION['success']); endif; ?>

<form action="StaffSpecimenDonorStore.php" method="POST">

<input type="hidden" name="action" value="update_donor_specimen">
<input type="hidden" name="specimen_id" value="<?= $specimen_id; ?>">

<div class="form-group">
<label>Unique Code</label>
<input type="text" value="<?= htmlspecialchars($specimen['unique_code']); ?>" class="locked" disabled>
<input type="hidden" name="unique_code" value="<?= htmlspecialchars($specimen['unique_code']); ?>">
</div>

<div class="form-group">
<label>Quantity</label>
<input type="number" name="quantity" min="0">
</div>

<div class="form-group">
<label>Status</label>
<select name="status">
<option value="">Select Status</option>
<option value="approved">Approved</option>
<option value="disapproved">Disapproved</option>
<option value="stored">Stored</option>
<option value="used">Used</option>
<option value="expired">Expired</option>
<option value="disposed">Disposed</option>
</select>
</div>

<div class="form-group">
<label>Storage Location</label>
<input type="text" name="storage_location">
</div>

<div class="form-group">
<label>Expiration Date</label>
<input type="date" name="expiration_date">
</div>

<button type="submit">Update Specimen</button>

<a href="../StaffSpecimenIndex.php" class="back-btn">← Back</a>

</form>
</div>
</div>

<?php include('../../../../includes/footer.php'); ?>
