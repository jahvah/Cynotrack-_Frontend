<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

/* ================= DONOR APPOINTMENTS ================= */
$donor_query = "SELECT 
        a.appointment_id,
        a.appointment_date,
        a.type,
        a.status,
        u.first_name,
        u.last_name
    FROM appointments a
    JOIN donors_users u ON a.user_id = u.donor_id
    WHERE a.user_type = 'donor'
    ORDER BY a.appointment_id DESC";
$donor_result = mysqli_query($conn, $donor_query);

/* ================= RECIPIENT APPOINTMENTS ================= */
$recipient_query = "SELECT 
        a.appointment_id,
        a.appointment_date,
        a.type,
        a.status,
        u.first_name,
        u.last_name
    FROM appointments a
    JOIN recipients_users u ON a.user_id = u.recipient_id
    WHERE a.user_type = 'recipient'
    ORDER BY a.appointment_id DESC";
$recipient_result = mysqli_query($conn, $recipient_query);

/* ================= STORAGE APPOINTMENTS ================= */
$storage_query = "SELECT 
        a.appointment_id,
        a.appointment_date,
        a.type,
        a.status,
        u.first_name,
        u.last_name
    FROM appointments a
    JOIN self_storage_users u ON a.user_id = u.storage_user_id
    WHERE a.user_type = 'storage'
    ORDER BY a.appointment_id DESC";
$storage_result = mysqli_query($conn, $storage_query);

$totalDonor = mysqli_num_rows($donor_result);
$totalRecipient = mysqli_num_rows($recipient_result);
$totalStorage = mysqli_num_rows($storage_result);
?>

<style>

@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Nunito:wght@300;400;600;700&display=swap');

