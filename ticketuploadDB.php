<?php

session_start();

$email = $_SESSION['email'];

// Database connection details
include ("config.php");

// Get form data from previous page
$seat_no = isset($_POST['seatnumber']) ? $_POST['seatnumber'] : '';
$bus_id = isset($_POST['bus_id']) ? $_POST['bus_id'] : '';
$busname = isset($_POST['busname']) ? $_POST['busname'] : '';
$journeydate = isset($_POST['journeydate']) ? $_POST['journeydate'] : '';
$fromplace = isset($_POST['fromplace']) ? $_POST['fromplace'] : '';
$toplace = isset($_POST['toplace']) ? $_POST['toplace'] : '';
$boardingtime = isset($_POST['boardingtime']) ? $_POST['boardingtime'] : '';
$ticketprice = isset($_POST['ticketprice']) ? $_POST['ticketprice'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $booking_id = htmlspecialchars(trim($_POST["booking_id"]));
    // Handle file upload
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($_FILES['ticketUpload']['name']);

    if (move_uploaded_file($_FILES['ticketUpload']['tmp_name'], $uploadFile)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO bus_sell (email, seat_no, bus_id, busname, journeydate, fromplace, toplace, boarding_time, ticketprice, booking_id, ticketUpload) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siisssssiis", $email, $seat_no, $bus_id, $busname, $journeydate, $fromplace, $toplace, $boardingtime, $ticketprice, $booking_id, $uploadFile);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>alert('Ticket Uploaded successfully!'); window.location.href = 'bushome.php';</script>";
        } else {
            echo "<h3>Error: " . $stmt->error . "</h3>";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "<h3>Error uploading the ticket file.</h3>";
    }

    // Close the connection
    $conn->close();
} else {
    echo "<h3>Invalid request method.</h3>";
}
?>
