<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}

$recipient_id = $_SESSION['role_user_id'];

$stmt = $conn->prepare("
    SELECT r.*, a.status AS account_status
    FROM recipients_users r
    INNER JOIN accounts a ON r.account_id = a.account_id
    WHERE r.recipient_id = ?
");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash_message'] = "Account does not exist. Please contact admin.";
    unset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']);
    header("Location: ../login.php");
    exit;
}

$recipient = $result->fetch_assoc();

if ($recipient['account_status'] !== 'active') {
    $_SESSION['flash_message'] = $recipient['account_status'] === 'inactive'
        ? "Your account is inactive. Please contact admin."
        : "Your account is pending approval.";
    unset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']);
    header("Location: ../login.php");
    exit;
}

// Fetch donors
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $sql  = "SELECT donor_id, first_name, last_name, profile_image, blood_type, ethnicity
             FROM donors_users
             WHERE evaluation_status = 'approved'
             AND (first_name LIKE ? OR last_name LIKE ?)
             ORDER BY donor_id ASC";
    $stmt = mysqli_prepare($conn, $sql);
    $like = "%$search%";
    mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    mysqli_stmt_execute($stmt);
    $donors = mysqli_stmt_get_result($stmt);
} else {
    $donors = mysqli_query($conn, "
        SELECT donor_id, first_name, last_name, profile_image, blood_type, ethnicity
        FROM donors_users
        WHERE evaluation_status = 'approved'
        ORDER BY donor_id ASC
    ");
}

// Count stats
$totalDonors   = mysqli_num_rows($donors);
mysqli_data_seek($donors, 0);

$pendingResult = $conn->prepare("SELECT COUNT(*) AS cnt FROM specimen_requests WHERE recipient_id=? AND status='pending'");
$pendingResult->bind_param("i", $recipient_id);
$pendingResult->execute();
$pendingCount = $pendingResult->get_result()->fetch_assoc()['cnt'] ?? 0;

$fulfilledResult = $conn->prepare("SELECT COUNT(*) AS cnt FROM specimen_requests WHERE recipient_id=? AND status='fulfilled'");
$fulfilledResult->bind_param("i", $recipient_id);
$fulfilledResult->execute();
$fulfilledCount = $fulfilledResult->get_result()->fetch_assoc()['cnt'] ?? 0;
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Nunito:wght@300;400;500;600;700;800&display=swap');

  .recipient-wrap { font-family: 'Nunito', sans-serif; }
  .display-font   { font-family: 'Playfair Display', serif; }

  .fade-up { opacity:0; transform:translateY(20px); animation: fuAnim .5s ease forwards; }
  @keyframes fuAnim { to { opacity:1; transform:translateY(0); } }
  .delay-1 { animation-delay:.08s; }
  .delay-2 { animation-delay:.16s; }
  .delay-3 { animation-delay:.24s; }
  .delay-4 { animation-delay:.32s; }
  .delay-5 { animation-delay:.40s; }

  /* Hero */
  .hero-banner {
    background: linear-gradient(135deg, #052e16 0%, #14532d 45%, #166534 100%);
    position: relative; overflow: hidden;
  }
  .hero-banner::before {
    content: '';
    position: absolute; inset: 0;
    background-image:
      radial-gradient(circle at 15% 40%, rgba(255,255,255,.06) 0%, transparent 40%),
      radial-gradient(circle at 85% 70%, rgba(134,239,172,.08) 0%, transparent 40%);
  }
  .hero-banner::after {
    content: '';
    position: absolute; bottom: -1px; left: 0; right: 0; height: 40px;
    background: linear-gradient(to bottom right, transparent 49%, #f0fdf4 50%);
  }

  /* Stat card */
  .stat-pill {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(6px);
    border-radius: 14px;
    transition: background .2s;
  }
  .stat-pill:hover { background: rgba(255,255,255,.18); }

  /* Search bar */
  .search-wrap input {
    border: 1.5px solid #bbf7d0;
    border-radius: 14px 0 0 14px;
    padding: 0.75rem 1.25rem;
    font-family: 'Nunito', sans-serif;
    font-size: .9rem;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    width: 100%;
    background: white;
  }
  .search-wrap input:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
  .search-wrap button {
    border-radius: 0 14px 14px 0;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white; font-weight: 700;
    padding: 0 1.5rem;
    border: none; cursor: pointer;
    font-family: 'Nunito', sans-serif;
    font-size: .9rem;
    transition: background .2s;
  }
  .search-wrap button:hover { background: linear-gradient(135deg, #15803d, #166534); }

  /* Donor card */
  .donor-card {
    background: white;
    border: 1px solid #d1fae5;
    border-radius: 18px;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    cursor: pointer;
  }
  .donor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 40px rgba(21,128,61,.14);
    border-color: #86efac;
  }
  .donor-card:hover .view-btn { background: #15803d; }

  .donor-img-wrap {
    position: relative; height: 180px; overflow: hidden;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
  }
  .donor-img-wrap img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s ease;
  }
  .donor-card:hover .donor-img-wrap img { transform: scale(1.05); }

  .donor-initials {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; font-weight: 700; color: #15803d;
    font-family: 'Playfair Display', serif;
  }

  .blood-badge {
    position: absolute; top: 10px; right: 10px;
    background: rgba(255,255,255,.9);
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 3px 8px;
    font-size: .65rem; font-weight: 800;
    color: #15803d; text-transform: uppercase; letter-spacing: .05em;
  }

  .view-btn {
    display: block; text-align: center;
    background: #16a34a; color: white;
    font-weight: 700; font-size: .82rem;
    padding: .75rem 1rem;
    text-decoration: none;
    transition: background .2s;
  }

  /* Avatar ring */
  .avatar-ring {
    width: 80px; height: 80px; border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,.4);
    box-shadow: 0 0 0 3px rgba(134,239,172,.3);
  }
  .avatar-placeholder {
    width: 80px; height: 80px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    border: 3px solid rgba(255,255,255,.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; font-weight: 700; color: white;
    font-family: 'Playfair Display', serif;
  }
</style>

<div class="recipient-wrap min-h-screen" style="background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);">

  <!-- ═══ HERO BANNER ═══ -->
  <div class="hero-banner px-6 pt-10 pb-14 md:px-12">
    <div class="max-w-5xl mx-auto relative z-10">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-8">

        <!-- Greeting -->
        <div class="flex items-center gap-5 fade-up">
          <?php if (!empty($recipient['profile_image'])): ?>
            <img src="../../uploads/<?= htmlspecialchars($recipient['profile_image']); ?>"
                 class="avatar-ring" alt="Profile">
          <?php else: ?>
            <div class="avatar-placeholder">
              <?= strtoupper(substr($recipient['first_name'],0,1).substr($recipient['last_name'],0,1)); ?>
            </div>
          <?php endif; ?>
          <div>
            <p class="text-green-300 text-xs font-bold uppercase tracking-widest mb-0.5">Recipient Portal</p>
            <h1 class="display-font text-white text-3xl leading-tight">
              Welcome back,<br><?= htmlspecialchars($recipient['first_name']); ?>
            </h1>
          </div>
        </div>

        <!-- Edit Profile Button -->
        <a href="RecipientEditProfile.php"
           class="fade-up delay-2 inline-flex items-center gap-2 px-5 py-2.5 bg-white/15 hover:bg-white/25 border border-white/25 rounded-xl text-white text-sm font-bold transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          Edit Profile
        </a>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-3 gap-3 fade-up delay-3">
        <div class="stat-pill px-4 py-3 text-center">
          <div class="text-2xl font-bold text-white display-font"><?= $totalDonors; ?></div>
          <div class="text-green-300 text-[10px] font-bold uppercase tracking-widest mt-0.5">Donors</div>
        </div>
        <div class="stat-pill px-4 py-3 text-center">
          <div class="text-2xl font-bold text-white display-font"><?= $pendingCount; ?></div>
          <div class="text-green-300 text-[10px] font-bold uppercase tracking-widest mt-0.5">Pending</div>
        </div>
        <div class="stat-pill px-4 py-3 text-center">
          <div class="text-2xl font-bold text-white display-font"><?= $fulfilledCount; ?></div>
          <div class="text-green-300 text-[10px] font-bold uppercase tracking-widest mt-0.5">Fulfilled</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══ MAIN CONTENT ═══ -->
  <div class="max-w-5xl mx-auto px-4 md:px-6 py-10 space-y-8">

    <!-- Flash messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
      <div class="fade-up p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl font-semibold">
        <?= htmlspecialchars($_SESSION['flash_message']); unset($_SESSION['flash_message']); ?>
      </div>
    <?php endif; ?>

    <!-- Profile Info Card -->
    <div class="fade-up delay-2 bg-white border border-green-100 rounded-2xl shadow-sm shadow-green-50 overflow-hidden">
      <div class="bg-green-50/60 border-b border-green-100 px-6 py-4">
        <h3 class="text-xs font-black uppercase tracking-widest text-green-700 flex items-center gap-2">
          <span class="w-2 h-2 bg-green-500 rounded-full"></span> My Profile
        </h3>
      </div>
      <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p class="text-[10px] font-bold uppercase tracking-wider text-green-600 mb-1">Full Name</p>
          <p class="text-base font-bold text-gray-800">
            <?= htmlspecialchars($recipient['first_name'] . ' ' . $recipient['last_name']); ?>
          </p>
        </div>
        <div>
          <p class="text-[10px] font-bold uppercase tracking-wider text-green-600 mb-1">Preferences</p>
          <p class="text-sm text-gray-600 leading-relaxed">
            <?= nl2br(htmlspecialchars($recipient['preferences'] ?? 'None specified.')); ?>
          </p>
        </div>
      </div>
    </div>

    <!-- Search + Donors -->
    <div class="fade-up delay-3">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
        <div>
          <h2 class="display-font text-2xl text-green-900">Available Donors</h2>
          <?php if (!empty($search)): ?>
            <p class="text-sm text-green-600 mt-1 font-medium">
              Results for "<strong><?= htmlspecialchars($search); ?></strong>"
              — <a href="RecipientDashboard.php" class="underline hover:text-green-800">clear</a>
            </p>
          <?php else: ?>
            <p class="text-sm text-green-600 mt-1 font-medium">Browse approved donors below</p>
          <?php endif; ?>
        </div>

        <!-- Search form -->
        <form method="GET" class="search-wrap flex w-full sm:w-80">
          <input type="text" name="search"
                 value="<?= htmlspecialchars($search); ?>"
                 placeholder="Search by name…">
          <button type="submit">Search</button>
        </form>
      </div>

      <!-- Donor Grid -->
      <?php if ($donors && mysqli_num_rows($donors) > 0): ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <?php $i = 0; while ($row = mysqli_fetch_assoc($donors)): $i++; ?>
            <div class="donor-card fade-up" style="animation-delay: <?= 0.05 * $i; ?>s">

              <!-- Image Section -->
              <div class="donor-img-wrap">
                <?php if (!empty($row['profile_image'])): ?>
                  <img src="../../uploads/<?= htmlspecialchars($row['profile_image']); ?>"
                       alt="<?= htmlspecialchars($row['first_name']); ?>">
                <?php else: ?>
                  <div class="donor-initials">
                    <?= strtoupper(substr($row['first_name'],0,1).substr($row['last_name'],0,1)); ?>
                  </div>
                <?php endif; ?>

                <?php if (!empty($row['blood_type'])): ?>
                  <span class="blood-badge"><?= htmlspecialchars($row['blood_type']); ?></span>
                <?php endif; ?>
              </div>

              <!-- Info Section -->
              <div class="px-4 py-3">
                <p class="text-sm font-bold text-gray-900 leading-tight mb-0.5">
                  <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                </p>
                <?php if (!empty($row['ethnicity'])): ?>
                  <p class="text-[10px] text-green-600 font-semibold uppercase tracking-wide">
                    <?= htmlspecialchars($row['ethnicity']); ?>
                  </p>
                <?php endif; ?>
              </div>

              <a href="RecipientSpecimenRequest/RecipientDonorRequestIndex.php?id=<?= intval($row['donor_id']); ?>"
                 class="view-btn">View Profile →</a>
            </div>
          <?php endwhile; ?>
        </div>

      <?php else: ?>
        <div class="text-center py-16 bg-white border border-green-100 rounded-2xl">
          <div class="text-5xl mb-4">🔍</div>
          <p class="text-green-800 font-bold text-lg display-font">No donors found</p>
          <p class="text-green-600 text-sm mt-1">
            <?= !empty($search) ? 'Try a different search term.' : 'Check back later for available donors.'; ?>
          </p>
        </div>
      <?php endif; ?>
    </div>

  </div><!-- /content -->
</div>

<?php include("../../includes/footer.php"); ?>
