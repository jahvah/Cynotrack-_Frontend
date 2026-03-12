<?php 
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

$donors_result = mysqli_query($conn, "SELECT donor_id, first_name, last_name FROM donors_users ORDER BY first_name ASC");
$donors = [];
while ($row = mysqli_fetch_assoc($donors_result)) {
    $donors[] = $row;
}
?>

<style>
.page-wrapper{
    padding:40px;
    display:flex;
    justify-content:center;
}
.card{
    width:600px;
    background:white;
    border-radius:10px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    padding:30px;
}
.card h2{
    margin-bottom:20px;
}
.form-group{
    margin-bottom:15px;
}
.form-group label{
    font-weight:600;
}
input,select{
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:6px;
}
button{
    background:#28a745;
    color:white;
    border:none;
    padding:10px 18px;
    border-radius:6px;
    cursor:pointer;
}
button:hover{background:#218838;}
.message{padding:12px;margin-bottom:15px;border-radius:6px;}
.error{background:#f8d7da;color:#721c24;}
.success{background:#d4edda;color:#155724;}

.back-btn{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    background:#6c757d;
    color:white;
    padding:8px 14px;
    border-radius:6px;
}
.back-btn:hover{background:#555;}

.search-container{position:relative;}
#search-results{
    position:absolute;
    width:100%;
    background:white;
    border:1px solid #ddd;
    max-height:200px;
    overflow-y:auto;
    display:none;
    z-index:100;
}
.search-item{
    padding:10px;
    cursor:pointer;
}
.search-item:hover{background:#f1f1f1;}
</style>

<div class="page-wrapper">
<div class="card">

<a href="../StaffSpecimenIndex.php" class="back-btn">← Back</a>
<h2>Add Donor Specimen</h2>

<?php if (isset($_SESSION['error'])): ?>
<div class="message error"><?= $_SESSION['error']; ?></div>
<?php unset($_SESSION['error']); endif; ?>

<?php if (isset($_SESSION['success'])): ?>
<div class="message success"><?= $_SESSION['success']; ?></div>
<?php unset($_SESSION['success']); endif; ?>

<form action="StaffSpecimenDonorStore.php" method="POST" autocomplete="off">

<input type="hidden" name="action" value="create_donor_specimen">

<div class="form-group">
<label>Donor Search</label>
<div class="search-container">
<input type="text" id="donor_search_input" placeholder="Search donor..." required>
<input type="hidden" name="donor_id" id="donor_id_hidden" required>
<div id="search-results"></div>
</div>
</div>

<div class="form-group">
<label>Unique Code</label>
<input type="text" name="unique_code" required>
</div>

<div class="form-group">
<label>Quantity</label>
<input type="number" name="quantity" min="1" required>
</div>

<div class="form-group">
<label>Price</label>
<input type="number" name="price" step="0.01" min="0" required>
</div>

<div class="form-group">
<label>Storage Location</label>
<input type="text" name="storage_location" required>
</div>

<div class="form-group">
<label>Expiration Date</label>
<input type="date" name="expiration_date" required>
</div>

<button type="submit">Add Specimen</button>

</form>
</div>
</div>

<script>
const donors = <?php echo json_encode($donors); ?>;
const searchInput = document.getElementById('donor_search_input');
const resultsDiv = document.getElementById('search-results');
const hiddenIdInput = document.getElementById('donor_id_hidden');

searchInput.addEventListener('input', function(){
const query=this.value.toLowerCase();
resultsDiv.innerHTML='';

if(query.length>0){
const matches=donors.filter(d=>(d.first_name+' '+d.last_name).toLowerCase().includes(query));

if(matches.length>0){
resultsDiv.style.display='block';

matches.forEach(match=>{
const div=document.createElement('div');
div.classList.add('search-item');
div.textContent=match.first_name+' '+match.last_name;

div.onclick=function(){
searchInput.value=match.first_name+' '+match.last_name;
hiddenIdInput.value=match.donor_id;
resultsDiv.style.display='none';
};

resultsDiv.appendChild(div);
});

}else{
resultsDiv.style.display='none';
}
}else{
resultsDiv.style.display='none';
}
});

document.addEventListener('click',function(e){
if(e.target!==searchInput){
resultsDiv.style.display='none';
}
});
</script>

<?php include('../../../../includes/footer.php'); ?>
