<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Fetch self storage users
$storage_result = mysqli_query($conn, "
    SELECT storage_user_id, first_name, last_name
    FROM self_storage_users
    ORDER BY first_name ASC
");
$storage_users = [];
while ($row = mysqli_fetch_assoc($storage_result)) {
    $storage_users[] = $row;
}
?>

<style>
.container { max-width: 600px; margin: 40px auto; padding: 30px; background:#fff; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); font-family: Arial,sans-serif; }
h2 { margin-bottom: 25px; color: #007bff; text-align: center; }

input, select, button {
    width: 100%; padding: 12px; margin: 10px 0; border-radius:6px; border:1px solid #ccc; box-sizing:border-box; font-size:14px;
}

button { background:#28a745; color:#fff; border:none; cursor:pointer; font-weight:bold; transition: background 0.2s; }
button:hover { background:#218838; }

.back-btn { display:inline-block; margin-bottom:20px; background:#6c757d; color:#fff; padding:8px 18px; border-radius:6px; text-decoration:none; }
.back-btn:hover { background:#5a6268; }

.message { padding:12px; margin-bottom:15px; border-radius:5px; font-size:14px; }
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }

.search-container { position: relative; }
.search-results { position:absolute; width:100%; max-height:200px; overflow-y:auto; background:#fff; border:1px solid #ccc; border-top:none; z-index:1000; display:none; border-radius:0 0 6px 6px; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
.search-item { padding:10px; cursor:pointer; border-bottom:1px solid #eee; }
.search-item:hover { background:#f0f0f0; }

label { font-weight:bold; margin-top:10px; display:block; }
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
<h2>Create Self-Storage Specimen Request</h2>

<form action="StaffSpecimenRequestSelfStorageStore.php" method="POST" autocomplete="off" enctype="multipart/form-data">
    <input type="hidden" name="action" value="create_specimen_request">

    <!-- Self Storage User Search -->
    <label>Search Self Storage User</label>
    <div class="search-container">
        <input type="text" id="storage_search_input" placeholder="Type name to search..." required>
        <input type="hidden" name="storage_user_id" id="storage_id_hidden" required>
        <div id="storage_search_results" class="search-results"></div>
    </div>

    <!-- Specimen Select -->
    <label>Select Specimen</label>
    <select name="specimen_id" id="specimenSelect" disabled required>
        <option value="">-- Select Self Storage User First --</option>
    </select>

    <!-- Requested Quantity -->
    <label>Requested Quantity</label>
    <input type="number" name="requested_quantity" min="1" required>

    <!-- Receipt Upload -->
    <label>Upload Receipt (optional)</label>
    <input type="file" name="receipt_image" accept="image/*">

    <button type="submit">Create Request</button>
</form>
</div>

<script>
const storageUsers = <?php echo json_encode($storage_users); ?>;
const storageInput = document.getElementById('storage_search_input');
const storageResults = document.getElementById('storage_search_results');
const storageHidden = document.getElementById('storage_id_hidden');
const specimenSelect = document.getElementById("specimenSelect");

storageInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    storageResults.innerHTML = '';
    if (query.length === 0) { storageResults.style.display='none'; return; }

    const matches = storageUsers.filter(u => (u.first_name + ' ' + u.last_name).toLowerCase().includes(query));
    if (matches.length === 0) { storageResults.style.display='none'; return; }

    storageResults.style.display = 'block';
    matches.forEach(u => {
        const div = document.createElement('div');
        div.classList.add('search-item');
        div.textContent = u.first_name + ' ' + u.last_name;
        div.onclick = () => {
            storageInput.value = u.first_name + ' ' + u.last_name;
            storageHidden.value = u.storage_user_id;
            storageResults.style.display = 'none';
            loadSpecimens(u.storage_user_id);
        };
        storageResults.appendChild(div);
    });
});

document.addEventListener('click', e => { if (e.target !== storageInput) storageResults.style.display='none'; });

function loadSpecimens(storageUserId) {
    specimenSelect.innerHTML = "<option>Loading...</option>";
    specimenSelect.disabled = true;

    fetch("GetSpecimenBySelfStorage.php?storage_user_id=" + storageUserId)
        .then(res => res.text())
        .then(data => {
            specimenSelect.innerHTML = data;
            specimenSelect.disabled = false;
        });
}
</script>

<?php include('../../../../includes/footer.php'); ?>
