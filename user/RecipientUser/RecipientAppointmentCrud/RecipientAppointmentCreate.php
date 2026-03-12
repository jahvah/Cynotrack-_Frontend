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

// Fetch all recipients for dropdown
$recipientQuery = "
    SELECT r.recipient_id, r.first_name, r.last_name, acc.email
    FROM recipients_users r
    JOIN accounts acc ON r.account_id = acc.account_id
    WHERE acc.status = 'active'
    ORDER BY r.first_name ASC
";
$recipients = mysqli_query($conn, $recipientQuery);
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="RecipientAppointmentIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Appointment List
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">

            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-green-900">Add New Appointment</h2>
                <p class="text-green-600 text-sm mt-1 font-medium">Schedule a new appointment for a recipient.</p>
            </div>

            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium">
                        <?= $_SESSION['error']; ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-6 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-medium">
                        <?= $_SESSION['success']; ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form action="RecipientAppointmentStore.php" method="POST" class="space-y-8">
                    <input type="hidden" name="action" value="RecipientAppointmentStore">

                    <!-- Recipient Selection -->
                    <div>
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Recipient
                        </h3>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Select Recipient</label>
                            <select name="recipient_id" required
                                class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                <option value="" disabled selected>-- Choose a Recipient --</option>
                                <?php while ($rec = mysqli_fetch_assoc($recipients)): ?>
                                    <option value="<?= $rec['recipient_id']; ?>">
                                        <?= htmlspecialchars($rec['first_name'] . ' ' . $rec['last_name']); ?> — <?= htmlspecialchars($rec['email']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
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
                                <input type="datetime-local" name="appointment_date" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Appointment Type</label>
                                <select name="type" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                    <option value="consultation" selected>Consultation</option>
                                    <option value="release">Release</option>
                                    <option value="donation">Donation</option>
                                    <option value="storage">Storage</option>
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
                            <select name="status" required
                                class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                <option value="scheduled" selected>Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50">
                        <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100 flex items-center justify-center gap-2">
                            <span>Schedule Appointment</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>
