<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$recipient_result = mysqli_query($conn, "SELECT recipient_id, first_name, last_name FROM recipients_users ORDER BY first_name ASC");
$recipients = [];
while ($row = mysqli_fetch_assoc($recipient_result)) {
    $recipients[] = $row;
}

$donor_result = mysqli_query($conn, "SELECT donor_id, first_name, last_name FROM donors_users ORDER BY first_name ASC");
$donors = [];
while ($row = mysqli_fetch_assoc($donor_result)) {
    $donors[] = $row;
}
?>

<style>

.container{
max-width:900px;
margin:auto;
padding:30px;
}

.card{
background:white;
padding:30px;
border-radius:10px;
box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

h2{
margin-bottom:20px;
}

label{
font-weight:600;
margin-top:12px;
display:block;
}

input,select{
width:100%;
padding:10px;
margin-top:6px;
border:1px solid #ddd;
border-radius:6px;
}

button{
background:#28a745;
color:white;
padding:12px;
border:none;
border-radius:6px;
cursor:pointer;
margin-top:15px;
width:100%;
font-weight:600;
}

button:hover{
background:#218838;
}

.back-btn{
display:inline-block;
margin-bottom:15px;
padding:8px 14px;
background:#6c757d;
color:white;
text-decoration:none;
border-radius:6px;
}

.back-btn:hover{
background:#5a6268;
}

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

.search-container{position:relative;}

.search-results{
position:absolute;
width:100%;
background:white;
border:1px solid #ccc;
border-top:none;
z-index:1000;
max-height:200px;
overflow-y:auto;
display:none;
box-shadow:0 4px 6px rgba(0,0,0,0.1);
}

.search-item{
padding:10px;
cursor:pointer;
border-bottom:1px solid #eee;
}

.search-item:hover{
background:#f5f5f5;
}

</style>

<div class="container">

<div class="card">

<?php if (isset($_SESSION['error'])): ?>
<div class="message error"><?= $_SESSION['error']; ?></div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
<div class="message success"><?= $_SESSION['success']; ?></div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<a href="../StaffSpecimenRequestIndex.php" class="back-btn">← Back to Request Dashboard</a>

<h2>Create Specimen Request</h2>

<form action="StaffSpecimenRequestRecipientStore.php" method="POST" autocomplete="off" enctype="multipart/form-data">

<input type="hidden" name="action" value="create_specimen_request">

<label>Search Recipient</label>

<div class="search-container">
<input type="text" id="recipient_search_input" placeholder="Type recipient name..." required>
<input type="hidden" name="recipient_id" id="recipient_id_hidden" required>
<div id="recipient_search_results" class="search-results"></div>
</div>

<label>Search Donor</label>

<div class="search-container">
<input type="text" id="donor_search_input" placeholder="Type donor name..." required>
<input type="hidden" name="donor_id" id="donor_id_hidden" required>
<div id="donor_search_results" class="search-results"></div>
</div>

<label>Select Specimen</label>
<select name="specimen_id" id="specimenSelect" required>
<option value="">-- Select Donor First --</option>
</select>

<label>Requested Quantity</label>
<input type="number" name="requested_quantity" min="1" required>

<label>Upload Receipt</label>
<input type="file" name="receipt_image" accept="image/*" required>

<button type="submit">Create Request</button>

</form>

</div>
</div>
