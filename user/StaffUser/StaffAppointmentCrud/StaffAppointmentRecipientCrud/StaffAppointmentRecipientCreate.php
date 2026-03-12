StaffAppointmentRecipientCrud/

StaffAppointmentRecipientCreate.php
<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Fetch recipients for search
$recipient_result = mysqli_query($conn, "SELECT recipient_id, first_name, last_name FROM recipients_users ORDER BY first_name ASC");
$recipients = [];
while ($row = mysqli_fetch_assoc($recipient_result)) {
    $recipients[] = $row;
}
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
    cursor: pointer;
}
.message { padding: 12px; margin-bottom: 15px; border-radius: 5px; }
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }

.back-btn {
    display: inline-block;
    padding: 8px 15px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-bottom: 15px;
}
.back-btn:hover { background: #333; }

.search-container { position: relative; }
#search-results {
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
.search-item {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}
.search-item:hover { background: #f0f0f0; }
</style>

<div class="container">
    <a href="../StaffAppointmentIndex.php" class="back-btn">← Back to Appointment Dashboard</a>
    <h2>Add Recipient Appointment</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="StaffAppointmentRecipientStore.php" method="POST" autocomplete="off">
        <input type="hidden" name="action" value="create_recipient_appointment">

        <label>Search Recipient</label>
        <div class="search-container">
            <input type="text" id="recipient_search_input" placeholder="Type name to search recipients..." required>
            <input type="hidden" name="recipient_id" id="recipient_id_hidden" required>
            <div id="search-results"></div>
        </div>

        <label>Appointment Date & Time</label>
        <input type="datetime-local" name="appointment_date" required>

        <label>Appointment Type</label>
        <select name="appointment_type" required>
        <option value="consultation">Consultation</option>
        <option value="release">Release</option>
        </select>  

        <label>Status</label>
        <select name="status" required>
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>

        <button type="submit">Add Recipient Appointment</button>
    </form>
</div>

<script>
// Pass PHP recipients array to JS
const recipients = <?php echo json_encode($recipients); ?>;

const searchInput = document.getElementById('recipient_search_input');
const resultsDiv = document.getElementById('search-results');
const hiddenIdInput = document.getElementById('recipient_id_hidden');

searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    resultsDiv.innerHTML = '';
    
    if (query.length > 0) {
        const matches = recipients.filter(r => 
            (r.first_name + ' ' + r.last_name).toLowerCase().includes(query)
        );

        if (matches.length > 0) {
            resultsDiv.style.display = 'block';
            matches.forEach(match => {
                const div = document.createElement('div');
                div.classList.add('search-item');
                div.textContent = match.first_name + ' ' + match.last_name;
                div.onclick = function() {
                    searchInput.value = match.first_name + ' ' + match.last_name;
                    hiddenIdInput.value = match.recipient_id;
                    resultsDiv.style.display = 'none';
                };
                resultsDiv.appendChild(div);
            });
        } else {
            resultsDiv.style.display = 'none';
        }
    } else {
        resultsDiv.style.display = 'none';
    }
});

// Hide results when clicking outside
document.addEventListener('click', function(e) {
    if (e.target !== searchInput) {
        resultsDiv.style.display = 'none';
    }
});
</script>

<?php include('../../../../includes/footer.php'); ?>

StaffAppointmentRecipientDelete.php
<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Make sure ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid appointment.";
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);
if ($appointment_id <= 0) {
    $_SESSION['error'] = "Invalid appointment.";
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

// Delete the recipient appointment
$stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ? AND user_type = 'recipient'");
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Recipient appointment deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete appointment.";
}

// Redirect back to the recipient appointment index
header("Location: ../StaffAppointmentIndex.php");
exit();
?>

StaffAppointmentRecipientStore.php
<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

/* ============================================================
   =============== CREATE RECIPIENT APPOINTMENT ===============
   ============================================================ */
