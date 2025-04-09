<?php
session_start();
include("connection.php");

// Redirect if not logged in or not admin
if(!isset($_SESSION['email']) || !isset($_SESSION['type']) || $_SESSION['type'] != 'admin') {
    header("location:login.php");
    exit();
}

// Process booking deletion if requested
if(isset($_POST['delete_booking']) && !empty($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $admin_email = $_SESSION['email'];
    
    // Call the stored procedure
    $stmt = $conn->prepare("CALL DeleteBooking(?, ?, @status)");
    $stmt->bind_param("is", $booking_id, $admin_email);
    $stmt->execute();
    
    // Get output status
    $result = $conn->query("SELECT @status as status");
    $status_row = $result->fetch_assoc();
    $delete_message = $status_row['status'];
    
    // Set session messages for display after redirect
    $_SESSION['message'] = $delete_message;
    
    // Redirect to refresh the page
    header("Location: manage_bookings.php");
    exit();
}

// Start building the base query
$bookingsQuery = "SELECT 
    bs.booking_id,
    bs.booking_reference,
    bs.customer_email,
    m.title AS movie_title,
    c.name AS cinema_name,
    CONCAT(s.seat_row, s.seat_number) AS seat,
    DATE_FORMAT(st.showtime_date, '%d %b %Y') AS show_date,
    TIME_FORMAT(st.time, '%h:%i %p') AS show_time,
    bs.total_price,
    DATE_FORMAT(bs.booking_timestamp, '%d %b %Y %h:%i %p') AS booking_time,
    bs.payment_status,
    a.account_name AS customer_name,
    a.contact_number
FROM booking_seats bs
JOIN movies m ON bs.movie_id = m.movie_id
JOIN showtimes st ON bs.showtime_id = st.showtime_id
JOIN seats s ON bs.seat_id = s.seat_id
JOIN cinemas c ON st.cinema_id = c.cinema_id
LEFT JOIN accounts a ON bs.customer_email = a.email_address";

// Apply filters if provided
$filterConditions = [];
$filterParams = [];
$filterParamTypes = "";

if(isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
    $filterConditions[] = "bs.payment_status = ?";
    $filterParams[] = $_GET['status_filter'];
    $filterParamTypes .= "s";
}

if(isset($_GET['date_filter']) && !empty($_GET['date_filter'])) {
    $filterConditions[] = "DATE(st.showtime_date) = ?";
    $filterParams[] = $_GET['date_filter'];
    $filterParamTypes .= "s";
}

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = "%" . $_GET['search'] . "%";
    $filterConditions[] = "(bs.customer_email LIKE ? OR bs.booking_reference LIKE ? OR m.title LIKE ?)";
    $filterParams[] = $searchTerm;
    $filterParams[] = $searchTerm;
    $filterParams[] = $searchTerm;
    $filterParamTypes .= "sss";
}

// Add WHERE clause only if there are conditions
if(!empty($filterConditions)) {
    $bookingsQuery .= " WHERE " . implode(" AND ", $filterConditions);
}

// Add ORDER BY clause at the end
$bookingsQuery .= " ORDER BY bs.booking_timestamp DESC";

// Prepare and execute the query with possible filters
if(!empty($filterParams)) {
    $stmt = $conn->prepare($bookingsQuery);
    
    // Create a reference array for bind_param
    $bindParams = array($filterParamTypes);
    for($i = 0; $i < count($filterParams); $i++) {
        $bindParams[] = &$filterParams[$i];
    }
    
    // Call bind_param with the reference array
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);
    
    $stmt->execute();
    $bookingsResult = $stmt->get_result();
} else {
    $bookingsResult = $conn->query($bookingsQuery);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - The Premiere Club</title>
    <link rel="stylesheet" href="mainpage.css">
    <link href="bookings.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include("header.php");?>
    
    <div class="container">
        <h1>Manage Bookings</h1>
        
        <a href="viewaccount.php" class="back-btn">Back to Dashboard</a>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <!-- Filter Options -->
        <div class="filter-section">
            <h3>Filter Bookings</h3>
            <form method="get" action="manage_bookings.php">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <div>
                        <label for="status_filter">Status:</label>
                        <select name="status_filter" id="status_filter">
                            <option value="">All Statuses</option>
                            <option value="completed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="failed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    <div>
                        <label for="date_filter">Date:</label>
                        <input type="date" name="date_filter" id="date_filter" value="<?php echo isset($_GET['date_filter']) ? $_GET['date_filter'] : ''; ?>">
                    </div>
                    <div>
                        <label for="search">Search:</label>
                        <input type="text" name="search" id="search" placeholder="Email, Reference, Movie..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="filter-bt-con">
                        <button type="submit" class="submit-btn">Apply Filters</button>
                        <a href="manage_bookings.php" class="cancel-btn">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if($bookingsResult->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Customer</th>
                            <th>Movie</th>
                            <th>Cinema</th>
                            <th>Seat</th>
                            <th>Date & Time</th>
                            <th>Booking Time</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = $bookingsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['booking_reference']); ?></td>
                                <td>
                                    <div class="customer-info"><?php echo htmlspecialchars($booking['customer_email']); ?></div>
                                    <?php if(!empty($booking['customer_name'])): ?>
                                        <div class="customer-info"><small><?php echo htmlspecialchars($booking['customer_name']); ?></small></div>
                                    <?php endif; ?>
                                    <?php if(!empty($booking['contact_number'])): ?>
                                        <div class="customer-info"><small>Contact: <?php echo htmlspecialchars($booking['contact_number']); ?></small></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                                <td><?php echo htmlspecialchars($booking['cinema_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['seat']); ?></td>
                                <td><?php echo htmlspecialchars($booking['show_date'] . ' at ' . $booking['show_time']); ?></td>
                                <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                                <td>₱<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td>
                                    <?php if($booking['payment_status'] == 'completed'): ?>
                                        <span class="status-completed">Completed</span>
                                    <?php elseif($booking['payment_status'] == 'pending'): ?>
                                        <span class="status-pending">Pending</span>
                                    <?php else: ?>
                                        <span class="status-failed">Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-cell">
                                    <a href="edit_bookings.php?id=<?php echo $booking['booking_id']; ?>" class="edit-btn">Edit</a>
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this booking? The seat will be marked as available again.');">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                        <button type="submit" name="delete_booking" class="delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-bookings">No bookings found in the system.</div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>