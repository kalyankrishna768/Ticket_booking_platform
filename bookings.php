<?php
session_start();

class Bookings
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "ticket");
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getAllBookings($search = "")
    {
        // Modified query to filter by agency_email
        $sql = "SELECT 
                    ab.id,
                    ab.user_email,
                    ab.agency_email,
                    ab.journey_date,
                    ab.seat_number,
                    ab.passenger_name,
                    ab.ticket_price,
                    ab.booking_status,
                    ab.boarding_point,
                    ab.dropping_point,
                    ab.created_at,
                    ab.route_id,
                    r.from_location,
                    r.to_location,
                    CONCAT(r.from_location, ' - ', r.to_location) as route
                FROM 
                    agency_bookings ab
                JOIN 
                    routes r ON ab.route_id = r.id
                WHERE 
                    ab.agency_email = ?";

        // Add search functionality if search parameter is provided
        if (!empty($search)) {
            $sql .= " AND (
                ab.user_email LIKE ? OR
                ab.seat_number LIKE ? OR
                ab.passenger_name LIKE ? OR 
                r.from_location LIKE ? OR
                ab.route_id LIKE ? OR 
                r.to_location LIKE ? OR 
                ab.boarding_point LIKE ? OR 
                ab.dropping_point LIKE ?
            )";
        }

        $sql .= " ORDER BY ab.created_at DESC";

        // Prepare and bind parameters
        if (!empty($search)) {
            $stmt = $this->conn->prepare($sql);
            $searchParam = "%$search%";
            $stmt->bind_param(
                "sssssssss",
                $_SESSION['email'],
                $searchParam,
                $searchParam,
                $searchParam,
                $searchParam,
                $searchParam,
                $searchParam,
                $searchParam,
                $searchParam
            );
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $_SESSION['email']);
        }

        // Execute and get results
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getBookingDetails($bookingId)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                ab.*,
                r.from_location,
                r.to_location
            FROM 
                agency_bookings ab
            JOIN 
                routes r ON ab.route_id = r.id
            WHERE 
                ab.id = ? AND ab.agency_email = ?
        ");
        $stmt->bind_param("is", $bookingId, $_SESSION['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateBookingStatus($bookingId, $status)
    {
        $stmt = $this->conn->prepare("
            UPDATE agency_bookings 
            SET booking_status = ? 
            WHERE id = ? AND agency_email = ?
        ");
        $stmt->bind_param("sis", $status, $bookingId, $_SESSION['email']);
        return $stmt->execute();
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}

// Initialize Bookings class and get data
$bookings = new Bookings();
$searchQuery = isset($_GET['search']) ? $_GET['search'] : "";
$allBookings = $bookings->getAllBookings($searchQuery);

// Handle view booking details AJAX request
if (isset($_GET['action']) && $_GET['action'] === 'getBookingDetails' && isset($_GET['id'])) {
    $bookingDetails = $bookings->getBookingDetails($_GET['id']);
    header('Content-Type: application/json');
    echo json_encode($bookingDetails);
    exit;
}

// Handle booking status update
if (isset($_POST['action']) && $_POST['action'] === 'updateStatus' && isset($_POST['id']) && isset($_POST['status'])) {
    $result = $bookings->updateBookingStatus($_POST['id'], $_POST['status']);
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Home - Bookings</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Sidebar transition for mobile */
        @media (max-width: 768px) {
            #sidebar {
                transition: transform 0.3s ease-in-out;
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 40;
                transform: translateX(-100%);
            }

            #sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                transition: margin-left 0.3s ease-in-out;
            }

            body.sidebar-open {
                overflow: hidden;
            }

            .sidebar-backdrop {
                transition: opacity 0.3s ease-in-out;
                opacity: 0;
                pointer-events: none;
            }

            .sidebar-backdrop.active {
                opacity: 1;
                pointer-events: auto;
            }
        }

        /* Search button styling */
        .search-button {
            background-color: #3b82f6;
            color: white;
            transition: all 0.3s ease;
        }

        .search-button:hover {
            background-color: #2563eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Modal animation */
        .modal-content {
            animation: modalFade 0.3s ease-out;
        }

        @keyframes modalFade {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Sidebar Backdrop (Mobile only) -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 sidebar-backdrop md:hidden"
        onclick="toggleSidebar()"></div>

    <div class="flex flex-col md:flex-row min-h-screen">
        <!-- Mobile Header -->
        <div class="md:hidden bg-blue-700 text-white p-4 flex justify-between items-center sticky top-0 z-20">
            <h1 class="text-xl font-bold"><?php echo ucfirst($_SESSION['username']); ?></h1>
            <button id="navToggle" class="text-white focus:outline-none" onclick="toggleSidebar()">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Sidebar - Will slide in from left on mobile -->
        <div id="sidebar" class="w-64 bg-blue-700 text-white md:block">
            <div class="p-6">
                <h1 class="text-2xl font-bold"><?php echo ucfirst($_SESSION['username']); ?></h1>
                <p class="text-sm text-blue-200"><i class="fas fa-ticket-alt w-5"></i> Bookings</p>
            </div>

            <nav class="mt-4 flex-grow">
                <a href="agency_home.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>

                <a href="managebus.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800">
                    <i class="fas fa-bus w-5"></i>
                    <span class="ml-3">Manage Buses</span>
                </a>

                <a href="routes.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800">
                    <i class="fas fa-route w-5"></i>
                    <span class="ml-3">Routes</span>
                </a>

                <a href="bookings.php" class="flex items-center px-6 py-3 bg-blue-800">
                    <i class="fas fa-ticket-alt w-5"></i>
                    <span class="ml-3">Bookings</span>
                </a>

                <a href="x_bookings.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800">
                    <i class="fas fa-clipboard-list w-5"></i>
                    <span class="ml-3">New Bookings</span>
                </a>

                <a href="agency_wallet.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800">
                    <i class="fas fa-wallet w-5"></i>
                    <span class="ml-3">Wallet</span>
                </a>
            </nav>

            <!-- Mobile Only Close Button -->
            <div class="md:hidden p-4 border-t border-blue-800">
                <button onclick="toggleSidebar()" class="flex items-center text-blue-100 hover:text-white">
                    <i class="fas fa-arrow-left mr-2"></i> Close Menu
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto main-content">
            <!-- Top Navigation Bar (desktop only) -->
            <header class="bg-white shadow-sm hidden md:block">
                <div class="flex items-center justify-between px-4 md:px-6 py-4">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold"><i class="fas fa-ticket-alt text-blue-600 mr-2"></i>Bookings
                            Management</h1>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-2"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Mobile Search Bar -->
            <div class="md:hidden p-4 bg-white shadow-sm">
                <form method="GET" action="">
                    <div class="relative flex items-center">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-search text-gray-500"></i>
                        </span>
                        <input type="text" name="search" placeholder="Search bookings..."
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                            class="w-full pl-10 pr-16 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 bg-blue-600 text-white rounded-r-lg px-3">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bookings Management Content -->
            <main class="p-4 md:p-6">
                <!-- Search Bar (Desktop) -->
                <div class="mb-6 hidden md:block">
                    <div class="flex justify-between items-center">
                        <form method="GET" action="" class="flex items-center">
                            <div class="relative flex items-center">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-search text-gray-500"></i>
                                </span>
                                <input type="text" name="search" placeholder="Search bookings..."
                                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                                    class="pl-10 pr-16 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-64 md:w-80">
                                <button type="submit"
                                    class="absolute right-0 top-0 bottom-0 px-3 bg-blue-600 text-white rounded-r-lg search-button">
                                    <i class="fas fa-search mr-1"></i>
                                </button>
                            </div>
                        </form>

                        <?php if (!empty($searchQuery)): ?>
                            <a href="bookings.php" class="ml-2 text-blue-600 hover:text-blue-800 flex items-center">
                                <i class="fas fa-times-circle mr-1"></i> Clear Search
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bookings Table - Responsive -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-hashtag mr-1"></i> Booking ID
                                    </th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-chair mr-1"></i> Seat
                                    </th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-user mr-1"></i> Passenger
                                    </th>
                                    <th
                                        class="hidden md:table-cell px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-calendar-alt mr-1"></i> Journey Date
                                    </th>
                                    <th
                                        class="hidden md:table-cell px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-map-pin mr-1"></i> Route ID
                                    </th>
                                    <th
                                        class="hidden md:table-cell px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-map-marked-alt mr-1"></i> Route
                                    </th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-rupee-sign mr-1"></i> Amount
                                    </th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-info-circle mr-1"></i> Status
                                    </th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-cogs mr-1"></i> Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (count($allBookings) > 0): ?>
                                    <?php foreach ($allBookings as $booking): ?>
                                        <tr class="hover:bg-gray-50" data-booking-id="<?php echo $booking['id']; ?>">
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                                #<?php echo htmlspecialchars($booking['id']); ?></td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                                <i class="fas fa-chair text-blue-600 mr-1"></i>
                                                <?php echo htmlspecialchars($booking['seat_number']); ?>
                                            </td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <i class="fas fa-user-circle text-gray-400 mr-1"></i>
                                                    <?php echo htmlspecialchars($booking['passenger_name']); ?>
                                                </div>
                                            </td>
                                            <td class="hidden md:table-cell px-4 md:px-6 py-4 whitespace-nowrap">
                                                <i class="far fa-calendar-alt text-gray-500 mr-1"></i>
                                                <?php echo date('d M Y', strtotime($booking['journey_date'])); ?>
                                            </td>
                                            <td
                                                class="hidden md:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <i class="fas fa-route text-gray-500 mr-1"></i>
                                                    <?php echo htmlspecialchars($booking['route_id']); ?>
                                                </div>
                                            </td>
                                            <td
                                                class="hidden md:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div><i class="fas fa-map-signs text-gray-500 mr-1"></i>
                                                    <?php echo htmlspecialchars($booking['from_location'] . ' - ' . $booking['to_location']); ?>
                                                </div>
                                                <div class="text-xs ml-5">
                                                    <i class="fas fa-exchange-alt text-gray-400 mr-1"></i>
                                                    <?php echo htmlspecialchars($booking['boarding_point']) . ' → ' . htmlspecialchars($booking['dropping_point']); ?>
                                                </div>
                                            </td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap font-medium text-green-600">
                                                <i class="fas fa-rupee-sign mr-1"></i>
                                                <?php echo number_format($booking['ticket_price'], 2); ?>
                                            </td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $statusClasses = [
                                                    'Confirmed' => 'bg-green-100 text-green-800',
                                                    'Cancelled' => 'bg-red-100 text-red-800',
                                                    'Completed' => 'bg-blue-100 text-blue-800'
                                                ];
                                                $statusIcons = [
                                                    'Confirmed' => '<i class="fas fa-check-circle mr-1"></i>',
                                                    'Cancelled' => '<i class="fas fa-times-circle mr-1"></i>',
                                                    'Completed' => '<i class="fas fa-check-double mr-1"></i>'
                                                ];
                                                $statusClass = $statusClasses[$booking['booking_status']] ?? 'bg-gray-100 text-gray-800';
                                                $statusIcon = $statusIcons[$booking['booking_status']] ?? '<i class="fas fa-question-circle mr-1"></i>';
                                                ?>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo $statusIcon . htmlspecialchars($booking['booking_status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">
                                                <button onclick="viewBooking(<?php echo $booking['id']; ?>)"
                                                    class="text-blue-600 hover:text-blue-900 mr-2 transition duration-150"
                                                    title="View Booking">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                            <i class="fas fa-search-minus text-gray-400 text-3xl mb-2"></i>
                                            <p>No bookings
                                                found<?php echo !empty($searchQuery) ? ' matching "' . htmlspecialchars($searchQuery) . '"' : ''; ?>
                                            </p>
                                            <?php if (!empty($searchQuery)): ?>
                                                <a href="bookings.php"
                                                    class="text-blue-600 hover:text-blue-800 inline-block mt-2">
                                                    <i class="fas fa-times-circle mr-1"></i> Clear Search
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- Booking Details Modal (for all screens) -->
                <div id="bookingDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
                    <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeModal()"></div>
                    <div class="relative flex items-center justify-center min-h-screen p-4">
                        <div
                            class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white modal-content">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900"><i
                                        class="fas fa-info-circle text-blue-600 mr-2"></i>Booking Details</h3>
                                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <!-- Loading indicator -->
                            <div id="loadingIndicator" class="py-8 flex justify-center items-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <span class="ml-2">Loading...</span>
                            </div>

                            <div id="bookingDetailContent" class="space-y-3 hidden">
                                <!-- Details will be filled by JavaScript -->
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button type="button" onclick="closeModal()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-150">
                                    <i class="fas fa-times-circle mr-1"></i> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Modal -->
                <div id="confirmationModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
                    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
                    <div class="relative flex items-center justify-center min-h-screen p-4">
                        <div
                            class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white modal-content">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900"><i
                                        class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>Confirm Action</h3>
                                <button type="button"
                                    onclick="document.getElementById('confirmationModal').classList.add('hidden')"
                                    class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div id="confirmationContent" class="py-4 text-center">
                                Are you sure you want to cancel this booking?
                            </div>

                            <div class="mt-4 flex justify-end space-x-3">
                                <button type="button"
                                    onclick="document.getElementById('confirmationModal').classList.add('hidden')"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition duration-150">
                                    <i class="fas fa-times mr-1"></i> No
                                </button>
                                <button type="button" id="confirmActionBtn"
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition duration-150">
                                    <i class="fas fa-check mr-1"></i> Yes, Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>


    <script>
        // Toggle sidebar function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const body = document.body;

            sidebar.classList.toggle('active');
            backdrop.classList.toggle('active');
            body.classList.toggle('sidebar-open');
        }

        // Close sidebar when clicking navigation links on mobile
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth < 768) {
                    toggleSidebar();
                }
            });
        });


        // Function to view booking details
        function viewBooking(id) {
            // Show the modal
            document.getElementById('bookingDetailsModal').classList.remove('hidden');

            // Show loading indicator
            document.getElementById('loadingIndicator').classList.remove('hidden');
            document.getElementById('bookingDetailContent').classList.add('hidden');

            // In a real application, this would be an AJAX call to fetch booking details
            // For demonstration, we'll simulate a server request with setTimeout
            setTimeout(() => {
                // Find the booking data from the table
                const bookingRows = document.querySelectorAll('tbody tr');
                let bookingData = null;

                for (let row of bookingRows) {
                    if (row.getAttribute('data-booking-id') == id) {
                        const cells = row.querySelectorAll('td');

                        // Extract data from the table row
                        const bookingId = cells[0].textContent.trim();
                        const seatNumber = cells[1].textContent.trim();
                        const passengerName = cells[2].textContent.trim();

                        // Get journey date - might be hidden on mobile
                        let journeyDate = "N/A";
                        const journeyDateCell = row.querySelector('td:nth-child(4)');
                        if (journeyDateCell) {
                            journeyDate = journeyDateCell.textContent.trim();
                        }

                        // Get route info - might be hidden on mobile
                        let routeId = "N/A";
                        let route = "N/A";
                        const routeIdCell = row.querySelector('td:nth-child(5)');
                        const routeCell = row.querySelector('td:nth-child(6)');

                        if (routeIdCell) {
                            routeId = routeIdCell.textContent.trim();
                        }
                        if (routeCell) {
                            route = routeCell.textContent.trim();
                        }

                        const amount = cells[6].textContent.trim();
                        const statusElement = cells[7].querySelector('span');
                        const status = statusElement.textContent.trim();
                        const statusClass = statusElement.className;

                        bookingData = {
                            id: bookingId,
                            seat: seatNumber,
                            passenger: passengerName,
                            journeyDate: journeyDate,
                            routeId: routeId,
                            route: route,
                            amount: amount,
                            status: status,
                            statusClass: statusClass
                        };

                        break;
                    }
                }

                // Hide loading indicator
                document.getElementById('loadingIndicator').classList.add('hidden');

                // If booking data was found, display it
                if (bookingData) {
                    const detailContent = document.getElementById('bookingDetailContent');

                    // Create detailed view
                    detailContent.innerHTML = `
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-gray-600"><i class="fas fa-ticket-alt mr-1"></i> Booking ID:</div>
                    <div class="font-medium">${bookingData.id}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-user mr-1"></i> Passenger:</div>
                    <div class="font-medium">${bookingData.passenger}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-chair mr-1"></i> Seat Number:</div>
                    <div class="font-medium">${bookingData.seat}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-calendar-alt mr-1"></i> Journey Date:</div>
                    <div class="font-medium">${bookingData.journeyDate}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-route mr-1"></i> Route ID:</div>
                    <div class="font-medium">${bookingData.routeId}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-map-signs mr-1"></i> Route:</div>
                    <div class="font-medium">${bookingData.route}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-rupee-sign mr-1"></i> Amount:</div>
                    <div class="font-medium">${bookingData.amount}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-info-circle mr-1"></i> Status:</div>
                    <div class="font-medium">
                        <span class="${bookingData.statusClass}">${bookingData.status}</span>
                    </div>
                </div>
                
                
            `;

                    // Show the details
                    detailContent.classList.remove('hidden');
                } else {
                    // Handle case where booking wasn't found
                    const detailContent = document.getElementById('bookingDetailContent');
                    detailContent.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-circle text-red-500 text-3xl mb-2"></i>
                    <p>Booking information not found.</p>
                </div>
            `;
                    detailContent.classList.remove('hidden');
                }
            }, 500); // Simulate loading delay
        }

        // Close modal function
        function closeModal() {
            document.getElementById('bookingDetailsModal').classList.add('hidden');
        }

        // Function to show confirmation before cancelling
        function confirmCancelBooking(id) {
            // Hide the details modal
            document.getElementById('bookingDetailsModal').classList.add('hidden');

            // Show the confirmation modal
            document.getElementById('confirmationModal').classList.remove('hidden');

            // Set up the confirm button to call the actual cancel function
            document.getElementById('confirmActionBtn').onclick = function () {
                cancelBooking(id);
                document.getElementById('confirmationModal').classList.add('hidden');
            };
        }

        // Function to cancel booking
        function cancelBooking(id) {
            // In a real app, this would be an AJAX call to the server
            // For demonstration, we'll just update the UI

            // Find the booking row
            const bookingRow = document.querySelector(`tr[data-booking-id="${id}"]`);

            if (bookingRow) {
                // Update the status cell
                const statusCell = bookingRow.querySelector('td:nth-child(8)');
                if (statusCell) {
                    statusCell.innerHTML = `
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                    <i class="fas fa-times-circle mr-1"></i>Cancelled
                </span>
            `;
                }

                // Remove the cancel button
                const actionsCell = bookingRow.querySelector('td:nth-child(9)');
                if (actionsCell) {
                    const cancelButton = actionsCell.querySelector('button:nth-child(2)');
                    if (cancelButton) {
                        cancelButton.remove();
                    }
                }
            }

            // Show success message (in a real app, this would only happen after successful server response)
            alert("Booking #" + id + " has been cancelled successfully!");
        }

        // Function to print ticket
        function printTicket(id) {
            // In a real application, this would generate and print a ticket
            // For demonstration purposes, we'll just show an alert
            alert("Printing ticket for booking #" + id);

            // In a real implementation, you might do something like:
            // window.open('print_ticket.php?id=' + id, '_blank');
        }
    </script>
</body>

</html>