if ($action === 'create_recipient_appointment') {

    $recipient_id = intval($_POST['recipient_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_type = $_POST['appointment_type'] ?? 'donation';
    $status = $_POST['status'] ?? 'scheduled';

    if ($recipient_id <= 0 || empty($appointment_date)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    $appointment_datetime = strtotime($appointment_date);
    $now = time();
    $date_only = date('Y-m-d', $appointment_datetime);
    $today_date = date('Y-m-d');

    // 1️⃣ Cannot book for today
    if ($date_only === $today_date) {
        $_SESSION['error'] = "You cannot create an appointment for the current date.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    // 2️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot create appointment for past date/time.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));

    // 3️⃣ Operating hours check (7AM–7PM)
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    // 4️⃣ Check for any upcoming appointment (excluding cancelled & completed)
    $stmt_upcoming = $conn->prepare("
        SELECT * FROM appointments
        WHERE user_type = 'recipient' 
          AND user_id = ? 
          AND appointment_date > NOW() 
          AND status != 'cancelled'
          AND status != 'completed'
    ");
    $stmt_upcoming->bind_param("i", $recipient_id);
    $stmt_upcoming->execute();
    $result_upcoming = $stmt_upcoming->get_result();

    if ($result_upcoming->num_rows > 0) {
        $_SESSION['error'] = "This recipient already has an upcoming appointment.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    // 5️⃣ Check if recipient already has appointment that same day
    $stmt_day = $conn->prepare("
        SELECT * FROM appointments 
        WHERE user_type = 'recipient' 
          AND user_id = ? 
          AND DATE(appointment_date) = ?
          AND status != 'cancelled'
    ");
    $stmt_day->bind_param("is", $recipient_id, $date_only);
    $stmt_day->execute();
    $result_day = $stmt_day->get_result();

    if ($result_day->num_rows > 0) {
        $_SESSION['error'] = "This recipient already has an appointment booked for this day.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    // 6️⃣ Check if hour slot already taken by another recipient
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE user_type = 'recipient' 
          AND appointment_date BETWEEN ? AND ?
          AND status != 'cancelled'
    ");
    $stmt_hour->bind_param("ss", $start_hour, $end_hour);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();

    if ($result_hour->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked for a recipient.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    // ✅ Insert appointment
    $stmt = $conn->prepare("
    INSERT INTO appointments (user_type, user_id, appointment_date, type, status)
    VALUES ('recipient', ?, ?, ?, ?)
");
$stmt->bind_param("isss", $recipient_id, $appointment_date, $appointment_type, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Recipient appointment created successfully.";
    } else {
        $_SESSION['error'] = "Failed to create appointment.";
    }

    header("Location: StaffAppointmentRecipientCreate.php");
    exit();
}


/* ============================================================
   =============== UPDATE RECIPIENT APPOINTMENT ===============
   ============================================================ */
   $new_type = $_POST['appointment_type'] ?? '';

   if ($action === 'update_recipient_appointment') {

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $new_date = $_POST['appointment_date'] ?? '';
    $new_status = $_POST['status'] ?? '';

    if ($appointment_id <= 0 || empty($new_date)) {
        $_SESSION['error'] = "Invalid appointment or missing date.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Fetch current appointment
    $stmt_curr = $conn->prepare("
       SELECT appointment_date, status, type, user_id
        FROM appointments 
        WHERE appointment_id = ? AND user_type = 'recipient'
    ");
    $stmt_curr->bind_param("i", $appointment_id);
    $stmt_curr->execute();
    $result_curr = $stmt_curr->get_result();

    if ($result_curr->num_rows === 0) {
        $_SESSION['error'] = "Appointment not found.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    $current = $result_curr->fetch_assoc();
    $recipient_id = $current['user_id'];

    $current_date = date('Y-m-d H:i', strtotime($current['appointment_date']));
    $new_date_normalized = date('Y-m-d H:i', strtotime($new_date));

if ($current_date === $new_date_normalized && $current['status'] === $new_status && $current['type'] === $new_type) {        $_SESSION['error'] = "No changes detected.";
        $_SESSION['error'] = "No changes detected.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    $appointment_datetime = strtotime($new_date);
    $now = time();
    $date_only = date('Y-m-d', $appointment_datetime);
    $today_date = date('Y-m-d');

    // 1️⃣ Cannot set for today
    if ($date_only === $today_date) {
        $_SESSION['error'] = "You cannot set an appointment for the current date.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 2️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot set appointment for past date/time.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));

    // 3️⃣ Operating hours check
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 4️⃣ Check for another upcoming appointment (exclude this one)
    $stmt_upcoming = $conn->prepare("
        SELECT * FROM appointments
        WHERE user_type = 'recipient'
          AND user_id = ?
          AND appointment_date > NOW()
          AND status != 'cancelled'
          AND status != 'completed'
          AND appointment_id != ?
    ");
    $stmt_upcoming->bind_param("ii", $recipient_id, $appointment_id);
    $stmt_upcoming->execute();
    $result_upcoming = $stmt_upcoming->get_result();

    if ($result_upcoming->num_rows > 0) {
        $_SESSION['error'] = "This recipient already has another upcoming appointment.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 5️⃣ Hour conflict check
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE appointment_date BETWEEN ? AND ?
          AND appointment_id != ?
          AND user_type = 'recipient'
          AND status != 'cancelled'
    ");
    $stmt_hour->bind_param("ssi", $start_hour, $end_hour, $appointment_id);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();

    if ($result_hour->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    // ✅ Update appointment
    $stmt = $conn->prepare("
        UPDATE appointments
        SET appointment_date = ?, type = ?, status = ?
        WHERE appointment_id = ? AND user_type = 'recipient'
    ");
    $stmt->bind_param("sssi", $new_date, $new_type, $new_status, $appointment_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Recipient appointment updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
    exit();
}
?>

StaffAppointmentRecipientUpdate.php
<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Check for appointment ID
if (!isset($_GET['id'])) {
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);

// Fetch the recipient appointment
$stmt = $conn->prepare("
    SELECT a.appointment_date, a.status, a.type, u.first_name, u.last_name
    FROM appointments a
    JOIN recipients_users u ON a.user_id = u.recipient_id
    WHERE a.appointment_id = ? AND a.user_type = 'recipient'
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

$appointment = $result->fetch_assoc();
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
label, select { display: block; margin-top: 15px; }
input, select { width: 100%; padding: 10px; margin: 10px 0; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
}
.locked { background:#eee; }
.error { background:#f8d7da; color:#721c24; padding:10px; }
.success { background:#d4edda; color:#155724; padding:10px; }

.back-btn {
    display: inline-block;
    margin-bottom: 15px;
    padding: 8px 12px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.back-btn:hover { background: #333; }
</style>

<div class="container">
    <h2>Update Recipient Appointment</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="StaffAppointmentRecipientStore.php" method="POST">
        <input type="hidden" name="action" value="update_recipient_appointment">
        <input type="hidden" name="appointment_id" value="<?= $appointment_id; ?>">

        <label>Recipient Name</label>
        <input type="text" value="<?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>" class="locked" disabled>

        <label>Appointment Date & Time</label>
        <input type="datetime-local" name="appointment_date"
            value="<?= date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>">


            <label>Appointment Type</label>
<select name="appointment_type" required>
    <option value="consultation" <?= $appointment['type']=='consultation'?'selected':'' ?>>Consultation</option>
    <option value="release" <?= $appointment['type']=='release'?'selected':'' ?>>Release</option>
</select>

        <label>Status</label>
        <select name="status">
            <option value="">Select status</option>
            <option value="scheduled" <?= $appointment['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
            <option value="completed" <?= $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="cancelled" <?= $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>

        <button type="submit">Update Recipient Appointment</button>
        <a href="../StaffAppointmentIndex.php" class="back-btn">← Back to Index</a>
    </form>
</div>

<?php include('../../../../includes/footer.php'); ?>

