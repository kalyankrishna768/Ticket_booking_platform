<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Wallet - MetaTicket Admin</title>
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

        .wallet-summary {
            display: flex;
            justify-content: space-around;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary-card {
            text-align: center;
            padding: 15px;
        }

        .summary-card h4 {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .summary-card h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .add-money-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .transaction-credit {
            color: #28a745;
            font-weight: 600;
        }

        .transaction-debit {
            color: #dc3545;
            font-weight: 600;
        }

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

            .wallet-summary {
                flex-direction: column;
                gap: 15px;
            }

            .add-money-form {
                flex-direction: column;
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

            .summary-card h2 {
                font-size: 1.2rem;
            }

            .summary-card h4 {
                font-size: 0.9rem;
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
    $conn = new mysqli('localhost', 'root', '', 'ticket');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $added_amount = 0;
    $message = '';
    $message_type = '';

    $wallet_result = $conn->query("SELECT available_money FROM admin_wallet WHERE id = 1");
    if ($wallet_result && $wallet_result->num_rows > 0) {
        $wallet_data = $wallet_result->fetch_assoc();
        $available_money = $wallet_data['available_money'];
    } else {
        $available_money = 0;
    }

    $bus_result = $conn->query("SELECT SUM(ticketprice) AS total_revenue, COUNT(*) AS total_bookings FROM bookings");
    $bus_data = $bus_result->fetch_assoc();

    $train_result = $conn->query("SELECT SUM(ticketprice) AS total_revenue, COUNT(*) AS total_bookings FROM train_bookings");
    $train_data = $train_result->fetch_assoc();

    $total_revenue = $bus_data['total_revenue'] + $train_data['total_revenue'];
    $total_bookings = $bus_data['total_bookings'] + $train_data['total_bookings'];
    $platform_commission = $total_revenue * 0.1;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_money'])) {
        $added_amount = floatval($_POST['amount']);

        if ($added_amount <= 0) {
            $message = 'Please enter a valid amount greater than zero.';
            $message_type = 'danger';
        } else {
            $available_money += $added_amount;
            $description = "Manual wallet top-up";

            $stmt = $conn->prepare("UPDATE admin_wallet SET available_money = ? WHERE id = 1");
            $stmt->bind_param("d", $available_money);

            if ($stmt->execute()) {
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO admin_wallet_transactions (admin_wallet_id, transaction_type, amount, description) VALUES (1, 'credit', ?, ?)");
                $stmt->bind_param("ds", $added_amount, $description);

                if ($stmt->execute()) {
                    $message = 'Amount ₹' . number_format($added_amount, 2) . ' successfully added to wallet.';
                    $message_type = 'success';
                } else {
                    $message = 'Failed to record transaction: ' . $stmt->error;
                    $message_type = 'danger';
                }
            } else {
                $message = 'Failed to update wallet: ' . $stmt->error;
                $message_type = 'danger';
            }
            $stmt->close();
        }
    }

    $transactions_result = $conn->query("SELECT id, transaction_type, amount, transaction_date, description FROM admin_wallet_transactions ORDER BY transaction_date DESC LIMIT 10");

    $stats_query = "SELECT 
                    SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_credits,
                    SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as total_debits
                FROM admin_wallet_transactions";
    $stats_result = $conn->query($stats_query);
    if ($stats_result && $stats_result->num_rows > 0) {
        $stats_data = $stats_result->fetch_assoc();
        $total_credits = $stats_data['total_credits'] ?? 0;
        $total_debits = $stats_data['total_debits'] ?? 0;
    }
    ?>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="MetaTicket Logo">
            <h1 style="font-size: 1.5rem;">MetaTicket</h1>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_home.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="bus_admin.php" class="sidebar-link"><i class="fas fa-bus"></i> Bus Ticket Uploads</a></li>
            <li><a href="admin_bookings.php" class="sidebar-link"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="manage_users.php" class="sidebar-link"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_agency.php" class="sidebar-link"><i class="fas fa-building"></i> Bus Agencies</a></li>
            <li><a href="admin_wallet.php" class="sidebar-link active"><i class="fas fa-wallet"></i> Wallet</a></li>
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
    <h1 class="text-center mb-4">Wallet Management</h1>

    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="wallet-summary">
        <div class="summary-card">
            <h4><i class="fas fa-wallet me-2"></i>Available Balance</h4>
            <h2>₹<?php echo number_format($available_money, 2); ?></h2>
        </div>
        <div class="summary-card">
            <h4><i class="fas fa-plus-circle me-2"></i>Total Credits</h4>
            <h2>₹<?php echo number_format($total_credits, 2); ?></h2>
        </div>
        <div class="summary-card">
            <h4><i class="fas fa-minus-circle me-2"></i>Total Debits</h4>
            <h2>₹<?php echo number_format($total_debits, 2); ?></h2>
        </div>
    </div>

    <div class="table-responsive mb-4">
        <h4 class="mb-3">Add Money to Wallet</h4>
        <form method="POST" action="" class="add-money-form">
            <div class="input-group">
                <span class="input-group-text">₹</span>
                <input type="number" step="0.01" min="0" class="form-control" id="amount" name="amount" placeholder="Enter amount" required>
            </div>
            <button type="submit" name="add_money" class="btn btn-primary">Add Money</button>
        </form>
    </div>

    <div class="table-responsive">
        <h4 class="mb-3"><i class="fas fa-history me-2"></i>Recent Transactions</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-exchange-alt"></i> Type</th>
                    <th><i class="fas fa-rupee-sign"></i> Amount</th>
                    <th><i class="fas fa-calendar-alt"></i> Date & Time</th>
                    <th><i class="fas fa-info-circle"></i> Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($transactions_result && $transactions_result->num_rows > 0): ?>
                    <?php while ($transaction = $transactions_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $transaction['id']; ?></td>
                            <td><?php echo ucfirst($transaction['transaction_type']); ?></td>
                            <td class="<?php echo $transaction['transaction_type'] == 'credit' ? 'transaction-credit' : 'transaction-debit'; ?>">
                                <?php echo $transaction['transaction_type'] == 'credit' ? '+' : '-'; ?>₹<?php echo number_format($transaction['amount'], 2); ?>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($transaction['transaction_date'])); ?></td>
                            <td><?php echo $transaction['description']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No transactions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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

        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function (alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>

</html>