<?php
session_start();
include 'config.php'; // Database connection

// Check if Super Admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'super_admin') {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch total agencies
$agencyCount = $conn->query("SELECT COUNT(*) as total FROM agencies")->fetch_assoc()['total'];

// Fetch total buses
$busCount = $conn->query("SELECT COUNT(*) as total FROM buses")->fetch_assoc()['total'];

// Fetch total routes
$routeCount = $conn->query("SELECT COUNT(*) as total FROM routes")->fetch_assoc()['total'];

// Fetch total bookings (confirmed only)
$bookingCount = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE booking_status='Confirmed'")->fetch_assoc()['total'];

// Fetch total revenue
$revenue = $conn->query("SELECT SUM(amount) as total FROM payments")->fetch_assoc()['total'] ?? 0;

// Fetch top agencies based on bookings
$topAgencies = $conn->query("SELECT a.name, COUNT(b.id) as total 
                             FROM agencies a 
                             JOIN bookings b ON a.id = b.agency_id 
                             WHERE b.booking_status = 'Confirmed' 
                             GROUP BY a.name 
                             ORDER BY total DESC 
                             LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
</head>
<body class="bg-gray-100">

<!-- Sidebar -->
<div class="flex h-screen">
    <aside class="w-64 bg-blue-800 text-white p-6">
        <h2 class="text-2xl font-bold">Super Admin</h2>
        <nav class="mt-6">
            <a href="superadmin.php" class="block py-2 px-4 bg-blue-900 rounded">Dashboard</a>
            <a href="manage_agencies.php" class="block py-2 px-4 hover:bg-blue-700">Manage Agencies</a>
            <a href="manage_users.php" class="block py-2 px-4 hover:bg-blue-700">Manage Users</a>
            <a href="manage_buses.php" class="block py-2 px-4 hover:bg-blue-700">Manage Buses</a>
            <a href="logout.php" class="block py-2 px-4 hover:bg-red-600">Logout</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6">
        <h1 class="text-3xl font-semibold mb-6">Dashboard Overview</h1>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-gray-600">Total Agencies</h3>
                <p class="text-2xl font-bold"><?php echo $agencyCount; ?></p>
            </div>
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-gray-600">Total Buses</h3>
                <p class="text-2xl font-bold"><?php echo $busCount; ?></p>
            </div>
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-gray-600">Total Routes</h3>
                <p class="text-2xl font-bold"><?php echo $routeCount; ?></p>
            </div>
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-gray-600">Total Bookings</h3>
                <p class="text-2xl font-bold"><?php echo $bookingCount; ?></p>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-green-500 text-white p-6 rounded shadow mb-6">
            <h3 class="text-lg">Total Revenue</h3>
            <p class="text-3xl font-bold">₹<?php echo number_format($revenue, 2); ?></p>
        </div>

        <!-- Top Agencies Chart -->
        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-lg font-semibold mb-4">Top Performing Agencies</h3>
            <canvas id="topAgenciesChart"></canvas>
        </div>
    </main>
</div>

<script>
    // Top Agencies Chart
    new Chart(document.getElementById('topAgenciesChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($topAgencies, 'name')); ?>,
            datasets: [{
                label: 'Total Bookings',
                data: <?php echo json_encode(array_column($topAgencies, 'total')); ?>,
                backgroundColor: '#3B82F6'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
