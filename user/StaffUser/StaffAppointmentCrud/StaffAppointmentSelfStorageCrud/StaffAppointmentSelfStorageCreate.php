<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Fetch self-storage users for live search (active accounts only)
$storage_result = mysqli_query($conn, "
    SELECT s.storage_user_id, s.first_name, s.last_name, s.profile_image, a.email
    FROM self_storage_users s
    JOIN accounts a ON s.account_id = a.account_id
    WHERE a.status = 'active'
    ORDER BY s.first_name ASC
");
$storage_users = [];
while ($row = mysqli_fetch_assoc($storage_result)) {
    $storage_users[] = $row;
}
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

  /* Search dropdown */
  .search-wrap { position: relative; }
  #search-results {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: white;
    border: 1.5px solid #bbf7d0;
    border-radius: 12px;
    z-index: 50;
    max-height: 220px;
    overflow-y: auto;
    display: none;
    box-shadow: 0 8px 24px rgba(21,128,61,.12);
  }
  .search-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f0fdf4;
    transition: background .15s;
    font-size: .875rem;
  }
  .search-item:last-child { border-bottom: none; }
  .search-item:hover { background: #f0fdf4; }
  .search-item .initials {
    width: 32px; height: 32px; border-radius: 50%;
    background: #dcfce7; color: #15803d;
    font-weight: 700; font-size: .7rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .search-item .item-name { font-weight: 600; color: #1a2e1a; }
  .search-item .item-email { font-size: .7rem; color: #6b7280; }

  /* Selected user pill */
  #selected-user-pill {
    display: none;
    align-items: center; gap: 10px;
    padding: 10px 14px;
    background: #f0fdf4;
    border: 1.5px solid #86efac;
    border-radius: 12px;
    margin-top: 8px;
  }
  #selected-user-pill .pill-name { font-size: .875rem; font-weight: 700; color: #15803d; }
  #selected-user-pill button {
    margin-left: auto; background: none; border: none;
    color: #dc2626; cursor: pointer; font-size: .8rem; font-weight: 700;
    padding: 2px 6px; border-radius: 6px;
  }
  #selected-user-pill button:hover { background: #fee2e2; }

  /* Form inputs */
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
  .form-input::placeholder { color: #9ca3af; }

  .hint-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px; }
  .hint {
    font-size: .7rem; font-weight: 700; color: #16a34a;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    padding: 2px 8px; border-radius: 20px;
  }

  /* Submit */
  .submit-btn {
    width: 100%;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white; font-weight: 700; font-size: .95rem;
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
        <div class="relative">
          <p class="text-green-200 text-[10px] font-bold uppercase tracking-widest mb-1">Staff Portal</p>
          <h2 class="display-font text-white text-2xl">Add Self-Storage Appointment</h2>
          <p class="text-green-200/80 text-sm mt-1">Schedule a new appointment for a self-storage user.</p>
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

        <form action="StaffAppointmentSelfStorageStore.php" method="POST" autocomplete="off" class="space-y-7">
          <input type="hidden" name="action" value="create_storage_appointment">

          <!-- Storage User Search -->
          <div class="fade-up d2">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-green-800 mb-3 flex items-center gap-2">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span> Select Self-Storage User
            </h3>
            <div class="search-wrap">
              <input type="text" id="storage_search_input" class="form-input"
                     placeholder="Type name to search storage users…">
              <input type="hidden" name="storage_user_id" id="storage_id_hidden">
              <div id="search-results"></div>
            </div>

            <!-- Selected user pill -->
            <div id="selected-user-pill">
              <div class="initials" id="pill-initials"></div>
              <span class="pill-name" id="pill-name"></span>
              <button type="button" onclick="clearUser()">✕ Clear</button>
            </div>
          </div>

          <!-- Appointment Date & Type -->
          <div class="fade-up d3">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-green-800 mb-3 flex items-center gap-2">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span> Appointment Details
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-700 mb-2">Date & Time</label>
                <input type="datetime-local" name="appointment_date" required class="form-input">
                <div class="hint-row">
                  <span class="hint">⏰ 7:00 AM – 7:00 PM</span>
                  <span class="hint">📅 Future dates only</span>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-700 mb-2">Appointment Type</label>
                <select name="appointment_type" required class="form-input cursor-pointer">
                  <option value="consultation">Consultation</option>
                  <option value="storage">Storage</option>
                  <option value="release">Release</option>
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
              <label class="block text-[10px] font-bold uppercase tracking-wider text-green-700 mb-2">Initial Status</label>
              <select name="status" required class="form-input cursor-pointer">
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          <!-- Submit -->
          <div class="fade-up d5 pt-2 border-t border-green-50">
            <button type="submit" class="submit-btn">Schedule Appointment</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<script>
const storageUsers = <?= json_encode($storage_users); ?>;

const searchInput  = document.getElementById('storage_search_input');
const resultsDiv   = document.getElementById('search-results');
const hiddenInput  = document.getElementById('storage_id_hidden');
const pillEl       = document.getElementById('selected-user-pill');
const pillName     = document.getElementById('pill-name');
const pillInitials = document.getElementById('pill-initials');

function getInitials(first, last) {
  return ((first || '').charAt(0) + (last || '').charAt(0)).toUpperCase();
}

searchInput.addEventListener('input', function () {
  const q = this.value.toLowerCase().trim();
  resultsDiv.innerHTML = '';

  if (q.length < 1) { resultsDiv.style.display = 'none'; return; }

  const matches = storageUsers.filter(u =>
    (u.first_name + ' ' + u.last_name).toLowerCase().includes(q)
  );

  if (!matches.length) { resultsDiv.style.display = 'none'; return; }

  resultsDiv.style.display = 'block';
  matches.slice(0, 10).forEach(u => {
    const item = document.createElement('div');
    item.className = 'search-item';
    item.innerHTML = `
      <div class="initials">${getInitials(u.first_name, u.last_name)}</div>
      <div>
        <div class="item-name">${u.first_name} ${u.last_name}</div>
        ${u.email ? `<div class="item-email">${u.email}</div>` : ''}
      </div>`;
    item.onclick = () => selectUser(u);
    resultsDiv.appendChild(item);
  });
});

function selectUser(u) {
  searchInput.value        = u.first_name + ' ' + u.last_name;
  hiddenInput.value        = u.storage_user_id;
  resultsDiv.style.display = 'none';
  pillInitials.textContent = getInitials(u.first_name, u.last_name);
  pillName.textContent     = u.first_name + ' ' + u.last_name;
  pillEl.style.display     = 'flex';
  searchInput.readOnly     = true;
  searchInput.style.background = '#f0fdf4';
}

function clearUser() {
  searchInput.value        = '';
  hiddenInput.value        = '';
  pillEl.style.display     = 'none';
  searchInput.readOnly     = false;
  searchInput.style.background = '';
  searchInput.focus();
}

document.addEventListener('click', e => {
  if (e.target !== searchInput) resultsDiv.style.display = 'none';
});
</script>

<?php include('../../../../includes/footer.php'); ?>
