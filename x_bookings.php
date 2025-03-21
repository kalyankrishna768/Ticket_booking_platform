<?php
session_start();

// Check if the user is logged in as an agency
if (!isset($_SESSION['email']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

class NewBookingsManager
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "ticket");
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getNewBookings($search = "")
    {
        // Fetch bookings from new_bookings table where agency_email matches the logged-in agency
        $sql = "SELECT 
                    nb.id,
                    nb.ticket_id,
                    nb.user_email,
                    nb.passenger_name,
                    nb.age,
                    nb.gender,
                    nb.phone_number,
                    nb.passenger_email,
                    nb.journey_date,
                    nb.from_location,
                    nb.to_location,
                    nb.boarding_point,
                    nb.boarding_time,
                    nb.dropping_point,
                    nb.dropping_time,
                    nb.seat_no,
                    nb.ticket_price,
                    nb.convenience_fee,
                    nb.total_amount,
                    nb.route_id,
                    nb.bus_id,
                    nb.agency_email,
                    nb.booking_date,
                    nb.booking_status
                FROM 
                    new_bookings nb
                WHERE 
                    nb.agency_email = ?";

        // Add search functionality if search parameter is provided
        if (!empty($search)) {
            $sql .= " AND (
                nb.passenger_name LIKE ? OR
                nb.user_email LIKE ? OR
                nb.seat_no LIKE ? OR 
                nb.from_location LIKE ? OR 
                nb.to_location LIKE ? OR 
                nb.booking_status LIKE ? OR
                nb.passenger_email LIKE ? OR
                nb.phone_number LIKE ?
            )";
        }

        $sql .= " ORDER BY nb.booking_date DESC";

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

    public function processBooking($bookingId, $action)
    {
        // Process booking action (confirm or cancel)
        if ($action === 'confirm') {
            // First, fetch the booking details
            $stmt = $this->conn->prepare("
                SELECT * FROM new_bookings 
                WHERE id = ? AND agency_email = ?
            ");
            $stmt->bind_param("is", $bookingId, $_SESSION['email']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $booking = $result->fetch_assoc();

                // Insert into agency_bookings table
                $stmt = $this->conn->prepare("
                    INSERT INTO agency_bookings 
                    (user_email, agency_email, journey_date, seat_number, ticket_price, 
                    booking_status, boarding_point, dropping_point, route_id) 
                    VALUES (?, ?, ?, ?, ?, 'Confirmed', ?, ?, ?)
                ");
                $stmt->bind_param(
                    "ssssdssi",
                    $booking['user_email'],
                    $booking['agency_email'],
                    $booking['journey_date'],
                    $booking['seat_no'],
                    $booking['ticket_price'],
                    $booking['boarding_point'],
                    $booking['dropping_point'],
                    $booking['route_id']
                );
                $insertResult = $stmt->execute();

                if ($insertResult) {
                    // Update status in new_bookings
                    $stmt = $this->conn->prepare("
                        UPDATE new_bookings 
                        SET booking_status = 'CONFIRMED' 
                        WHERE id = ? AND agency_email = ?
                    ");
                    $stmt->bind_param("is", $bookingId, $_SESSION['email']);
                    return $stmt->execute();
                }
            }
            return false;
        } elseif ($action === 'cancel') {
            // Update status to CANCELLED
            $stmt = $this->conn->prepare("
                UPDATE new_bookings 
                SET booking_status = 'CANCELLED' 
                WHERE id = ? AND agency_email = ?
            ");
            $stmt->bind_param("is", $bookingId, $_SESSION['email']);
            return $stmt->execute();
        }
        return false;
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}

// Process booking action if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['action'])) {
    $bookingsManager = new NewBookingsManager();
    $result = $bookingsManager->processBooking($_POST['booking_id'], $_POST['action']);

    if ($result) {
        header("Location: x_bookings.php?success=1&action=" . $_POST['action']);
        exit();
    } else {
        header("Location: x_bookings.php?error=1&action=" . $_POST['action']);
        exit();
    }
}

// Initialize NewBookingsManager class and get data
$bookingsManager = new NewBookingsManager();
$searchQuery = isset($_GET['search']) ? $_GET['search'] : "";
$newBookings = $bookingsManager->getNewBookings($searchQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Home - New Bookings</title>
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
                <p class="text-sm text-blue-200"><i class="fas fa-bookmark mr-1"></i> New Bookings</p>
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

                <a href="bookings.php" class="flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800">
                    <i class="fas fa-ticket-alt w-5"></i>
                    <span class="ml-3">Bookings</span>
                </a>

                <a href="x_bookings.php" class="flex items-center px-6 py-3 bg-blue-800">
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
                        <h1 class="text-xl font-semibold"><i class="fas fa-clipboard-check mr-2"></i>New Bookings
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

            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mx-4 my-4" role="alert">
                    <p><i class="fas fa-check-circle mr-2"></i>Booking successfully
                        <?php echo $_GET['action'] === 'confirm' ? 'confirmed' : 'cancelled'; ?>!</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mx-4 my-4" role="alert">
                    <p><i class="fas fa-exclamation-circle mr-2"></i>Failed to <?php echo $_GET['action']; ?> booking.
                        Please try again.</p>
                </div>
            <?php endif; ?>

            

            <!-- Bookings Management Content -->
            <main class="p-4 md:p-6">
                <!-- Search Bar (Desktop) -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div class="search-container w-full md:w-80">
                        <form method="GET" class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-search text-gray-500"></i>
                            </span>
                            <input type="text" name="search" placeholder="Search ..." 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>" 
                                   class="w-full pl-10 pr-12 py-2 border rounded-lg focus:outline-none focus:ring-2 bg-white">
                            <button type="submit" 
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 bg-blue-600 text-white rounded-r-lg px-3">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- New Bookings Table - Responsive -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <?php if (empty($newBookings ?? [])): ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-inbox text-gray-400 text-4xl mb-3"></i>
                            <p>No new bookings found.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-hashtag mr-1"></i> ID
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
                                            <i class="fas fa-calendar-day mr-1"></i> Journey Date
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
                                            <i class="fas fa-tag mr-1"></i> Status
                                        </th>
                                        <th
                                            class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-cog mr-1"></i> Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($newBookings as $booking): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                                #<?php echo htmlspecialchars($booking['id']); ?></td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                                <i class="fas fa-chair text-gray-500 mr-1"></i>
                                                <?php echo htmlspecialchars($booking['seat_no']); ?>
                                            </td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <i class="fas fa-user text-gray-500 mr-1"></i>
                                                    <?php echo htmlspecialchars($booking['passenger_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500 hidden md:block">
                                                    <i class="fas fa-phone-alt text-gray-400 mr-1"></i>
                                                    <?php echo htmlspecialchars($booking['phone_number']); ?>
                                                </div>
                                            </td>
                                            <td class="hidden md:table-cell px-4 md:px-6 py-4 whitespace-nowrap">
                                                <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                                <?php echo date('d M Y', strtotime($booking['journey_date'])); ?>
                                            </td>
                                            <td
                                                class="hidden md:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div><i class="fas fa-route text-gray-500 mr-1"></i>
                                                    <?php echo htmlspecialchars($booking['from_location'] . ' - ' . $booking['to_location']); ?>
                                                </div>
                                                <div class="text-xs">
                                                    <i class="fas fa-map-pin text-gray-400 mr-1"></i>
                                                    <?php echo htmlspecialchars($booking['boarding_point']) . ' → ' . htmlspecialchars($booking['dropping_point']); ?>
                                                </div>
                                            </td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap font-medium text-green-600">
                                                <i class="fas fa-rupee-sign mr-1"></i>
                                                <?php echo number_format($booking['total_amount'], 2); ?>
                                            </td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $statusClasses = [
                                                    'PENDING' => 'bg-yellow-100 text-yellow-800',
                                                    'CONFIRMED' => 'bg-green-100 text-green-800',
                                                    'CANCELLED' => 'bg-red-100 text-red-800'
                                                ];
                                                $statusIcons = [
                                                    'PENDING' => '<i class="fas fa-clock mr-1"></i>',
                                                    'CONFIRMED' => '<i class="fas fa-check-circle mr-1"></i>',
                                                    'CANCELLED' => '<i class="fas fa-times-circle mr-1"></i>'
                                                ];
                                                $statusClass = $statusClasses[$booking['booking_status']] ?? 'bg-gray-100 text-gray-800';
                                                $statusIcon = $statusIcons[$booking['booking_status']] ?? '';
                                                ?>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo $statusIcon . htmlspecialchars($booking['booking_status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">
                                                <button onclick="viewBooking(<?php echo $booking['id']; ?>)"
                                                    class="text-blue-600 hover:text-blue-900 mr-2" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($booking['booking_status'] === 'PENDING'): ?>
                                                    <button onclick="confirmBooking(<?php echo $booking['id']; ?>)"
                                                        class="text-green-600 hover:text-green-900 mr-2" title="Confirm Booking">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button onclick="cancelBooking(<?php echo $booking['id']; ?>)"
                                                        class="text-red-600 hover:text-red-900" title="Cancel Booking">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button onclick="showBookingDetails(<?php echo $booking['id']; ?>)"
                                                    class="md:hidden text-gray-600 hover:text-gray-900 ml-2"
                                                    title="More Details">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Booking Details Modal (for mobile) -->
                <div id="bookingDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
                    <div class="fixed inset-0 bg-black bg-opacity-50"
                        onclick="document.getElementById('bookingDetailsModal').classList.add('hidden')"></div>
                    <div class="relative flex items-center justify-center min-h-screen p-4">
                        <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900"><i
                                        class="fas fa-ticket-alt mr-2"></i>Booking Details</h3>
                                <button type="button"
                                    onclick="document.getElementById('bookingDetailsModal').classList.add('hidden')"
                                    class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div id="bookingDetailContent" class="space-y-3">
                                <!-- Details will be filled by JavaScript -->
                            </div>
                            <div class="mt-4 flex justify-end space-x-2">
                                <div id="actionButtons">
                                    <!-- Action buttons will be inserted by JavaScript if needed -->
                                </div>
                                <button type="button"
                                    onclick="document.getElementById('bookingDetailsModal').classList.add('hidden')"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    <i class="fas fa-times mr-1"></i> Close
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

        function showBookingDetails(id) {
            // Find the correct booking data from the page
            const bookingRows = document.querySelectorAll('tbody tr');
            let booking = {};
            let bookingStatus = '';

            for (let row of bookingRows) {
                const cells = row.querySelectorAll('td');
                if (cells[0].textContent.replace('#', '') == id) {
                    const statusElement = cells[6].querySelector('span');
                    bookingStatus = statusElement.textContent.trim();

                    booking = {
                        id: cells[0].textContent,
                        seat: cells[1].textContent.trim(),
                        passenger: cells[2].querySelector('div').textContent.trim(),
                        phone: cells[2].querySelectorAll('div')[1]?.textContent.trim() || 'N/A',
                        amount: cells[5].textContent.trim(),
                        status: bookingStatus
                    };

                    // Try to get journey date and route from hidden cells on mobile
                    const allCells = row.querySelectorAll('td');
                    if (allCells.length > 6) {
                        booking.date = allCells[3].textContent.trim();

                        const routeDiv = allCells[4].querySelectorAll('div');
                        if (routeDiv.length > 0) {
                            booking.route = routeDiv[0].textContent.trim();
                            booking.points = routeDiv[1]?.textContent.trim() || '';
                        }
                    }

                    break;
                }
            }

            const detailContent = document.getElementById('bookingDetailContent');
            detailContent.innerHTML = `
                <div class="grid grid-cols-2 gap-2">
                    <div class="text-gray-600"><i class="fas fa-hashtag mr-1"></i> Booking ID:</div>
                    <div class="font-medium">${booking.id}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-user mr-1"></i> Passenger:</div>
                    <div class="font-medium">${booking.passenger}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-phone-alt mr-1"></i> Phone:</div>
                    <div class="font-medium">${booking.phone}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-chair mr-1"></i> Seat Number:</div>
                    <div class="font-medium">${booking.seat}</div>
                    
                    ${booking.date ? `
                    <div class="text-gray-600"><i class="fas fa-calendar-day mr-1"></i> Journey Date:</div>
                    <div class="font-medium">${booking.date}</div>
                    ` : ''}
                    
                    ${booking.route ? `
                    <div class="text-gray-600"><i class="fas fa-route mr-1"></i> Route:</div>
                    <div class="font-medium">${booking.route}</div>
                    ` : ''}
                    
                    ${booking.points ? `
                    <div class="text-gray-600"><i class="fas fa-map-pin mr-1"></i> Boarding/Dropping:</div>
                    <div class="font-medium">${booking.points}</div>
                    ` : ''}
                    
                    <div class="text-gray-600"><i class="fas fa-rupee-sign mr-1"></i> Amount:</div>
                    <div class="font-medium">${booking.amount}</div>
                    
                    <div class="text-gray-600"><i class="fas fa-tag mr-1"></i> Status:</div>
                    <div class="font-medium">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${booking.status.includes('CONFIRMED') ? 'bg-green-100 text-green-800' :
                    booking.status.includes('CANCELLED') ? 'bg-red-100 text-red-800' :
                        'bg-yellow-100 text-yellow-800'}">
                            ${booking.status.includes('CONFIRMED') ? '<i class="fas fa-check-circle mr-1"></i>' :
                    booking.status.includes('CANCELLED') ? '<i class="fas fa-times-circle mr-1"></i>' :
                        '<i class="fas fa-clock mr-1"></i>'}
                            ${booking.status.replace(/^.*?(?:PENDING|CONFIRMED|CANCELLED)/i, "$&")}
                        </span>
                    </div>
                </div>
            `;

            // Add action buttons if status is PENDING
            const actionButtons = document.getElementById('actionButtons');
            if (bookingStatus.includes('PENDING')) {
                actionButtons.innerHTML = `
                    <button type="button" onclick="confirmBooking(${id})" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fas fa-check-circle mr-1"></i> Confirm
                    </button>
                    <button type="button" onclick="cancelBooking(${id})" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        <i class="fas fa-ban mr-1"></i> Cancel
                    </button>
                `;
            } else {
                actionButtons.innerHTML = '';
            }

            document.getElementById('bookingDetailsModal').classList.remove('hidden');
        }

        // Functions for handling booking actions
        function viewBooking(id) {
            // Implement view functionality
            console.log('View booking:', id);
            showBookingDetails(id);
        }

        function confirmBooking(id) {
            if (confirm('Are you sure you want to confirm this booking?')) {
                // Redirect to processing page or submit via AJAX
                window.location.href = 'process_booking.php?id=' + id + '&action=confirm';
            }
        }

        function cancelBooking(id) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                // Redirect to processing page or submit via AJAX
                window.location.href = 'process_booking.php?id=' + id + '&action=cancel';
            }
        }

        // Initialize sidebar state based on screen size
        function initSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth >= 768) {
                sidebar.classList.add('active');
            } else {
                sidebar.classList.remove('active');
            }
        }

        // Run on page load
        document.addEventListener('DOMContentLoaded', initSidebar);

        // Check for window resize to handle sidebar visibility
        window.addEventListener('resize', function () {
            initSidebar();
        });
    </script>
</body>

</html>