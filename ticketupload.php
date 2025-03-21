<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ticket");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data from previous page
$seatnumber = isset($_POST['seatnumber']) ? $_POST['seatnumber'] : '';
$bus_id = isset($_POST['bus_id']) ? $_POST['bus_id'] : '';
$busname = isset($_POST['busname']) ? $_POST['busname'] : '';
$journeydate = isset($_POST['journeydate']) ? $_POST['journeydate'] : '';
$fromplace = isset($_POST['fromplace']) ? $_POST['fromplace'] : '';
$toplace = isset($_POST['toplace']) ? $_POST['toplace'] : '';
$boardingtime = isset($_POST['boardingtime']) ? $_POST['boardingtime'] : '';
$ticketprice = isset($_POST['ticketprice']) ? $_POST['ticketprice'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Ticket Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #ff4757;
            --primary-hover: #e84118;
            --text-light: #ffffff;
            --text-dark: #333333;
            --bg-overlay: rgba(0, 0, 0, 0.75);
            --bg-card: rgba(255, 255, 255, 0.15);
            --bg-input: rgba(255, 255, 255, 0.2);
            --bg-policy: rgba(0, 0, 0, 0.3);
            --accent-color: #ff6b81;
            --shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', 'Arial', sans-serif;
            background: linear-gradient(to bottom, var(--bg-overlay), var(--bg-overlay)),
                url("assets/images/travel2.jpg") no-repeat center center fixed;
            background-size: cover;
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        #container {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: var(--border-radius);
            width: 100%;
            max-width: 550px;
            box-shadow: var(--shadow);
            position: relative;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        header {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--primary-color);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 1rem;
            margin-bottom: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        label i {
            margin-right: 8px;
            color: var(--accent-color);
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            font-size: 1rem;
            background: var(--bg-input);
            color: var(--text-light);
            outline: none;
            transition: var(--transition);
        }

        input:focus,
        textarea:focus,
        select:focus {
            background: rgba(255, 255, 255, 0.3);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.3);
        }

        input[type="submit"] {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(232, 65, 24, 0.4);
        }

        input[type="submit"]:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(232, 65, 24, 0.5);
        }

        input[type="submit"]:active {
            transform: translateY(1px);
        }

        .back {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: var(--text-light);
            font-size: 1.2rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .back:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        p {
            text-align: center;
            font-size: 0.95rem;
            margin-top: 20px;
        }

        p a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: bold;
            transition: var(--transition);
        }

        p a:hover {
            text-decoration: underline;
            color: var(--primary-color);
        }

        /* Policy Checkbox Styles */
        .policy-container {
            margin: 15px 0 25px 0;
            background: var(--bg-policy);
            padding: 20px;
            border-radius: 10px;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .policy-title {
            color: var(--accent-color);
            text-align: center;
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .policy-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 3px;
        }

        /* Custom checkbox styling */
        .checkbox-container {
            display: flex;
            align-items: flex-start;
            position: relative;
            padding-left: 35px;
            margin-bottom: 15px;
            cursor: pointer;
            font-size: 0.95rem;
            user-select: none;
            line-height: 1.5;
            transition: var(--transition);
        }

        .checkbox-container:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        .checkbox-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            top: 2px;
            left: 0;
            height: 22px;
            width: 22px;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 5px;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .checkbox-container:hover input ~ .checkmark {
            background-color: rgba(255, 255, 255, 0.25);
        }

        .checkbox-container input:checked ~ .checkmark {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            transition: var(--transition);
        }

        .checkbox-container input:checked ~ .checkmark:after {
            display: block;
        }

        .checkbox-container .checkmark:after {
            left: 7px;
            top: 3px;
            width: 6px;
            height: 11px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        /* Notice styling */
        .notice {
            text-align: center;
            color: var(--primary-color);
            margin: 0 0 25px 0;
            font-weight: 600;
            padding: 10px;
            border-radius: 8px;
            background: rgba(255, 71, 87, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notice i {
            margin-right: 8px;
            font-size: 1.2rem;
        }

        /* File upload styling */
        .file-upload {
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .file-upload label {
            display: block;
        }

        .upload-area {
            border: 2px dashed rgba(255, 255, 255, 0.3);
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.1);
        }

        .upload-area:hover {
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.15);
        }

        .upload-area i {
            font-size: 2rem;
            color: var(--accent-color);
            margin-bottom: 10px;
        }

        .upload-text {
            font-size: 0.9rem;
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.8);
        }

        #ticketUpload {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        #fileName {
            margin-top: 5px;
            font-size: 0.85rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Card design for inputs */
        .input-card {
            background: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            margin-bottom: 5px;
        }

        .form-group input {
            margin-bottom: 0;
        }

        /* Progress bar for submission */
        .progress-container {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            margin-top: 10px;
            overflow: hidden;
            display: none;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: var(--primary-color);
            transition: width 0.3s;
        }

        /* Loading indicator */
        .loading {
            display: none;
            text-align: center;
            margin-top: 15px;
        }

        .loading i {
            animation: spin 1s infinite linear;
            color: var(--accent-color);
            font-size: 1.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #container {
                padding: 25px;
                margin: 15px;
                max-width: 100%;
                max-height: calc(100vh - 40px);
                overflow-y: auto;
            }

            header {
                font-size: 1.7rem;
                margin-bottom: 20px;
            }
            
            .back {
                top: 15px;
                left: 15px;
                width: 35px;
                height: 35px;
            }
        }

        @media (max-width: 480px) {
            #container {
                padding: 20px 15px;
                border-radius: 10px;
            }

            header {
                font-size: 1.5rem;
                margin-bottom: 15px;
            }
            
            input[type="submit"] {
                padding: 12px;
            }
            
            .policy-title {
                font-size: 1.1rem;
            }
            
            .checkbox-container {
                font-size: 0.9rem;
                line-height: 1.4;
                padding-left: 30px;
            }
            
            .checkmark {
                width: 20px;
                height: 20px;
            }
        }
    </style>
