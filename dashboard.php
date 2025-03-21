<?php
include 'config.php';
session_start();
$username = $_SESSION['username'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTicket - Travel Booking</title>
    <!-- Include Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            position: relative;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        /* Navbar */
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

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-container img {
            width: 40px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .user-profile {
            display: flex;
            align-items: center;
            color: white;
        }

        .user-profile img {
            width: 40px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            color: white;
            font-weight: bold;
        }

        .nav-links a:hover {
            color: #ffcc00;
        }

        .sign-out {
            color: white;
            margin-left: 15px;
            display: flex;
            align-items: center;
        }

        .sign-out i {
            margin-right: 5px;
        }

        .sign-out:hover {
            color: #ff4d4d;
        }

        /* Mobile Menu Button */
        .menu-btn {
            display: none;
            cursor: pointer;
            font-size: 24px;
            color: white;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background-color: #001F3F;
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
            padding-top: 60px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
        }

        .sidebar-user {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #0a2e58;
        }

        .sidebar-user img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .sidebar-nav {
            padding: 20px;
        }

        .sidebar-nav a {
            display: block;
            color: white;
            padding: 15px 0;
            font-size: 16px;
            border-bottom: 1px solid #0a2e58;
        }

        .sidebar-nav a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav a:hover {
            color: #ffcc00;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .overlay.active {
            display: block;
        }

        /* Section Styling */
        .hero, .category-section, .top-picks, .my-bookings {
            padding: 20px;
            text-align: center;
        }

        .hero img {
            width: 100%;
            max-width: 1000px;
            border-radius: 10px;
            margin: 20px 0;
            position: relative;
        }

        .my-bookings {
            background-color: #fff;
            margin: 20px auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            max-width: 800px;
        }

        .my-bookings h3 {
            margin-bottom: 15px;
        }

        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .booking-item:last-child {
            border-bottom: none;
        }

        .booking-item img {
            width: 60px;
            border-radius: 5px;
        }

        .booking-details {
            flex: 1;
            margin-left: 15px;
            text-align: left;
        }

        .btn-cancel {
            color: #fff;
            background-color: #ff4d4d;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background-color: #e60000;
        }

        .category-section {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
        }

        .category-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            transition: transform 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        .category-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .category-card h3 {
            padding: 15px;
            color: #001F3F;
        }

        .text-overlay {
            position: absolute;
            top: 50%;
            left: 20%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.6);
            padding: 15px;
            border-radius: 8px;
            color: white;
            max-width: 250px;
        }

        .text-overlayy {
            position: absolute;
            top: 50%;
            right: 10%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.6);
            padding: 15px;
            border-radius: 8px;
            color: white;
            max-width: 250px;
        }

        .text-overlay h2, .text-overlayy h2 {
            font-size: 20px;
            margin: 0;
            color: #ffcc00;
        }

        .text-overlay p, .text-overlayy p {
            font-size: 16px;
            margin-top: 2px;
        }

        .hero-container {
            position: relative;
            display: inline-block;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .navbar {
                padding: 10px;
            }
            
            .nav-links, .user-profile .sign-out, .user-profile span {
                display: none;
            }
            
            .menu-btn {
                display: block;
            }
            
            .hero img {
                margin: 10px 0;
            }
            
            .text-overlay, .text-overlayy {
                position: relative;
                top: auto;
                left: auto;
                right: auto;
                transform: none;
                margin: 10px auto;
                max-width: 100%;
            }
            
            .category-card {
                width: 100%;
                max-width: 400px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar for Mobile -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-close" onclick="toggleSidebar()">
        <i class="fas fa-times"></i>
    </div>
    <div class="sidebar-user">
        <img src="assets/images/user.jpeg" alt="User">
        <span><?php echo $username; ?></span>
        <small><?php echo $email; ?></small>
    </div>
    <div class="sidebar-nav">
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="mybookings.php"><i class="fas fa-ticket-alt"></i> My Bookings</a>
        <a href="myuploads.php"><i class="fas fa-upload"></i> My Uploads</a>
        <a href="homepage.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </div>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Navbar -->
<div class="navbar">
    <div class="logo-container">  
        <img src="assets/images/logo.png" alt="MetaTicket Logo">
        <h1>MetaTicket</h1>
    </div>
    <div class="nav-links">
        <a href="profile.php">Profile</a>
        <a href="mybookings.php">My Bookings</a>
        <a href="myuploads.php">My Uploads</a>
    </div>
    <div class="user-profile">
        <a href="profile.php">
            <img src="assets/images/user.jpeg" alt="User">
        </a>
        <div class="menu-btn" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <span><?php echo $username; ?><br><small><?php echo $email; ?></small></span>
        <!-- Sign Out Button -->
        <a href="homepage.php" class="sign-out">
            <i class="fas fa-sign-out-alt"></i> Sign Out
        </a>
    </div>
</div>

<!-- Hero Section -->
<div class="hero">
    <h2 style="color: blue">Book Your Tickets Now !!</h2>
    <div class="hero-container">
        <a href="xticket.php">
            <img src="assets/images/ticket_booking.jpg" alt="Book Tickets">
        </a>
    </div>
</div>

<!-- Browse By Category -->
<div class="category-section">
    <div class="category-card">
        <a href="bushome.php">
            <img src="assets/images/busPic.jpg" alt="Bus">
            <h3>Bus Booking</h3>
        </a>
    </div>
    <div class="category-card">
        <a href="xticket.php">
            <img src="assets/images/bus11.jpg" alt="Tickets">
            <h3>Available Tickets</h3>
        </a>
    </div>
    <div class="category-card">
        <a href="new_bookings.php">
            <img src="assets/images/contact.jpg" alt="Bookings">
            <h3>Platform Bookings</h3>
        </a>
    </div>
</div>

<!-- Footer -->
<?php
include 'footer.php';
?>

<!-- JavaScript for Sidebar Toggle -->
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('overlay').classList.toggle('active');
    }
</script>

</body>
</html>