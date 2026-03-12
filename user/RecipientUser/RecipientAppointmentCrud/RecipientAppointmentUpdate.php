<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/head.php');
include('../../../includes/admin_header.php');

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . ROOT_URL . "login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);

// Fetch appointment + recipient info
$stmt = $conn->prepare("
    SELECT 
        a.appointment_id,
        a.user_id,
        a.appointment_date,
        a.type,
        a.status,
        a.created_at,
        r.recipient_id,
        r.first_name,
        r.last_name,
        r.profile_image,
        acc.username,
        acc.email
    FROM appointments a
    JOIN recipients_users r ON a.user_id = r.recipient_id
    JOIN accounts acc ON r.account_id = acc.account_id
    WHERE a.appointment_id = ? AND a.user_type = 'recipient'
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

$appointment = $result->fetch_assoc();

// Format date for datetime-local input
$formatted_date = date('Y-m-d\TH:i', strtotime($appointment['appointment_date']));
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="RecipientAppointmentIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Appointment List
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">

            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-green-900">Update Appointment</h2>
                    <p class="text-green-600 text-sm mt-1 font-medium">
                        Currently Editing: <span class="text-green-900"><?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>'s Appointment</span>
                    </p>
                </div>
                <div class="shrink-0 flex flex-col items-end gap-2">
                    <?php if (!empty($appointment['profile_image'])): ?>
                        <img src="../../../uploads/<?= htmlspecialchars($appointment['profile_image']); ?>"
                             class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md ring-1 ring-green-100">
                    <?php else: ?>
                        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold border-2 border-white shadow-sm">
                            <?= substr($appointment['first_name'], 0, 1) . substr($appointment['last_name'], 0, 1); ?>
                        </div>
                    <?php endif; ?>
                    <span class="text-[10px] font-black uppercase tracking-widest text-green-800 opacity-40">Appt #<?= $appointment['appointment_id']; ?></span>
                </div>
            </div>

            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-6 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-medium">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form action="RecipientAppointmentStore.php" method="POST" class="space-y-8">
                    <input type="hidden" name="action" value="RecipientAppointmentUpdate">
                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id']; ?>">

                    <!-- Locked Recipient Info -->
                    <div>
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-slate-300 rounded-full"></span> Locked Recipient Details
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Recipient Name</label>
                                <p class="text-sm font-bold text-slate-500 italic">
                                    <?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                                </p>
                            </div>
                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Email Address</label>
                                <p class="text-sm font-bold text-slate-500 italic"><?= htmlspecialchars($appointment['email']); ?></p>
                            </div>
                        </div>
                        <div class="mt-4 bg-gray-50 border border-gray-100 p-4 rounded-xl">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Created At</label>
                            <p class="text-sm font-bold text-slate-500 italic">
                                <?= date('F d, Y — h:i A', strtotime($appointment['created_at'])); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Appointment Details -->
                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Appointment Details
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Date & Time</label>
                                <input type="datetime-local" name="appointment_date"
                                    value="<?= htmlspecialchars($formatted_date); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Appointment Type</label>
                                <select name="type"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                    <option value="consultation" <?= $appointment['type'] === 'consultation' ? 'selected' : ''; ?>>Consultation</option>
                                    <option value="release"      <?= $appointment['type'] === 'release'      ? 'selected' : ''; ?>>Release</option>
                                    <option value="donation"     <?= $appointment['type'] === 'donation'     ? 'selected' : ''; ?>>Donation</option>
                                    <option value="storage"      <?= $appointment['type'] === 'storage'      ? 'selected' : ''; ?>>Storage</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Status
                        </h3>
                        <div class="w-full md:w-1/2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Appointment Status</label>
                            <select name="status"
                                class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                <option value="scheduled" <?= $appointment['status'] === 'scheduled'  ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="completed" <?= $appointment['status'] === 'completed'  ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?= $appointment['status'] === 'cancelled'  ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50 flex gap-4">
                        <button type="submit"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100">
                            Apply Updates
                        </button>
                        <a href="RecipientAppointmentIndex.php"
                           class="px-8 py-4 border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>
