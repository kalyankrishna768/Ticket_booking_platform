<?php
session_start();
// Connect to the database
$conn = new mysqli("localhost", "root", "", "ticket");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Retrieve form inputs
        $train_id = $_POST['train_id'];
        $trainname = $_POST['trainname'];
        $journeydate = $_POST['journeydate'];
        $startingtime = $_POST['startingtime'];
        $ticketprice = $_POST['ticketprice'];
        
        // Validate inputs
        if (empty($train_id) || empty($trainname) || empty($journeydate) || 
            empty($startingtime) || empty($ticketprice)) {
            throw new Exception("All fields are required.");
        }

        // Get user ID from session
        if (!isset($_SESSION['email'])) {
            throw new Exception("Please login to book tickets.");
        }
        
        // Get user details and wallet
        $user_email = $_SESSION['email'];
        $user_query = "SELECT s.id, w.id as wallet_id, w.balance 
                      FROM signup s 
                      LEFT JOIN user_wallets w ON s.id = w.user_id 
                      WHERE s.email = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("s", $user_email);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        
        if (!$user_data) {
            throw new Exception("User wallet not found.");
        }
        
        $user_id = $user_data['id'];
        $wallet_id = $user_data['wallet_id'];
        $wallet_balance = $user_data['balance'];
        
        // Check if user has sufficient balance
        if ($wallet_balance < $ticketprice) {
            throw new Exception("Insufficient balance. Please add funds to your wallet.");
        }
        
        // Get admin wallet
        $admin_query = "SELECT id, available_money FROM admin_wallet WHERE id = 1";
        $admin_result = $conn->query($admin_query);
        $admin_data = $admin_result->fetch_assoc();
        
        if (!$admin_data) {
            throw new Exception("Admin wallet not configured.");
        }
        
        // Update user wallet (deduct money)
        $update_user_wallet = "UPDATE user_wallets 
                             SET balance = balance - ? 
                             WHERE id = ?";
        $user_wallet_stmt = $conn->prepare($update_user_wallet);
        $user_wallet_stmt->bind_param("di", $ticketprice, $wallet_id);
        $user_wallet_stmt->execute();
        
        // Update admin wallet (add money)
        $update_admin_wallet = "UPDATE admin_wallet 
                              SET available_money = available_money + ? 
                              WHERE id = 1";
        $admin_wallet_stmt = $conn->prepare($update_admin_wallet);
        $admin_wallet_stmt->bind_param("d", $ticketprice);
        $admin_wallet_stmt->execute();
        
        // Record wallet transaction
        $transaction_sql = "INSERT INTO wallet_transactions 
                          (wallet_id, type, amount, transaction_date) 
                          VALUES (?, 'Train Ticket Purchase', -?, NOW())";
        $trans_stmt = $conn->prepare($transaction_sql);
        $trans_stmt->bind_param("id", $wallet_id, $ticketprice);
        $trans_stmt->execute();
        
        // Display form for stop locations
        echo "<!DOCTYPE html>
              <html lang='en'>
              <head>
                  <meta charset='UTF-8'>
                  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                  <title>Enter Stop Locations</title>
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
                      }
                      .container {
                          background: #fff;
                          padding: 20px;
                          border-radius: 8px;
                          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                          width: 100%;
                          max-width: 500px;
                      }
                      h2 {
                          text-align: center;
                          color: #333;
                      }
                      .form-group {
                          margin-bottom: 20px;
                      }
                      label {
                          display: block;
                          margin-bottom: 5px;
                          font-weight: 500;
                      }
                      input[type='text'] {
                          width: 100%;
                          padding: 8px;
                          border: 1px solid #ddd;
                          border-radius: 4px;
                          box-sizing: border-box;
                      }
                      button {
                          background: #e84118;
                          color: #fff;
                          padding: 10px 20px;
                          border: none;
                          border-radius: 4px;
                          cursor: pointer;
                          width: 100%;
                      }
                      button:hover {
                          background: #c73615;
                      }
                  </style>
              </head>
              <body>
                  <div class='container'>
                      <h2>Enter Stop Locations</h2>
                      <form action='complete_booking.php' method='POST'>
                          <input type='hidden' name='train_id' value='" . htmlspecialchars($train_id) . "'>
                          <input type='hidden' name='trainname' value='" . htmlspecialchars($trainname) . "'>
                          <input type='hidden' name='journeydate' value='" . htmlspecialchars($journeydate) . "'>
                          <input type='hidden' name='startingtime' value='" . htmlspecialchars($startingtime) . "'>
                          <input type='hidden' name='ticketprice' value='" . htmlspecialchars($ticketprice) . "'>
                          <input type='hidden' name='user_id' value='" . htmlspecialchars($user_id) . "'>
                          <input type='hidden' name='wallet_balance' value='" . htmlspecialchars($wallet_balance) . "'>
                          
                          <div class='form-group'>
                              <label for='departure_location'>Departure Location:</label>
                              <input type='text' id='departure_location' name='departure_location' required>
                          </div>
                          
                          <div class='form-group'>
                              <label for='dropping_location'>Dropping Location:</label>
                              <input type='text' id='dropping_location' name='dropping_location' required>
                          </div>
                          
                          <button type='submit'>Complete Booking</button>
                      </form>
                  </div>
              </body>
              </html>";
              
        // Commit transaction
        $conn->commit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
    
    // Close all statements
    if(isset($user_stmt)) $user_stmt->close();
    if(isset($user_wallet_stmt)) $user_wallet_stmt->close();
    if(isset($admin_wallet_stmt)) $admin_wallet_stmt->close();
    if(isset($trans_stmt)) $trans_stmt->close();
    
} else {
    echo "Invalid request.";
}

// Close the database connection
$conn->close();
?>