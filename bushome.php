<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTicket - Your Travel Companion</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2196f3;
            --primary-dark: #1976d2;
            --accent-color: #64b5f6;
            --dark-bg: rgba(0, 0, 0, 0.9);
            --card-bg: rgba(255, 255, 255, 0.1);
            --footer-bg: #1A237E;
            --text-color: white;
            --transition-speed: 0.3s;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            --card-radius: 16px;
            --button-radius: 30px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, html {
            height: 100%;
            font-family: 'Segoe UI', 'Arial', sans-serif;
            color: var(--text-color);
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        .background {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)), 
                        url('assets/images/back1.jpg') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--card-radius);
            box-shadow: var(--box-shadow);
        }

        /* Header and Navigation */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            z-index: 1000;
            transition: all var(--transition-speed);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: rgba(0, 0, 0, 0.7);
        }

        .navbar.scrolled {
            background: rgba(0, 0, 0, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            padding: 10px 5%;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 20;
        }

        .navbar-brand img {
            height: 42px;
            transition: all var(--transition-speed);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .navbar-brand a {
            font-weight: 600;
            font-size: 24px;
            letter-spacing: 0.5px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .navbar a {
            color: var(--text-color);
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: all var(--transition-speed);
            display: inline-block;
            position: relative;
            padding: 5px 0;
        }

        .navbar a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--accent-color);
            transition: width var(--transition-speed);
        }

        .navbar a:hover {
            color: var(--accent-color);
            transform: translateY(-2px);
        }

        .navbar a:hover:after {
            width: 100%;
        }

        .menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 24px;
            z-index: 20;
            border: none;
            background: transparent;
            color: var(--text-color);
            transition: all var(--transition-speed);
        }

        .menu-toggle:hover {
            color: var(--accent-color);
            transform: rotate(90deg);
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            padding: 150px 20px 80px;
            max-width: 900px;
            width: 90%;
            animation: fadeIn 1s ease;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            font-weight: 700;
            letter-spacing: 1px;
        }

        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 40px;
            line-height: 1.7;
            animation: fadeInUp 1.2s ease;
            opacity: 0.9;
        }

        .button-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            animation: fadeInUp 1.4s ease;
            flex-wrap: wrap;
        }

        .button {
            padding: 16px 32px;
            background-color: var(--primary-color);
            color: var(--text-color);
            font-size: 1.125rem;
            font-weight: 600;
            border: none;
            border-radius: var(--button-radius);
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 10px 5px;
            position: relative;
            overflow: hidden;
        }

        .button:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }

        .button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .button:hover:before {
            left: 100%;
        }

        .button:active {
            transform: translateY(-2px);
        }

        .button i {
            font-size: 1.2em;
        }

        /* Features Section */
        .features-section {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.8), rgba(8, 24, 65, 0.9));
            padding: 80px 5% 100px;
            width: 100%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
            font-size: 2.5rem;
            color: var(--text-color);
            position: relative;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .feature-card {
            padding: 40px 30px;
            border-radius: var(--card-radius);
            text-align: center;
            transition: all var(--transition-speed);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--box-shadow);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.15);
        }

        .feature-card i {
            font-size: 50px;
            color: var(--accent-color);
            margin-bottom: 25px;
            transition: all var(--transition-speed);
        }

        .feature-card:hover i {
            transform: scale(1.1);
            color: var(--primary-color);
        }

        .feature-card h3 {
            margin-bottom: 15px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .feature-card p {
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background: linear-gradient(0deg, var(--footer-bg), #283593);
            color: var(--text-color);
            text-align: center;
            padding: 50px 20px 30px;
        }

        .social-icons {
            margin: 30px 0;
            display: flex;
            justify-content: center;
            gap: 25px;
        }

        .social-icons a {
            color: var(--text-color);
            font-size: 20px;
            transition: all var(--transition-speed);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .social-icons a:hover {
            color: var(--accent-color);
            transform: translateY(-5px) scale(1.1);
            background: rgba(255, 255, 255, 0.2);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin: 30px 0;
        }

        .footer-links a {
            color: var(--text-color);
            text-decoration: none;
            transition: all var(--transition-speed);
            position: relative;
            font-weight: 500;
            padding: 5px 0;
        }

        .footer-links a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--accent-color);
            transition: width var(--transition-speed);
        }

        .footer-links a:hover {
            color: var(--accent-color);
        }

        .footer-links a:hover:after {
            width: 100%;
        }

        .copyright {
            margin-top: 30px;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Pulse animation for CTA buttons */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(33, 150, 243, 0.6);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(33, 150, 243, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(33, 150, 243, 0);
            }
        }

        .primary-cta {
            animation: pulse 2s infinite;
            background: linear-gradient(45deg, var(--primary-color), #1e88e5);
        }

        /* App Install Banner */
        .app-banner {
            background: linear-gradient(to right, #1A237E, #3949AB);
            padding: 15px 20px;
            text-align: center;
            display: none;
            align-items: center;
            justify-content: center;
            width: 100%;
            position: relative;
            z-index: 1001;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .app-banner p {
            margin: 0 15px 0 0;
            font-weight: 500;
        }

        .app-banner .close-banner {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
            transition: all var(--transition-speed);
        }

        .app-banner .close-banner:hover {
            transform: translateY(-50%) rotate(90deg);
            color: var(--accent-color);
        }

        .install-button {
            padding: 8px 20px;
            background: white;
            color: #1A237E;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all var(--transition-speed);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .install-button:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-color);
            color: white;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-speed);
            z-index: 999;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: var(--primary-dark);
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        .back-to-top i {
            font-size: 20px;
        }

        /* Features Icons */
        .features-section .row {
            margin-top: 50px;
        }

        /* Add Feature Cards */
        .feature-card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Mobile Responsive Styles */
        @media screen and (max-width: 991px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
                padding: 0 20px;
            }
            
            .hero-section {
                padding-top: 130px;
            }
        }

        @media screen and (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .navbar {
                padding: 12px 20px;
            }

            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                height: 100vh;
                background: rgba(0, 0, 0, 0.95);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                flex-direction: column;
                justify-content: center;
                transition: all var(--transition-speed) ease;
                padding: 80px 0 30px;
                z-index: 10;
                box-shadow: -5px 0 30px rgba(0, 0, 0, 0.5);
            }

            .nav-links.active {
                right: 0;
            }

            .nav-links a {
                padding: 15px;
                width: 100%;
                text-align: center;
                font-size: 18px;
            }

            .hero-section {
                padding-top: 120px;
            }

            .hero-section h1 {
                font-size: 2.5rem;
            }

            .hero-section p {
                font-size: 1.1rem;
            }

            .button {
                width: 100%;
                justify-content: center;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .social-icons {
                gap: 15px;
            }

            .footer-links {
                gap: 20px;
                flex-direction: column;
            }
            
            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }

        @media screen and (max-width: 480px) {
            .navbar-brand img {
                height: 35px;
            }

            .navbar-brand a {
                font-size: 20px;
            }

            .hero-section {
                padding: 110px 15px 40px;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section p {
                font-size: 1rem;
            }

            .button {
                padding: 14px 20px;
                font-size: 0.95rem;
            }

            .feature-card {
                padding: 25px 20px;
            }

            .feature-card i {
                font-size: 40px;
            }

            .feature-card h3 {
                font-size: 1.3rem;
            }
            
            .app-banner {
                flex-direction: column;
                gap: 10px;
                padding: 15px 30px 15px 15px;
            }

            .app-banner p {
                margin: 0 0 10px 0;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }

        /* Landscape mode fixes */
        @media screen and (max-height: 480px) and (orientation: landscape) {
            .nav-links {
                padding-top: 60px;
                overflow-y: auto;
            }

            .nav-links a {
                padding: 10px;
            }

            .hero-section {
                padding: 100px 20px 40px;
            }
            
            .button-container {
                flex-direction: row;
            }
            
            .button {
                width: auto;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .install-button {
                background: var(--primary-color);
                color: white;
            }
        }
    </style>
</head>
<body>
    

    <div class="background">
        <nav class="navbar" id="mainNav">
            <div class="navbar-brand">
                <img src="assets/images/logo.png" alt="MetaTicket Logo">
                <a href="#">MetaTicket</a>
            </div>
            <div class="nav-links" id="navLinks">
                <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="mybookings.php"><i class="fas fa-ticket-alt"></i> My Bookings</a>
                <a href="myuploads.php"><i class="fas fa-upload"></i> My Uploads</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
            </div>
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </button>
        </nav>

        <section class="hero-section">
            <h1>Welcome to MetaTicket</h1>
            <p>Your one-stop destination for hassle-free ticket trading. Buy and sell tickets securely with our trusted platform.</p>
            
            <div class="button-container">
                <a href="sellticket.php" class="button">
                    <i class="fas fa-ticket-alt"></i>
                    Sell Ticket
                </a>
                <a href="busbuy.php" class="button primary-cta">
                    <i class="fas fa-shopping-cart"></i>
                    Book Ticket
                </a>
            </div>
        </section>
    </div>

    <!-- Features Section -->
    <section class="features-section">
        <h2 class="section-title">Why Choose Us</h2>
        
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Secure Transactions</h3>
                <p>All transactions are protected with advanced encryption and secure payment gateways to ensure your data remains safe.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-bolt"></i>
                <h3>Fast & Easy</h3>
                <p>Book or sell tickets in minutes with our streamlined process that saves your time and reduces hassle.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-hand-holding-usd"></i>
                <h3>No Hidden Fees</h3>
                <p>Transparent pricing with no surprise charges. What you see is what you pay, always.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-users"></i>
                <h3>Verified Community</h3>
                <p>Our user verification system ensures you're dealing with real people and legitimate tickets.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-mobile-alt"></i>
                <h3>Mobile Friendly</h3>
                <p>Access MetaTicket on any device with our responsive platform designed for on-the-go transactions.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-headset"></i>
                <h3>24/7 Support</h3>
                <p>Our dedicated customer service team is available round the clock to assist you with any issues.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        
        
        <div class="footer-links">
            <a href="privacy.php">Privacy Policy</a>
            <a href="contact.php">Contact Us</a>
        </div>
        
        <p class="copyright">© 2025 MetaTicket. All rights reserved.</p>
    </footer>

    <a href="#" class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fas fa-chevron-up"></i>
    </a>

    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('navLinks').classList.toggle('active');
            
            // Toggle between menu and close icons
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const navLinks = document.getElementById('navLinks');
            const menuToggle = document.getElementById('menuToggle');
            
            if (navLinks.classList.contains('active') && 
                !navLinks.contains(event.target) && 
                !menuToggle.contains(event.target)) {
                navLinks.classList.remove('active');
                const icon = menuToggle.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close menu when a link is clicked
        const navLinks = document.querySelectorAll('.nav-links a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navLinks').classList.remove('active');
                const icon = document.querySelector('.menu-toggle i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            });
        });

        // Scroll effects
        window.addEventListener('scroll', function() {
            // Navbar background change on scroll
            const navbar = document.getElementById('mainNav');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            // Back to top button visibility
            const backToTop = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });

        // Back to top functionality
        document.getElementById('backToTop').addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // App banner
        document.addEventListener('DOMContentLoaded', function() {
            // Check if the user is on a mobile device
            if (/Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                const appBanner = document.getElementById('appBanner');
                
                // Check if the banner has been closed before
                if (!localStorage.getItem('appBannerClosed')) {
                    appBanner.style.display = 'flex';
                }
            }
            
            // Close banner and remember choice
            document.getElementById('closeBanner').addEventListener('click', function() {
                document.getElementById('appBanner').style.display = 'none';
                localStorage.setItem('appBannerClosed', 'true');
            });
        });

        // Touch swipe detection for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        document.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        }, false);
        
        document.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, false);
        
        function handleSwipe() {
            const navLinks = document.getElementById('navLinks');
            
            // Swipe from left to right (open menu)
            if (touchEndX - touchStartX > 100 && touchStartX < 50) {
                navLinks.classList.add('active');
                const icon = document.querySelector('.menu-toggle i');
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
            
            // Swipe from right to left (close menu)
            if (touchStartX - touchEndX > 100 && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                const icon = document.querySelector('.menu-toggle i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }

    </script>
</body>
</html>