.staff-wrap{
font-family:'Nunito',sans-serif;
background:linear-gradient(180deg,#f0fdf4 0%,#ffffff 100%);
min-height:100vh;
}

.display-font{
font-family:'Playfair Display',serif;
}

/* HERO */

.hero{
background:linear-gradient(135deg,#052e16,#166534);
padding:40px;
color:white;
}

.stat-card{
background:rgba(255,255,255,.15);
border-radius:14px;
padding:16px;
text-align:center;
}

/* TABLE */

.card{
background:white;
border-radius:16px;
border:1px solid #dcfce7;
box-shadow:0 10px 25px rgba(0,0,0,.04);
overflow:hidden;
}

.table{
width:100%;
border-collapse:collapse;
}

.table th{
background:#f0fdf4;
font-size:12px;
text-transform:uppercase;
letter-spacing:.08em;
padding:12px;
}

.table td{
padding:12px;
border-top:1px solid #f0fdf4;
font-size:14px;
}

.badge{
padding:4px 10px;
border-radius:8px;
font-size:11px;
font-weight:700;
}

.green{background:#dcfce7;color:#166534;}
.red{background:#fee2e2;color:#991b1b;}
.yellow{background:#fef9c3;color:#92400e;}

.btn{
padding:6px 12px;
border-radius:8px;
font-size:12px;
font-weight:700;
text-decoration:none;
}

.btn-edit{background:#f59e0b;color:white;}
.btn-delete{background:#ef4444;color:white;}
.btn-add{background:#16a34a;color:white;padding:8px 14px;}

.section{
margin-top:40px;
}

</style>


<div class="staff-wrap">

<!-- HERO -->
<div class="hero">

<div class="max-w-6xl mx-auto">

<h1 class="display-font text-3xl mb-6">
Appointment Management
</h1>

<div class="grid grid-cols-3 gap-4">

<div class="stat-card">
<div class="text-3xl font-bold"><?= $totalDonor ?></div>
<div class="text-xs uppercase">Donor Appointments</div>
</div>

<div class="stat-card">
<div class="text-3xl font-bold"><?= $totalRecipient ?></div>
<div class="text-xs uppercase">Recipient Appointments</div>
</div>

<div class="stat-card">
<div class="text-3xl font-bold"><?= $totalStorage ?></div>
<div class="text-xs uppercase">Storage Appointments</div>
</div>

</div>

</div>
</div>


<div class="max-w-6xl mx-auto px-4 py-10">


<!-- DONOR -->
<div class="section">

<div class="flex justify-between items-center mb-4">
<h2 class="display-font text-xl text-green-900">Donor Appointments</h2>

<a href="StaffAppointmentDonorCrud/StaffAppointmentDonorCreate.php"
class="btn-add">
+ Add Appointment
</a>
</div>

<div class="card">
<table class="table">

<tr>
<th>ID</th>
<th>Name</th>
<th>Date</th>
<th>Type</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row=mysqli_fetch_assoc($donor_result)): ?>

<tr>

<td><?= $row['appointment_id'] ?></td>

<td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>

<td><?= date("M d Y h:i A",strtotime($row['appointment_date'])) ?></td>

<td><?= ucfirst($row['type']) ?></td>

<td>

<?php
$status=$row['status'];
$class=$status=='completed'?'green':($status=='cancelled'?'red':'yellow');
?>

<span class="badge <?= $class ?>">
<?= ucfirst($status) ?>
</span>

</td>

<td>

<?php if($status=='scheduled'): ?>

<a class="btn btn-edit"
href="StaffAppointmentDonorCrud/StaffAppointmentDonorUpdate.php?id=<?= $row['appointment_id'] ?>">
Edit
</a>

<a class="btn btn-delete"
onclick="return confirm('Delete appointment?')"
href="StaffAppointmentDonorCrud/StaffAppointmentDonorDelete.php?id=<?= $row['appointment_id'] ?>">
Delete
</a>

<?php else: ?>

-

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

</table>
</div>

</div>


<!-- RECIPIENT -->

<div class="section">

<div class="flex justify-between items-center mb-4">
<h2 class="display-font text-xl text-green-900">Recipient Appointments</h2>

<a href="StaffAppointmentRecipientCrud/StaffAppointmentRecipientCreate.php"
class="btn-add">
+ Add Appointment
</a>
</div>

<div class="card">
<table class="table">

<tr>
<th>ID</th>
<th>Name</th>
<th>Date</th>
<th>Type</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row=mysqli_fetch_assoc($recipient_result)): ?>

<tr>

<td><?= $row['appointment_id'] ?></td>

<td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>

<td><?= date("M d Y h:i A",strtotime($row['appointment_date'])) ?></td>

<td><?= ucfirst($row['type']) ?></td>

<td>

<?php
$status=$row['status'];
$class=$status=='completed'?'green':($status=='cancelled'?'red':'yellow');
?>

<span class="badge <?= $class ?>">
<?= ucfirst($status) ?>
</span>

</td>

<td>

<?php if($status=='scheduled'): ?>

<a class="btn btn-edit"
href="StaffAppointmentRecipientCrud/StaffAppointmentRecipientUpdate.php?id=<?= $row['appointment_id'] ?>">
Edit
</a>

<a class="btn btn-delete"
onclick="return confirm('Delete appointment?')"
href="StaffAppointmentRecipientCrud/StaffAppointmentRecipientDelete.php?id=<?= $row['appointment_id'] ?>">
Delete
</a>

<?php else: ?>

-

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

</table>
</div>

</div>


<!-- STORAGE -->

<div class="section">

<div class="flex justify-between items-center mb-4">
<h2 class="display-font text-xl text-green-900">Storage Appointments</h2>

<a href="StaffAppointmentSelfStorageCrud/StaffAppointmentSelfStorageCreate.php"
class="btn-add">
+ Add Appointment
</a>
</div>

<div class="card">
<table class="table">

<tr>
<th>ID</th>
<th>Name</th>
<th>Date</th>
<th>Type</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row=mysqli_fetch_assoc($storage_result)): ?>

<tr>

<td><?= $row['appointment_id'] ?></td>

<td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>

<td><?= date("M d Y h:i A",strtotime($row['appointment_date'])) ?></td>

<td><?= ucfirst($row['type']) ?></td>

<td>

<?php
$status=$row['status'];
$class=$status=='completed'?'green':($status=='cancelled'?'red':'yellow');
?>

<span class="badge <?= $class ?>">
<?= ucfirst($status) ?>
</span>

</td>

<td>

<?php if($status=='scheduled'): ?>

<a class="btn btn-edit"
href="StaffAppointmentSelfStorageCrud/StaffAppointmentSelfStorageUpdate.php?id=<?= $row['appointment_id'] ?>">
Edit
</a>

<a class="btn btn-delete"
onclick="return confirm('Delete appointment?')"
href="StaffAppointmentSelfStorageCrud/StaffAppointmentSelfStorageDelete.php?id=<?= $row['appointment_id'] ?>">
Delete
</a>

<?php else: ?>

-

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

</table>
</div>

</div>


</div>
</div>

<?php include('../../../includes/footer.php'); ?>
