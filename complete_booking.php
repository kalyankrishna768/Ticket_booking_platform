<?php
session_start();
// Connect to the database
$conn = new mysqli("localhost", "root", "", "ticket");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Retrieve all form data
        $train_id = $_POST['train_id'];
        $trainname = $_POST['trainname'];
        $journeydate = $_POST['journeydate'];
        $startingtime = $_POST['startingtime'];
        $ticketprice = $_POST['ticketprice'];
        $user_id = $_POST['user_id'];
        $wallet_balance = $_POST['wallet_balance'];
        $departure_location = $_POST['departure_location'];
        $dropping_location = $_POST['dropping_location'];
        
        // Insert into train_bookings
        $booking_sql = "INSERT INTO train_bookings 
                       (user_id, train_id, trainname, journeydate, 
                        startingtime, ticketprice, departure_location, 
                        dropping_location, booking_date) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bind_param("iisssiss", 
            $user_id, $train_id, $trainname, $journeydate, 
            $startingtime, $ticketprice, $departure_location, 
            $dropping_location
        );
        $booking_stmt->execute();

        // Commit transaction
        $conn->commit();
        
        // Show success message
        echo "<!DOCTYPE html>
              <html lang='en'>
              <head>
                  <meta charset='UTF-8'>
                  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                  <title>Booking Successful</title>
                  <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
                  <style>
                      body {
                          font-family: 'Poppins', sans-serif;
                          background: #f4f4f9;
                          color: #333;
                          display: flex;
                          justify-content: center;
                          align-items: center;
                          min-height: 100vh;
                          margin: 0;
                          position: relative;
                      }
                      .container {
                          background: #fff;
                          padding: 20px;
                          border-radius: 8px;
                          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                          text-align: center;
                          max-width: 500px;
                          width: 100%;
                      }
                      h1 {
                          color: #28a745;
                      }
                      p {
                          margin: 20px 0;
                      }
                      .details {
                          text-align: left;
                          margin: 20px 0;
                          padding: 15px;
                          background: #f8f9fa;
                          border-radius: 5px;
                      }
                      a {
                          display: inline-block;
                          padding: 10px 20px;
                          background: #e84118;
                          color: #fff;
                          text-decoration: none;
                          border-radius: 5px;
                          margin-top: 20px;
                      }
                      a:hover {
                          background: #c73615;
                      }
                  </style>
              </head>
              <body>
                  <div class='container'>
                      <h1>Booking Successful!</h1>
                      <div class='details'>
                          <p><strong>Train:</strong> " . htmlspecialchars($trainname) . "</p>
                          <p><strong>Date:</strong> " . htmlspecialchars($journeydate) . "</p>
                          <p><strong>Time:</strong> " . htmlspecialchars($startingtime) . "</p>
                          <p><strong>From:</strong> " . htmlspecialchars($departure_location) . "</p>
                          <p><strong>To:</strong> " . htmlspecialchars($dropping_location) . "</p>
                          <p><strong>Amount Paid:</strong> ₹" . htmlspecialchars($ticketprice) . "</p>
                          <p><strong>Remaining Wallet Balance:</strong> ₹" . number_format($wallet_balance - $ticketprice, 2) . "</p>
                      </div>
                      <a href='trainbuy.php'>Back to Search</a>
                  </div>
              </body>
              </html>";
              
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
    
    // Close statements
    if(isset($booking_stmt)) $booking_stmt->close();
    if(isset($total_booking_stmt)) $total_booking_stmt->close();
    
} else {
    echo "Invalid request.";
}

// Close the database connection
$conn->close();
?>