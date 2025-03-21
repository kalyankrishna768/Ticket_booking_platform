<?php
include 'config.php';
session_start();

// Initialize variables for filtering (same as original)
$filter_status = isset($_GET['status']) ? $_GET['status'] : "";
$filter_date = isset($_GET['date']) ? $_GET['date'] : "";
$search_term = isset($_GET['search']) ? $_GET['search'] : "";

// Build the SQL query (same as original)
$sql = "SELECT * FROM new_bookings WHERE 1=1";
$params = array();
$types = "";

if (!empty($filter_status)) {
    $sql .= " AND booking_status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_date)) {
    $sql .= " AND DATE(journey_date) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

if (!empty($search_term)) {
    $sql .= " AND (passenger_name LIKE ? OR passenger_email LIKE ? OR ticket_id LIKE ? OR from_location LIKE ? OR to_location LIKE ?)";
    $search_pattern = "%$search_term%";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $types .= "sssss";
}

$sql .= " ORDER BY booking_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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

        /* Sidebar styles (from first code) */
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

        /* Main content */
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

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
            padding: 6px 12px;
            border-radius: 50px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            padding: 6px 12px;
            border-radius: 50px;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            padding: 6px 12px;
            border-radius: 50px;
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
            <li><a href="bus_admin.php" class="sidebar-link"><i class="fas fa-bus"></i> Bus Ticket Uploads</a></li>
            <li><a href="admin_bookings.php" class="sidebar-link active"><i class="fas fa-ticket-alt"></i> Bookings</a>
            </li>
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
            <h1 class="text-center mb-4">Booking Management</h1>

            <!-- Filter Section -->
            <form method="GET" class="mb-4">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-filter"></i></span>
                            <select class="form-select" name="status">
                                <option value="">All Statuses</option>
                                <option value="CONFIRMED" <?php echo $filter_status == "CONFIRMED" ? "selected" : ""; ?>>
                                    Confirmed</option>
                                <option value="PENDING" <?php echo $filter_status == "PENDING" ? "selected" : ""; ?>>
                                    Pending</option>
                                <option value="CANCELLED" <?php echo $filter_status == "CANCELLED" ? "selected" : ""; ?>>
                                    Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" name="date" class="form-control"
                                value="<?php echo htmlspecialchars($filter_date); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search..."
                                value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i
                                class="fas fa-filter me-2"></i>Apply Filter</button>
                    </div>
                </div>
            </form>

            <!-- Bookings Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-ticket-alt"></i> Ticket ID</th>
                            <th><i class="fas fa-user"></i> Passenger</th>
                            <th><i class="fas fa-route"></i> Journey Details</th>
                            <th><i class="fas fa-calendar-alt"></i> Date & Time</th>
                            <th><i class="fas fa-rupee-sign"></i> Price</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status_class = "";
                                switch ($row["booking_status"]) {
                                    case "CONFIRMED":
                                        $status_class = "status-confirmed";
                                        break;
                                    case "PENDING":
                                        $status_class = "status-pending";
                                        break;
                                    case "CANCELLED":
                                        $status_class = "status-cancelled";
                                        break;
                                }
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["ticket_id"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["passenger_name"]) . "<br>" . htmlspecialchars($row["passenger_email"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["from_location"]) . " to " . htmlspecialchars($row["to_location"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["journey_date"]) . " " . htmlspecialchars($row["boarding_time"]) . "</td>";
                                echo "<td>₹" . number_format($row["total_amount"], 2) . "</td>";
                                echo "<td><span class='$status_class'>" . htmlspecialchars($row["booking_status"]) . "</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No bookings found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>


        </div>

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
        </script>
</body>

</html>

<?php
$conn->close();
?>