<?php
session_start();

// Database connection
$host = 'localhost';
$username = "root";
$password = "";
$dbname = "ticket";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to automatically reject past tickets
function autoRejectPastTickets($pdo)
{
    $currentDateTime = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        UPDATE bus_sell 
        SET status = 'rejected' 
        WHERE status = 'pending' 
        AND CONCAT(journeydate, ' ', boarding_time) < :currentDateTime
    ");
    $stmt->execute(['currentDateTime' => $currentDateTime]);
}

// Handle accept/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bus_id']) && isset($_POST['action'])) {
    $busId = $_POST['bus_id'];
    $action = $_POST['action'];
    $newStatus = ($action === 'accept') ? 'accepted' : 'rejected';

    try {
        $stmt = $pdo->prepare("UPDATE bus_sell SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => $busId]);
        $_SESSION['message'] = "Ticket successfully " . $newStatus;
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error updating ticket status: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header("Location: " . $_SERVER['PHP_SELF'] . ($_GET['date'] ? "?date=" . $_GET['date'] : ""));
    exit();
}

// Run auto-rejection before fetching pending tickets
autoRejectPastTickets($pdo);

// Fetch pending bus records
$dateFilter = isset($_GET['date']) ? $_GET['date'] : null;
$query = "SELECT * FROM bus_sell WHERE status = 'pending'";
$params = [];

if ($dateFilter) {
    $query .= " AND DATE(journeydate) = :date";
    $params['date'] = $dateFilter;
}

$query .= " ORDER BY journeydate ASC, boarding_time ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pendingBusRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Bus Ticket Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --topbar-height: 60px;
            --primary-color: #343a40;
            --secondary-color: #495057;
            --accent-color: #ffc107;
            --text-light: #f8f9fa;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Sidebar styles */
        .sidebar {
            position: fixed;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--primary-color);
            color: white;
            transition: transform var(--transition-speed);
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transform: translateX(0);
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar-brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 15px;
            background-color: rgba(0, 0, 0, 0.2);
        }

        .sidebar-brand h2 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .sidebar-menu {
            padding: 0;
            list-style: none;
            margin-top: 20px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            transition: all var(--transition-speed);
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background-color: var(--secondary-color);
            border-left-color: var(--accent-color);
        }

        .sidebar-link i {
            margin-right: 15px;
            min-width: 20px;
        }

        /* Main content styles */
        .main-content {
            margin-left: 0;
            transition: margin-left var(--transition-speed);
            min-height: 100vh;
            padding-bottom: 60px;
        }

        .topbar {
            background-color: white;
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .user-info {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .container {
            padding: 15px;
        }

        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            70% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .btn-accept {
            background: linear-gradient(to right, #28a745, #218838);
            color: #fff;
            border: none;
        }

        .btn-accept:hover {
            background: linear-gradient(to right, #218838, #1e7e34);
        }

        .btn-reject {
            background: linear-gradient(to right, #dc3545, #c82333);
            color: #fff;
            border: none;
        }

        .btn-reject:hover {
            background: linear-gradient(to right, #c82333, #bd2130);
        }

        .table-responsive {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        .table th {
            background-color: #f8f9fa;
            font-size: 0.9rem;
            padding: 10px;
        }

        .table td {
            font-size: 0.85rem;
            padding: 10px;
            vertical-align: middle;
        }

        .footer {
            background-color: var(--primary-color);
            color: #fff;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 99;
        }

        /* Responsive design */
        @media (min-width: 992px) {
            .main-content {
                margin-left: var(--sidebar-width);
            }

            .sidebar.hidden+.main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: none;
            }
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.visible {
                transform: translateX(0);
            }

            .table th,
            .table td {
                font-size: 0.8rem;
                padding: 8px;
            }

            .btn-sm {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 10px;
            }

            .table th,
            .table td {
                font-size: 0.75rem;
                padding: 6px;
            }

            .btn-sm {
                font-size: 0.7rem;
                padding: 3px 6px;
            }

            h1 {
                font-size: 1.5rem;
            }
        }

        .logo-container {
            display: flex;
            align-items: center;
            padding: 15px;
        }

        .logo-container img {
            width: 40px;
            margin-right: 10px;
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="MetaTicket Logo">
            <h1 style="font-size: 1.5rem;">MetaTicket</h1>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_home.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="bus_admin.php" class="sidebar-link active"><i class="fas fa-bus"></i> Bus Ticket Uploads</a>
            </li>
            <li><a href="admin_bookings.php" class="sidebar-link"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="manage_users.php" class="sidebar-link"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_agency.php" class="sidebar-link"><i class="fas fa-building"></i> Bus Agencies</a></li>
            <li><a href="admin_wallet.php" class="sidebar-link"><i class="fas fa-wallet"></i> Wallet</a></li>
            <li><a href="homepage.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <button class="menu-toggle" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-info">
                <div class="user-avatar">A</div>
                <span class="ms-2 d-none d-md-inline">Admin</span>
            </div>
        </div>

        <!-- Content -->
        <div class="container my-4">
            <h1 class="text-center mb-4">Manage Bus Ticket Uploads</h1>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                    <?php
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filter Section -->
            <form method="GET" class="mb-4">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4 col-8">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" name="date" class="form-control"
                                value="<?php echo htmlspecialchars($filter_date); ?>">
                        </div>
                    </div>
                    <div class="col-md-2 col-4">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i
                                class="fas fa-filter me-2"></i>Apply Filter</button>
                    </div>
                    <?php if ($dateFilter): ?>
                        <div class="col-md-2 col-4">
                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary btn-sm w-100">Clear</a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-chair"></i> Seat No</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-bus"></i> Bus ID</th>
                            <th><i class="fas fa-signature"></i> Bus Name</th>
                            <th><i class="fas fa-calendar-alt"></i> Journey Date</th>
                            <th><i class="fas fa-map-pin"></i> From</th>
                            <th><i class="fas fa-map-marker-alt"></i> To</th>
                            <th><i class="fas fa-clock"></i> Start Time</th>
                            <th><i class="fas fa-rupee-sign"></i> Price</th>
                            <th><i class="fas fa-tools"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pendingBusRecords)) { ?>
                            <?php foreach ($pendingBusRecords as $record) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['id']); ?></td>
                                    <td><?php echo htmlspecialchars($record['seat_no']); ?></td>
                                    <td><?php echo htmlspecialchars($record['email']); ?></td>
                                    <td><?php echo htmlspecialchars($record['bus_id']); ?></td>
                                    <td><?php echo htmlspecialchars($record['busname']); ?></td>
                                    <td><?php echo htmlspecialchars($record['journeydate']); ?></td>
                                    <td><?php echo htmlspecialchars($record['fromplace']); ?></td>
                                    <td><?php echo htmlspecialchars($record['toplace']); ?></td>
                                    <td><?php echo htmlspecialchars($record['boarding_time']); ?></td>
                                    <td><?php echo htmlspecialchars($record['ticketprice']); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="bus_id"
                                                value="<?php echo htmlspecialchars($record['id']); ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-accept btn-sm">Accept</button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="bus_id"
                                                value="<?php echo htmlspecialchars($record['id']); ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-reject btn-sm">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="11" class="text-center">No pending bus ticket records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Back Button -->
            <div class="mt-4">
                <a href="javascript:history.back()" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>


    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menu-toggle');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('visible');
            sidebar.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && !sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('visible')) {
                sidebar.classList.remove('visible');
                sidebar.classList.add('hidden');
            }
        });

        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.getElementsByClassName('alert');
            for (let alert of alerts) {
                alert.style.display = 'none';
            }
        }, 5000);
    </script>
</body>

</html>