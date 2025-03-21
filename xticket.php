<?php
include 'config.php';
session_start();
$username = $_SESSION['username'];
$user_email = $_SESSION['email'];

// Get current date and time
$currentDateTime = date('Y-m-d H:i:s');

// Modified query to check both date and time
$query = "SELECT * FROM bus_sell 
          WHERE CONCAT(journeydate, ' ', boarding_time) > ? 
          AND status = 'accepted'
          ORDER BY journeydate ASC, boarding_time ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $currentDateTime);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Tickets - MetaTicket</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style/style.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --background-color: #f1f5f9;
            --card-background: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --success-color: #10b981;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --rounded-sm: 0.375rem;
            --rounded-md: 0.5rem;
            --rounded-lg: 1rem;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
        }

        .navbar {
            background-color: #001F3F;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        .navbar h1 {
            margin: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .ticket-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .filters {
            background: var(--card-background);
            border-radius: var(--rounded-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .filters form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .filters input {
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: var(--rounded-md);
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .filters input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .ticket-card {
            background: var(--card-background);
            border-radius: var(--rounded-lg);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .ticket-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .ticket-info h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .ticket-details {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .ticket-details span {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .ticket-details i {
            color: var(--accent-color);
            font-size: 1rem;
        }

        .price {
            color: var(--success-color) !important;
            font-size: 1.5rem !important;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .buy-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--rounded-md);
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
            text-decoration: none;
        }

        .buy-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .no-tickets {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
            font-size: 1.125rem;
            grid-column: 1 / -1;
            background: var(--card-background);
            border-radius: var(--rounded-lg);
            box-shadow: var(--shadow-sm);
        }

        .section-title {
            font-size: 1.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: var(--text-primary);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .reset-btn {
            background: #e2e8f0;
            color: var(--text-primary);
        }

        .reset-btn:hover {
            background: #cbd5e1;
        }

        @media (max-width: 768px) {
            .ticket-container {
                padding: 1rem;
            }
            
            .filters form {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }

        .back-link {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: var(--primary-color);
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="user-profile">
        <img src="assets/images/logo.png" alt="MetaTicket Logo">
        <h1>MetaTicket</h1>
    </div>
    <div class="nav-links">
        
    </div>
</div>

<div class="ticket-container">
<a href="#" class="back-link" onclick="window.history.back();">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back
    </a>
    <h2 class="section-title">Available Tickets</h2>
    
    <div class="filters">
        <form method="POST" action="xticketbuy.php">
            <div class="filter-group">
                <label for="date">Journey Date</label>
                <input type="date" id="date" name="date" 
                       min="<?php echo date('Y-m-d'); ?>" 
                       value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>">
            </div>
            <div class="filter-group">
                <label for="from">From</label>
                <input type="text" id="from" name="from" placeholder="Departure City" 
                       value="<?php echo isset($_GET['from']) ? htmlspecialchars($_GET['from']) : ''; ?>">
            </div>
            <div class="filter-group">
                <label for="to">To</label>
                <input type="text" id="to" name="to" placeholder="Destination City" 
                       value="<?php echo isset($_GET['to']) ? htmlspecialchars($_GET['to']) : ''; ?>">
            </div>
            <div class="action-buttons">
                <button type="submit" class="buy-btn">Search Tickets</button>
                <a href="ticketx.php" class="buy-btn reset-btn">Reset Filters</a>
            </div>

        </form>
    </div>

    <div class="ticket-grid">
        <?php
        if ($result->num_rows > 0) {
            $hasValidTickets = false;
            
            while ($row = $result->fetch_assoc()) {
                // Skip if filters don't match
                if (isset($_GET['date']) && !empty($_GET['date']) && $_GET['date'] != $row['journeydate']) continue;
                if (isset($_GET['from']) && !empty($_GET['from']) && stripos($row['fromplace'], $_GET['from']) === false) continue;
                if (isset($_GET['to']) && !empty($_GET['to']) && stripos($row['toplace'], $_GET['to']) === false) continue;
                
                // Check if journey datetime has passed
                $journeyDateTime = $row['journeydate'] . ' ' . $row['boarding_time'];
                if (strtotime($journeyDateTime) <= strtotime($currentDateTime)) continue;

                // Additional check to verify if the ticket is not already booked
                $checkBooking = "SELECT id FROM new_bookings WHERE 
                    seat_no = ? AND 
                    journey_date = ? AND 
                    from_location = ? AND 
                    to_location = ?";
                    
                $checkStmt = $conn->prepare($checkBooking);
                $checkStmt->bind_param("ssss", 
                    $row['seat_no'], 
                    $row['journeydate'], 
                    $row['fromplace'], 
                    $row['toplace']
                );
                $checkStmt->execute();
                $bookingResult = $checkStmt->get_result();
                
                if ($bookingResult->num_rows > 0) continue;  // Skip if ticket is already booked
                
                $hasValidTickets = true;
                ?>
                <div class="ticket-card">
                    <div class="ticket-info">
                        <h3><?php echo htmlspecialchars($row['fromplace']); ?> to <?php echo htmlspecialchars($row['toplace']); ?></h3>
                        <div class="ticket-details">
                            <span><i class="fas fa-bus"></i> <?php echo htmlspecialchars($row['busname']); ?></span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($row['journeydate'])); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($row['boarding_time'])); ?></span>
                            <span><i class="fas fa-ticket-alt"></i> Ticket #<?php echo htmlspecialchars($row['seat_no']); ?></span>
                            <span class="price"><i class="fas fa-tag"></i> ₹<?php echo number_format($row['ticketprice'], 2); ?></span>
                        </div>
                    </div>
                    <a href="xticketbuy.php?id=<?php echo $row['id']; ?>" class="buy-btn">
                        Buy Now
                    </a>
                </div>
                <?php
            }
            
            if (!$hasValidTickets) {
                echo '<div class="no-tickets">
                        <i class="fas fa-ticket-alt" style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;"></i>
                        <p>No tickets available for the selected criteria.</p>
                      </div>';
            }
        } else {
            echo '<div class="no-tickets">
                    <i class="fas fa-ticket-alt" style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;"></i>
                    <p>No tickets available at the moment.</p>
                  </div>';
        }
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>