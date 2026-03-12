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
background:#007bff;
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
background:#0069d9;
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

</style>

<div class="container">

<div class="card">

<a href="../StaffSpecimenRequestIndex.php" class="back-btn">← Back to Request Dashboard</a>

<h2>Update Specimen Request</h2>

<form action="StaffSpecimenRequestRecipientStore.php" method="POST">

<input type="hidden" name="request_id" value="<?= $request['request_id']; ?>">
<input type="hidden" name="action" value="update_specimen_request">

<label>Recipient</label>
<input type="text" value="<?= $request['recipient_first'].' '.$request['recipient_last']; ?>" disabled>

<label>Donor</label>
<input type="text" value="<?= $request['donor_first'].' '.$request['donor_last']; ?>" disabled>

<label>Specimen</label>
<input type="text" value="<?= $request['unique_code']; ?>" disabled>

<label>Requested Quantity</label>
<input type="number" value="<?= $request['requested_quantity']; ?>" disabled>

<label>Request Status</label>
<select name="status" required>
<?php foreach ($status_options as $status): ?>
<option value="<?= $status; ?>" <?= $request['status']===$status?'selected':'' ?>>
<?= ucfirst($status); ?>
</option>
<?php endforeach; ?>
</select>

<label>Payment Status</label>
<select name="payment_status" required>
<?php foreach ($payment_options as $payment): ?>
<option value="<?= $payment; ?>" <?= $request['payment_status']===$payment?'selected':'' ?>>
<?= ucfirst($payment); ?>
</option>
<?php endforeach; ?>
</select>

<button type="submit">Update Request</button>

</form>

</div>
</div>
