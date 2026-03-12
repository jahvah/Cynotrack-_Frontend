<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);

// Fetch the donor appointment with full donor info
$stmt = $conn->prepare("
    SELECT
        a.appointment_id,
        a.appointment_date,
        a.status,
        a.type,
        a.created_at,
        d.first_name,
        d.last_name,
        d.profile_image,
        d.blood_type
    FROM appointments a
    JOIN donors_users d ON a.user_id = d.donor_id
    WHERE a.appointment_id = ? AND a.user_type = 'donor'
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

$appointment    = $result->fetch_assoc();
$formatted_date = date('Y-m-d\TH:i', strtotime($appointment['appointment_date']));

$statusColor = match($appointment['status']) {
    'scheduled' => 'bg-amber-100 text-amber-800 border-amber-200',
    'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'cancelled' => 'bg-red-100 text-red-800 border-red-200',
    default     => 'bg-gray-100 text-gray-800 border-gray-200',
};
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap');

  .staff-wrap { font-family: 'DM Sans', sans-serif; }
  .display-font { font-family: 'DM Serif Display', serif; }

  .fade-up { opacity:0; transform:translateY(16px); animation: fu .48s ease forwards; }
  @keyframes fu { to { opacity:1; transform:translateY(0); } }
  .d1{animation-delay:.06s} .d2{animation-delay:.12s}
  .d3{animation-delay:.18s} .d4{animation-delay:.24s}
  .d5{animation-delay:.30s}

  .form-input {
    width: 100%;
    padding: .75rem 1rem;
    border: 1.5px solid #bbf7d0;
    border-radius: 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: .9rem; color: #1a2e1a;
    background: white; outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .form-input:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.1); }

  .hint-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px; }
  .hint {
    font-size: .7rem; font-weight: 700; color: #16a34a;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    padding: 2px 8px; border-radius: 20px;
  }

  .submit-btn {
    flex: 1;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white; font-weight: 700; font-size: .9rem;
    padding: .95rem; border: none; border-radius: 14px; cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    box-shadow: 0 4px 16px rgba(21,128,61,.28);
    transition: all .2s;
  }
  .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 22px rgba(21,128,61,.38); }
</style>

<div class="staff-wrap min-h-screen bg-gradient-to-br from-green-50/60 via-white to-emerald-50/30 py-10 px-4">
  <div class="max-w-2xl mx-auto">

    <!-- Back -->
    <div class="fade-up mb-6">
      <a href="../StaffAppointmentIndex.php"
         class="inline-flex items-center gap-2 text-sm font-semibold text-green-700 hover:text-green-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Appointments
      </a>
    </div>

    <!-- Card -->
    <div class="fade-up d1 bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">

      <!-- Card Header -->
      <div class="relative bg-gradient-to-r from-green-800 to-emerald-700 px-8 py-6 overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px),
                                      radial-gradient(circle at 80% 20%, white 1px, transparent 1px);
                    background-size: 38px 38px;"></div>
        <div class="relative flex items-center justify-between gap-4">
          <div>
            <p class="text-green-200 text-[10px] font-bold uppercase tracking-widest mb-1">Staff Portal</p>
            <h2 class="display-font text-white text-2xl">Update Donor Appointment</h2>
            <p class="text-green-200/80 text-sm mt-1">Appt #<?= $appointment_id; ?></p>
          </div>
          <div class="shrink-0 flex flex-col items-end gap-2">
            <?php if (!empty($appointment['profile_image'])): ?>
              <img src="../../../../uploads/<?= htmlspecialchars($appointment['profile_image']); ?>"
                   class="w-14 h-14 rounded-full object-cover border-2 border-white/40 shadow-lg">
            <?php else: ?>
              <div class="w-14 h-14 rounded-full bg-white/20 border-2 border-white/30 flex items-center justify-center text-white font-bold text-lg display-font">
                <?= strtoupper(substr($appointment['first_name'],0,1).substr($appointment['last_name'],0,1)); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="p-8 space-y-7">

        <!-- Alerts -->
        <?php if (isset($_SESSION['error'])): ?>
          <div class="fade-up p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-xl font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
          <div class="fade-up p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-xl font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
          </div>
        <?php endif; ?>

        <form action="StaffAppointmentDonorStore.php" method="POST" class="space-y-7">
          <input type="hidden" name="action" value="update_donor_appointment">
          <input type="hidden" name="appointment_id" value="<?= $appointment_id; ?>">

          <!-- Locked Donor Info -->
          <div class="fade-up d2">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 flex items-center gap-2">
              <span class="w-2 h-2 bg-slate-300 rounded-full"></span> Locked Donor Details
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Donor Name</p>
                <p class="text-sm font-bold text-slate-500 italic">
                  <?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                </p>
              </div>
              <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Current Status</p>
                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $statusColor ?>">
                  <?= htmlspecialchars($appointment['status']); ?>
                </span>
              </div>
            </div>
            <div class="mt-3 bg-gray-50 border border-gray-100 rounded-xl p-4">
              <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Created At</p>
              <p class="text-sm font-bold text-slate-500 italic">
                <?= date('F d, Y — h:i A', strtotime($appointment['created_at'])); ?>
              </p>
            </div>
          </div>

          <!-- Editable Fields -->
          <div class="fade-up d3">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-green-800 mb-3 flex items-center gap-2">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span> Appointment Details
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-700 mb-2">Date & Time</label>
                <input type="datetime-local" name="appointment_date"
                       value="<?= htmlspecialchars($formatted_date); ?>"
                       class="form-input">
                <div class="hint-row">
                  <span class="hint">⏰ 7:00 AM – 7:00 PM</span>
                  <span class="hint">📅 Future dates only</span>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-700 mb-2">Appointment Type</label>
                <select name="appointment_type" required class="form-input cursor-pointer">
                  <option value="consultation" <?= $appointment['type'] === 'consultation' ? 'selected' : ''; ?>>Consultation</option>
                  <option value="donation"     <?= $appointment['type'] === 'donation'     ? 'selected' : ''; ?>>Donation</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Status -->
          <div class="fade-up d4">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-green-800 mb-3 flex items-center gap-2">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span> Status
            </h3>
            <div class="w-full sm:w-1/2">
              <label class="block text-[10px] font-bold uppercase tracking-wider text-green-700 mb-2">Appointment Status</label>
              <select name="status" required class="form-input cursor-pointer">
                <option value="scheduled" <?= $appointment['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                <option value="completed" <?= $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?= $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
              </select>
            </div>
          </div>

          <!-- Actions -->
          <div class="fade-up d5 pt-2 border-t border-green-50 flex gap-3">
            <button type="submit" class="submit-btn">Apply Updates</button>
            <a href="../StaffAppointmentIndex.php"
               class="px-6 py-3 border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition text-sm text-center self-stretch flex items-center">
              Cancel
            </a>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?php include('../../../../includes/footer.php'); ?>
