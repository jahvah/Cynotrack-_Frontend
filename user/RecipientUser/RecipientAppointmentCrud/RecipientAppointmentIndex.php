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

// Fetch all recipient appointments with recipient info
$query = "
    SELECT 
        a.appointment_id,
        a.user_id,
        a.appointment_date,
        a.type,
        a.status,
        a.created_at,
        r.first_name,
        r.last_name,
        r.profile_image,
        acc.email,
        acc.username
    FROM appointments a
    JOIN recipients_users r ON a.user_id = r.recipient_id
    JOIN accounts acc ON r.account_id = acc.account_id
    WHERE a.user_type = 'recipient'
    ORDER BY a.appointment_date DESC
";

$result = mysqli_query($conn, $query);

// Handle success/error messages
$success = $_GET['success'] ?? null;
$error   = $_GET['error'] ?? null;
?>

<div class="min-h-screen bg-green-50/30 py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-7xl mx-auto bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden">

        <div class="p-6 sm:p-8 border-b border-green-50 bg-white">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-green-900">Recipient Appointments</h2>
                    <p class="text-green-600 font-medium text-sm mt-1">Manage all recipient consultation and release appointments.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="../AdminDashboard.php" class="px-4 py-2 text-sm font-semibold text-green-700 bg-white border border-green-200 rounded-lg hover:bg-green-50 transition">
                        ← Dashboard
                    </a>
                    <a href="RecipientAppointmentCreate.php" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-md shadow-green-100">
                        + Add Appointment
                    </a>
                </div>
            </div>
        </div>

        <?php if ($success === 'appointment_deleted'): ?>
            <div class="mx-6 mt-4 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-medium">
                Appointment deleted successfully.
            </div>
        <?php endif; ?>

        <?php if ($success === 'appointment_created'): ?>
            <div class="mx-6 mt-4 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-medium">
                Appointment created successfully.
            </div>
        <?php endif; ?>

        <?php if ($success === 'appointment_updated'): ?>
            <div class="mx-6 mt-4 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-medium">
                Appointment updated successfully.
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-green-50/50 border-b border-green-100">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Recipient</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Account</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Date & Time</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Type</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-50">
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-green-50/30 transition">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($row['profile_image'])): ?>
                                            <img src="../../../uploads/<?= htmlspecialchars($row['profile_image']); ?>"
                                                 class="w-10 h-10 rounded-full object-cover border border-green-200">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-xs">
                                                <?= htmlspecialchars(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                                            <div class="text-[10px] text-green-600 font-medium uppercase tracking-tight">Appt ID: #<?= htmlspecialchars($row['appointment_id']); ?></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 font-medium"><?= htmlspecialchars($row['email']); ?></div>
                                    <div class="text-xs text-green-600 font-bold">@<?= htmlspecialchars($row['username']); ?></div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-800">
                                        <?= date('M d, Y', strtotime($row['appointment_date'])); ?>
                                    </div>
                                    <div class="text-xs text-green-600 font-medium">
                                        <?= date('h:i A', strtotime($row['appointment_date'])); ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $typeColor = match($row['type']) {
                                            'consultation' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'release'      => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'donation'     => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'storage'      => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                            default        => 'bg-gray-100 text-gray-800 border-gray-200',
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $typeColor ?>">
                                        <?= htmlspecialchars($row['type']); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $statusColor = match($row['status']) {
                                            'scheduled'  => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'completed'  => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'cancelled'  => 'bg-red-100 text-red-800 border-red-200',
                                            default      => 'bg-gray-100 text-gray-800 border-gray-200',
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $statusColor ?>">
                                        <?= htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-4">
                                        <a href="RecipientAppointmentUpdate.php?id=<?= $row['appointment_id']; ?>"
                                           class="text-sm font-bold text-amber-600 hover:text-amber-700 transition">
                                            Edit
                                        </a>
                                        <a href="RecipientAppointmentDelete.php?id=<?= $row['appointment_id']; ?>"
                                           onclick="return confirm('Are you sure you want to delete this appointment?');"
                                           class="text-sm font-bold text-red-600 hover:text-red-700 transition">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-green-800/50 italic bg-green-50/10">
                                No recipient appointments found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="bg-green-50/30 px-8 py-4 border-t border-green-50 flex justify-between items-center">
            <span class="text-[10px] text-green-700 font-bold uppercase tracking-widest">System Log: Active</span>
            <span class="text-[10px] text-green-600 italic">Recipient Appointment Portal v1.0</span>
        </div>

    </div>
</div>

<?php include('../../../includes/footer.php'); ?>
