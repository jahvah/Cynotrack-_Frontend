<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Fetch recipients
$recipient_result = mysqli_query($conn, "SELECT recipient_id, first_name, last_name FROM recipients_users ORDER BY first_name ASC");
$recipients = [];
while ($row = mysqli_fetch_assoc($recipient_result)) {
    $recipients[] = $row;
}

// Fetch donors
$donor_result = mysqli_query($conn, "SELECT donor_id, first_name, last_name FROM donors_users ORDER BY first_name ASC");
$donors = [];
while ($row = mysqli_fetch_assoc($donor_result)) {
    $donors[] = $row;
}
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
button { padding: 10px 15px; background: green; color: white; border: none; cursor: pointer; }
.message { padding: 12px; margin-bottom: 15px; border-radius: 5px; }
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }
.back-btn { display: inline-block; padding: 8px 15px; background: #555; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 15px; }
.back-btn:hover { background: #333; }

/* Search Results Styling */
.search-container { position: relative; }
.search-results {
    position: absolute;
    width: 100%;
    background: white;
    border: 1px solid #ccc;
    border-top: none;
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
    display: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.search-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
.search-item:hover { background: #f0f0f0; }
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
    <h2>Create Specimen Request</h2>

    <form action="StaffSpecimenRequestRecipientStore.php" method="POST" autocomplete="off">
        <input type="hidden" name="action" value="create_specimen_request">

        <!-- Recipient Search -->
        <label>Search Recipient</label>
        <div class="search-container">
            <input type="text" id="recipient_search_input" placeholder="Type name to search..." required>
            <input type="hidden" name="recipient_id" id="recipient_id_hidden" required>
            <div id="recipient_search_results" class="search-results"></div>
        </div>

        <!-- Donor Search -->
        <label>Search Donor</label>
        <div class="search-container">
            <input type="text" id="donor_search_input" placeholder="Type name to search..." required>
            <input type="hidden" name="donor_id" id="donor_id_hidden" required>
            <div id="donor_search_results" class="search-results"></div>
        </div>

        <!-- Specimen -->
        <label>Select Specimen</label>
        <select name="specimen_id" id="specimenSelect" required>
            <option value="">-- Select Donor First --</option>
        </select>

        <!-- Quantity -->
        <label>Requested Quantity</label>
        <input type="number" name="requested_quantity" min="1" required>

        <button type="submit">Create Request</button>
    </form>
</div>

<script>
// Pass PHP arrays to JS
const recipients = <?php echo json_encode($recipients); ?>;
const donors = <?php echo json_encode($donors); ?>;

// Recipient search
const recipientInput = document.getElementById('recipient_search_input');
const recipientResults = document.getElementById('recipient_search_results');
const recipientHidden = document.getElementById('recipient_id_hidden');

recipientInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    recipientResults.innerHTML = '';
    if (query.length > 0) {
        const matches = recipients.filter(r => (r.first_name + ' ' + r.last_name).toLowerCase().includes(query));
        if (matches.length > 0) {
            recipientResults.style.display = 'block';
            matches.forEach(r => {
                const div = document.createElement('div');
                div.classList.add('search-item');
                div.textContent = r.first_name + ' ' + r.last_name;
                div.onclick = () => {
                    recipientInput.value = r.first_name + ' ' + r.last_name;
                    recipientHidden.value = r.recipient_id;
                    recipientResults.style.display = 'none';
                };
                recipientResults.appendChild(div);
            });
        } else recipientResults.style.display = 'none';
    } else recipientResults.style.display = 'none';
});

// Donor search
const donorInput = document.getElementById('donor_search_input');
const donorResults = document.getElementById('donor_search_results');
const donorHidden = document.getElementById('donor_id_hidden');

donorInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    donorResults.innerHTML = '';
    if (query.length > 0) {
        const matches = donors.filter(d => (d.first_name + ' ' + d.last_name).toLowerCase().includes(query));
        if (matches.length > 0) {
            donorResults.style.display = 'block';
            matches.forEach(d => {
                const div = document.createElement('div');
                div.classList.add('search-item');
                div.textContent = d.first_name + ' ' + d.last_name;
                div.onclick = () => {
                    donorInput.value = d.first_name + ' ' + d.last_name;
                    donorHidden.value = d.donor_id;
                    donorResults.style.display = 'none';
                    loadSpecimens(d.donor_id); // auto-load specimens
                };
                donorResults.appendChild(div);
            });
        } else donorResults.style.display = 'none';
    } else donorResults.style.display = 'none';
});

// Hide dropdowns when clicking outside
document.addEventListener('click', e => {
    if (e.target !== recipientInput) recipientResults.style.display = 'none';
    if (e.target !== donorInput) donorResults.style.display = 'none';
});

// Keep your existing loadSpecimens function for donor
function loadSpecimens(donorId) {
    const specimenSelect = document.getElementById("specimenSelect");
    specimenSelect.innerHTML = "<option>Loading...</option>";
    fetch("GetSpecimenByDonor.php?donor_id=" + donorId)
        .then(res => res.text())
        .then(data => { specimenSelect.innerHTML = data; });
}
</script>

<?php include('../../../../includes/footer.php'); ?>