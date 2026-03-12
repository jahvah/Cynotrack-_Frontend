<?php
session_start();
include('../../includes/config.php');
include('../../includes/header.php');

// Not logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}

// Logged in but NOT staff
if ($_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}
?>

<style>
body {
    background: #f8f9fa;
    font-family: Arial, sans-serif;
}

.dashboard-container {
    max-width: 900px;
    margin: 50px auto;
    padding: 30px;
    text-align: center;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.dashboard-title {
    font-size: 32px;
    color: #007bff;
    margin-bottom: 40px;
}

.dashboard-btn {
    display: inline-block;
    width: 220px;
    padding: 20px 0;
    margin: 15px;
    font-size: 18px;
    font-weight: bold;
    text-decoration: none;
    color: white;
    background-color: #007bff;
    border-radius: 10px;
    transition: 0.3s;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.dashboard-btn:hover {
    background-color: #0056b3;
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

@media (max-width: 650px) {
    .dashboard-btn {
        width: 100%;
        margin: 10px 0;
    }
}
</style>

<div class="dashboard-container">
    <h1 class="dashboard-title">Staff Dashboard</h1>

    <!-- SPECIMEN MANAGEMENT -->
    <a href="StaffSpecimenCrud/StaffSpecimenIndex.php" class="dashboard-btn">
        Manage Specimens
    </a>

    <!-- APPOINTMENT MANAGEMENT -->
    <a href="StaffAppointmentCrud/StaffAppointmentIndex.php" class="dashboard-btn">
        Manage Appointments
    </a>

    <!-- SPECIMEN REQUESTS -->
    <a href="StaffSpecimenRequestCrud/StaffSpecimenRequestIndex.php" class="dashboard-btn">
        Manage Specimen Requests
    </a>

</div>

<?php include('../../includes/footer.php'); ?>
