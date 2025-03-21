<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Meta Ticket Exchange</title>
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
            --danger-color: #ff4d4d;
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
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-menu {
            padding: 0;
            list-style: none;
            margin-top: 20px;
        }

        .sidebar-item {
            margin-bottom: 5px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            transition: all var(--transition-speed);
            border-left: 3px solid transparent;
            font-size: 1rem;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background-color: var(--secondary-color);
            border-left-color: var(--accent-color);
        }

        .sidebar-link i {
            margin-right: 15px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar-link .link-text {
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-footer {
            padding: 15px;
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.2);
        }

        /* Main content styles */
        .main-content {
            margin-left: 0;
            transition: margin-left var(--transition-speed);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            padding: 5px;
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

        .dashboard-container {
            padding: 15px;
            flex: 1;
        }

        /* Dashboard cards */
        .stat-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 15px;
            transition: transform var(--transition-speed);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: var(--accent-color);
        }

        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.85rem;
        }

        /* Recent booking table */
        .booking-table {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            overflow-x: auto;
        }

        .booking-table table {
            margin-bottom: 0;
            width: 100%;
        }

        .booking-table th {
            background-color: #f8f9fa;
            border-bottom-width: 1px;
            font-size: 0.9rem;
            padding: 10px;
        }

        .booking-table td {
            padding: 10px;
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Quick access cards */
        .quick-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 15px;
            transition: all var(--transition-speed);
            text-decoration: none;
            color: var(--primary-color);
            display: block;
        }

        .quick-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: var(--accent-color);
        }

        .quick-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .quick-card:hover i {
            color: var(--accent-color);
        }

        .quick-card h4 {
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        /* Responsive design */
        @media (min-width: 992px) {
            .main-content {
                margin-left: var(--sidebar-width);
            }

            .sidebar.hidden + .main-content {
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

            .main-content {
                margin-left: 0;
            }

            .sidebar-link .link-text {
                display: inline;
            }

            .stat-card {
                padding: 12px;
            }

            .stat-card .stat-value {
                font-size: 1.3rem;
            }

            .quick-card {
                padding: 12px;
            }

            .quick-card i {
                font-size: 1.8rem;
            }

            .quick-card h4 {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .dashboard-container {
                padding: 10px;
            }

            .stat-card {
                padding: 10px;
            }

            .stat-card .stat-value {
                font-size: 1.2rem;
            }

            .stat-card .stat-label {
                font-size: 0.75rem;
            }

            .quick-card {
                padding: 10px;
            }

            .quick-card i {
                font-size: 1.5rem;
                margin-bottom: 5px;
            }

            .quick-card h4 {
                font-size: 0.8rem;
            }

            .booking-table th,
            .booking-table td {
                font-size: 0.75rem;
                padding: 8px;
            }

            .btn-sm {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
        }

        /* Modal styles */
        .ticket-modal .modal-header {
            background-color: var(--primary-color);
            color: white;
        }

        .ticket-modal .modal-body {
            padding: 15px;
        }

        .ticket-modal .ticket-detail-row {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .ticket-modal .ticket-detail-row:last-child {
            border-bottom: none;
        }

        .ticket-modal .ticket-label {
            font-weight: 500;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .ticket-modal .ticket-value {
            font-weight: 600;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .ticket-modal .modal-dialog {
                margin: 0.5rem;
            }

            .ticket-modal .ticket-label,
            .ticket-modal .ticket-value {
                font-size: 0.8rem;
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
    <?php
    // Database connection (unchanged)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ticket";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql_tickets = "SELECT COUNT(*) as total_tickets FROM new_bookings";
    $result_tickets = $conn->query($sql_tickets);
    $total_tickets = $result_tickets->num_rows > 0 ? $result_tickets->fetch_assoc()["total_tickets"] : 0;

    $sql_users = "SELECT COUNT(*) as total_users FROM signup WHERE user_type = 2";
    $result_users = $conn->query($sql_users);
    $total_users = $result_users->num_rows > 0 ? $result_users->fetch_assoc()["total_users"] : 0;

    $sql_agencies = "SELECT COUNT(*) as total_agencies FROM signup WHERE user_type = 3";
    $result_agencies = $conn->query($sql_agencies);
    $total_agencies = $result_agencies->num_rows > 0 ? $result_agencies->fetch_assoc()["total_agencies"] : 0;

    $sql_revenue = "SELECT SUM(convenience_fee) as total_revenue FROM new_bookings";
    $result_revenue = $conn->query($sql_revenue);
    $total_revenue = $result_revenue->num_rows > 0 ? ($result_revenue->fetch_assoc()["total_revenue"] ?? 0) : 0;
    ?>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="MetaTicket Logo">
            <h1 style="font-size: 1.5rem;">MetaTicket</h1>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="#" class="sidebar-link active">
                    <i class="fas fa-home"></i>
                    <span class="link-text">Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="bus_admin.php" class="sidebar-link">
                    <i class="fas fa-bus"></i>
                    <span class="link-text">Bus Ticket Uploads</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="admin_bookings.php" class="sidebar-link">
                    <i class="fas fa-ticket-alt"></i>
                    <span class="link-text">Bookings</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="manage_users.php" class="sidebar-link">
                    <i class="fas fa-users"></i>
                    <span class="link-text">Users</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="manage_agency.php" class="sidebar-link">
                    <i class="fas fa-building"></i>
                    <span class="link-text">Bus Agencies</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="admin_wallet.php" class="sidebar-link">
                    <i class="fas fa-wallet"></i>
                    <span class="link-text">Wallet</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="homepage.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="link-text">Sign Out</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Top Navigation Bar -->
        <div class="topbar">
            <button class="menu-toggle" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-info">
                <div class="user-avatar">A</div>
                <span class="ms-2 d-none d-md-inline">Admin</span>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-container">
            <h1 class="mb-3" style="font-size: 1.5rem;">Dashboard</h1>

            <!-- Stats Cards Row -->
            <div class="row mb-4">
                <div class="col-6 col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                        <div class="stat-value"><?php echo $total_tickets; ?></div>
                        <div class="stat-label">Total Tickets</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-value"><?php echo $total_users; ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-building"></i></div>
                        <div class="stat-value"><?php echo $total_agencies; ?></div>
                        <div class="stat-label">Bus Agencies</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></div>
                        <div class="stat-label">Revenue</div>
                    </div>
                </div>
            </div>

            <!-- Quick Access & Recent Bookings -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3" style="font-size: 1.1rem;">Quick Access</h5>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="bus_admin.php" class="quick-card">
                                <i class="fas fa-bus"></i>
                                <h4>Bus Tickets</h4>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="admin_bookings.php" class="quick-card">
                                <i class="fas fa-ticket-alt"></i>
                                <h4>Bookings</h4>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="manage_users.php" class="quick-card">
                                <i class="fas fa-users"></i>
                                <h4>Users</h4>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="manage_agency.php" class="quick-card">
                                <i class="fas fa-building"></i>
                                <h4>Agencies</h4>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <h5 class="mb-3" style="font-size: 1.1rem;">Recent Bookings</h5>
                    <div class="booking-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-user"></i> Passenger</th>
                                    <th><i class="fas fa-route"></i> Journey</th>
                                    <th><i class="fas fa-calendar-alt"></i> Date</th>
                                    <th><i class="fas fa-rupee-sign"></i> Price</th>
                                    <th><i class="fas fa-info-circle"></i> Status</th>
                                    <th><i class="fas fa-tools"></i> Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT nb.id, nb.passenger_name, nb.from_location, nb.to_location, 
                                        nb.journey_date, nb.total_amount, nb.booking_status, 
                                        nb.booking_date, nb.seat_no, nb.bus_id, nb.convenience_fee, nb.payment_status,
                                        nb.phone_number, nb.boarding_time, nb.dropping_time, nb.passenger_email
                                        FROM new_bookings nb
                                        ORDER BY nb.booking_date DESC 
                                        LIMIT 5";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>#" . $row["id"] . "</td>";
                                        echo "<td>" . $row["passenger_name"] . "</td>";
                                        echo "<td>" . $row["from_location"] . " to " . $row["to_location"] . "</td>";
                                        echo "<td>" . date('M d', strtotime($row["journey_date"])) . "</td>";
                                        echo "<td>₹" . $row["total_amount"] . "</td>";
                                        $statusClass = $row["booking_status"] === "CONFIRMED" ? "status-confirmed" : ($row["booking_status"] === "PENDING" ? "status-pending" : "status-cancelled");
                                        echo "<td><span class='status-badge $statusClass'>" . $row["booking_status"] . "</span></td>";
                                        echo "<td><button class='btn btn-sm btn-outline-primary view-ticket' data-bs-toggle='modal' data-bs-target='#ticketModal' 
                                            data-id='" . $row["id"] . "' 
                                            data-passenger='" . htmlspecialchars($row["passenger_name"], ENT_QUOTES) . "' 
                                            data-from='" . htmlspecialchars($row["from_location"], ENT_QUOTES) . "' 
                                            data-to='" . htmlspecialchars($row["to_location"], ENT_QUOTES) . "' 
                                            data-date='" . date('M d, Y', strtotime($row["journey_date"])) . "' 
                                            data-amount='" . $row["total_amount"] . "' 
                                            data-status='" . $row["booking_status"] . "' 
                                            data-booking-date='" . date('M d, Y', strtotime($row["booking_date"])) . "'
                                            data-seats='" . htmlspecialchars($row["seat_no"], ENT_QUOTES) . "'
                                            data-bus-id='" . $row["bus_id"] . "'
                                            data-convenience-fee='" . $row["convenience_fee"] . "'
                                            data-payment-method='" . htmlspecialchars($row["payment_status"], ENT_QUOTES) . "'
                                            data-mobile='" . $row["phone_number"] . "'
                                            data-departure-time='" . $row["boarding_time"] . "'
                                            data-arrival-time='" . $row["dropping_time"] . "'
                                            data-email='" . htmlspecialchars($row["passenger_email"], ENT_QUOTES) . "'>View</button></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>No recent bookings found</td></tr>";
                                }
                                $conn->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <a href="admin_bookings.php" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Modal (unchanged structure, adjusted styles) -->
    <div class="modal fade ticket-modal" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketModalLabel">Ticket Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Booking ID:</div><div class="col-7 ticket-value" id="modal-ticket-id"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Passenger Name:</div><div class="col-7 ticket-value" id="modal-passenger"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">From:</div><div class="col-7 ticket-value" id="modal-from"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">To:</div><div class="col-7 ticket-value" id="modal-to"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Journey Date:</div><div class="col-7 ticket-value" id="modal-journey-date"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Departure Time:</div><div class="col-7 ticket-value" id="modal-departure-time"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Arrival Time:</div><div class="col-7 ticket-value" id="modal-arrival-time"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Bus ID:</div><div class="col-7 ticket-value" id="modal-bus-id"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Seats:</div><div class="col-7 ticket-value" id="modal-seats"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Email:</div><div class="col-7 ticket-value" id="modal-email"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Mobile:</div><div class="col-7 ticket-value" id="modal-mobile"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Booking Date:</div><div class="col-7 ticket-value" id="modal-booking-date"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Total Amount:</div><div class="col-7 ticket-value" id="modal-amount"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Convenience Fee:</div><div class="col-7 ticket-value" id="modal-convenience-fee"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Payment Method:</div><div class="col-7 ticket-value" id="modal-payment-method"></div></div></div>
                    <div class="ticket-detail-row"><div class="row"><div class="col-5 ticket-label">Status:</div><div class="col-7"><span class="status-badge" id="modal-status"></span></div></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const menuToggle = document.getElementById('menu-toggle');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('visible');
            sidebar.classList.toggle('hidden');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && !sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('visible')) {
                sidebar.classList.remove('visible');
                sidebar.classList.add('hidden');
            }
        });

        // Handle ticket view button clicks (unchanged logic, simplified)
        document.querySelectorAll('.view-ticket').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('modal-ticket-id').textContent = '#' + this.getAttribute('data-id');
                document.getElementById('modal-passenger').textContent = this.getAttribute('data-passenger');
                document.getElementById('modal-from').textContent = this.getAttribute('data-from');
                document.getElementById('modal-to').textContent = this.getAttribute('data-to');
                document.getElementById('modal-journey-date').textContent = this.getAttribute('data-date');
                document.getElementById('modal-amount').textContent = '₹' + this.getAttribute('data-amount');
                document.getElementById('modal-booking-date').textContent = this.getAttribute('data-booking-date');
                document.getElementById('modal-seats').textContent = this.getAttribute('data-seats');
                document.getElementById('modal-bus-id').textContent = this.getAttribute('data-bus-id');
                document.getElementById('modal-convenience-fee').textContent = '₹' + this.getAttribute('data-convenience-fee');
                document.getElementById('modal-payment-method').textContent = this.getAttribute('data-payment-method');
                document.getElementById('modal-mobile').textContent = this.getAttribute('data-mobile');
                document.getElementById('modal-departure-time').textContent = this.getAttribute('data-departure-time');
                document.getElementById('modal-arrival-time').textContent = this.getAttribute('data-arrival-time');
                document.getElementById('modal-email').textContent = this.getAttribute('data-email');

                const statusElement = document.getElementById('modal-status');
                const status = this.getAttribute('data-status');
                statusElement.textContent = status;
                statusElement.classList.remove('status-confirmed', 'status-pending', 'status-cancelled');
                statusElement.classList.add(status === 'CONFIRMED' ? 'status-confirmed' : status === 'PENDING' ? 'status-pending' : 'status-cancelled');
            });
        });
    </script>
</body>
</html>