</head>

<body>
    <button class="back" onclick="window.history.back();"><i class="fas fa-arrow-left"></i></button>
    <div id="container">
        <header>Enter Ticket Details</header>

        <div class="notice">
            <i class="fas fa-exclamation-circle"></i>
            Please verify all details before submission!
        </div>
        
        <div class="policy-container">
            <div class="policy-title">Ticket Policies</div>
            
            <label class="checkbox-container">If anyone buys your ticket, only then will your ticket money be transferred to your wallet. You can check in available tickets page. If anyone books it, it will disappear from available tickets page.
                <input type="checkbox" class="policy-check" required>
                <span class="checkmark"></span>
            </label>
            
            <label class="checkbox-container">If you want to cancel your sold ticket and continue your journey, please contact us through the contact page.
                <input type="checkbox" class="policy-check" required>
                <span class="checkmark"></span>
            </label>

            <label class="checkbox-container">Please upload ticket with the same email you booked with.
                <input type="checkbox" class="policy-check" required>
                <span class="checkmark"></span>
            </label>

            <label class="checkbox-container">Check your ticket details clearly, if there is any mistake your ticket will be rejected. If admin accepts it, your ticket will float in available tickets page.
                <input type="checkbox" class="policy-check" required>
                <span class="checkmark"></span>
            </label>
        </div>
        
        <form method="post" action="ticketuploadDB.php" enctype="multipart/form-data" id="ticketForm">
            <div class="input-card">
                <div class="form-group">
                    <label for="bookID"><i class="fas fa-ticket-alt"></i> Booking ID</label>
                    <input type="text" name="booking_id" id="bookID" placeholder="Enter your booking ID" required>
                </div>
            </div>

            <div class="file-upload">
                <label for="ticketUpload"><i class="fas fa-file-upload"></i> Upload Ticket</label>
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div>Drag & drop ticket image here or click to browse</div>
                    <div class="upload-text">File type: "youremail.(jpg or pdf)"</div>
                    <input type="file" name="ticketUpload" id="ticketUpload" accept="image/*" required>
                </div>
                <div id="fileName"></div>
            </div>

            <input type="hidden" name="seatnumber" value="<?php echo htmlspecialchars($seatnumber); ?>">
            <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($bus_id); ?>">
            <input type="hidden" name="busname" value="<?php echo htmlspecialchars($busname); ?>">
            <input type="hidden" name="journeydate" value="<?php echo htmlspecialchars($journeydate); ?>">
            <input type="hidden" name="fromplace" value="<?php echo htmlspecialchars($fromplace); ?>">
            <input type="hidden" name="toplace" value="<?php echo htmlspecialchars($toplace); ?>">
            <input type="hidden" name="boardingtime" value="<?php echo htmlspecialchars($boardingtime); ?>">
            <input type="hidden" name="ticketprice" value="<?php echo htmlspecialchars($ticketprice); ?>">

            <input type="submit" value="Submit Ticket" id="submitBtn">
            
            <div class="progress-container" id="progressContainer">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            
            <div class="loading" id="loadingIndicator">
                <i class="fas fa-spinner"></i> Processing...
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Policy checkbox validation
            const form = document.getElementById('ticketForm');
            const checkboxes = document.querySelectorAll('.policy-check');
            const submitBtn = document.getElementById('submitBtn');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const ticketUpload = document.getElementById('ticketUpload');
            const fileName = document.getElementById('fileName');
            const uploadArea = document.getElementById('uploadArea');
            
            // File upload preview
            ticketUpload.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileName.textContent = this.files[0].name;
                    uploadArea.style.borderColor = 'var(--primary-color)';
                } else {
                    fileName.textContent = '';
                }
            });
            
            // Drag and drop functionality
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                uploadArea.style.backgroundColor = 'rgba(255, 255, 255, 0.25)';
                uploadArea.style.borderColor = 'var(--primary-color)';
            }
            
            function unhighlight() {
                uploadArea.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                uploadArea.style.borderColor = 'rgba(255, 255, 255, 0.3)';
            }
            
            uploadArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                ticketUpload.files = files;
                
                if (files && files[0]) {
                    fileName.textContent = files[0].name;
                }
            }
            
            // Form submission
            form.addEventListener('submit', function(event) {
                let allChecked = true;
                
                checkboxes.forEach(function(checkbox) {
                    if (!checkbox.checked) {
                        allChecked = false;
                    }
                });
                
                if (!allChecked) {
                    event.preventDefault();
                    alert('Please acknowledge all policies before submitting.');
                    return;
                }
                
                // Show loading animation
                submitBtn.disabled = true;
                progressContainer.style.display = 'block';
                loadingIndicator.style.display = 'block';
                
                // Simulate progress (in real application, you would use AJAX to track actual upload progress)
                let progress = 0;
                const interval = setInterval(function() {
                    progress += 5;
                    progressBar.style.width = progress + '%';
                    
                    if (progress >= 100) {
                        clearInterval(interval);
                        // In a real application, you would submit the form here or handle the response
                    }
                }, 150);
                
                // For demo purposes, we'll prevent the default form submission and simulate it
                // Remove this line in production to allow actual form submission
                // event.preventDefault();
            });
        });
    </script>
</body>
</html>