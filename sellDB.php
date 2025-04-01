<?php
include 'config.php';
session_start();
$email = $_SESSION['email'];

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: newsignin.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the user agreed to the policy
    if (!isset($_POST['agree_policy'])) {
        // Redirect back to the form with an error message
        header("Location: sell_ticket.php?id=" . $_POST['booking_id'] . "&error=policy");
        exit();
    }
    
    // Get form data
    $bus_id = $_POST['bus_id'];
    $busname = $_POST['busname'];
    $journeydate = $_POST['journeydate'];
    $fromplace = $_POST['from_location'];
    $toplace = $_POST['to_location'];
    $boarding_time = $_POST['boarding_time'];
    $seat_no = $_POST['selected_seats'];
    $ticketprice = $_POST['total_amount'];
    $booking_id = $_POST['booking_id'];
    $ticketUpload = "from my bookings";
    $status = "accepted";
    
    // Prepare the SQL statement
    $insert_query = "INSERT INTO bus_sell (email, seat_no, bus_id, busname, journeydate, fromplace, toplace, boarding_time, ticketprice, booking_id, ticketUpload, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("siissssdiiss", $email, $seat_no, $bus_id, $busname, $journeydate, $fromplace, $toplace, $boarding_time, $ticketprice, $booking_id, $ticketUpload, $status);
    
    if ($stmt->execute()) {
        echo "<script>alert('Ticket Uploaded successfully!'); window.location.href = 'mybookings.php';</script>";
        exit();
    } else {
        // If there was an error, redirect back with error message
        header("Location: sell.php?id=" . $booking_id . "&error=database");
        exit();
    }
} else {
    // If the form was not submitted, redirect to the booking page
    header("Location: sell.php");
    exit();
}
?>