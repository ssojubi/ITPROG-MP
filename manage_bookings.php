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

// Process booking status update if requested
if(isset($_POST['update_booking']) && !empty($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['payment_status'];
    
    // Update booking status
    $update_stmt = $conn->prepare("UPDATE booking_seats SET payment_status = ? WHERE booking_id = ?");
    $update_stmt->bind_param("si", $new_status, $booking_id);
    
    if($update_stmt->execute()) {
        $_SESSION['message'] = "Booking status updated successfully.";
    } else {
        $_SESSION['message'] = "Error updating booking status: " . $conn->error;
    }
    
    // Redirect to refresh the page
    header("Location: manage_bookings.php");
    exit();
}

// Get all bookings for admin view with detailed information
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
LEFT JOIN accounts a ON bs.customer_email = a.email_address
ORDER BY bs.booking_timestamp DESC";

$bookingsResult = $conn->query($bookingsQuery);

// Fetch specific booking details if editing
$editBooking = null;
if(isset($_GET['edit']) && !empty($_GET['id'])) {
    $booking_id = $_GET['id'];
    
    $editQuery = "SELECT 
        bs.booking_id,
        bs.booking_reference,
        bs.customer_email,
        m.title AS movie_title,
        bs.payment_status
    FROM booking_seats bs
    JOIN movies m ON bs.movie_id = m.movie_id
    WHERE bs.booking_id = ?";
    
    $stmt = $conn->prepare($editQuery);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $editBooking = $result->fetch_assoc();
    }
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
    <script>
        function toggleQuickEdit(bookingId) {
            const editForm = document.getElementById(`quick-edit-${bookingId}`);
            if (editForm.style.display === 'block') {
                editForm.style.display = 'none';
            } else {
                // Hide all other edit forms first
                const allForms = document.querySelectorAll('.quick-edit');
                allForms.forEach(form => form.style.display = 'none');
                // Show this form
                editForm.style.display = 'block';
            }
        }
    </script>
</head>
<body>
    <?php include("header.php");?>
    
    <div class="container">
        <?php if(isset($editBooking)): ?>
            <!-- Edit Booking Form -->
            <h1>Edit Booking</h1>
            
            <a href="manage_bookings.php" class="back-btn">Back to All Bookings</a>
            
            <div class="edit-form">
                <form method="post" action="manage_bookings.php">
                    <input type="hidden" name="booking_id" value="<?php echo $editBooking['booking_id']; ?>">
                    
                    <div class="form-group">
                        <label>Booking Reference</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($editBooking['booking_reference']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Customer</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($editBooking['customer_email']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Movie</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($editBooking['movie_title']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_status">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-control" required>
                            <option value="pending" <?php echo ($editBooking['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo ($editBooking['payment_status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo ($editBooking['payment_status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="update_booking" class="submit-btn">Update Booking</button>
                        <a href="manage_bookings.php" class="cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
            
        <?php else: ?>
            <!-- Main Bookings List with Comprehensive Details -->
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
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div>
                            <label for="date_filter">Date:</label>
                            <input type="date" name="date_filter" id="date_filter">
                        </div>
                        <div>
                            <label for="search">Search:</label>
                            <input type="text" name="search" id="search" placeholder="Email, Reference, Movie...">
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
                                        <button onclick="toggleQuickEdit(<?php echo $booking['booking_id']; ?>)" class="edit-btn">Edit</button>
                                        <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this booking? The seat will be marked as available again.');">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                            <button type="submit" name="delete_booking" class="delete-btn">Delete</button>
                                        </form>
                                        
                                        <!-- Quick Edit Form (hidden by default) -->
                                        <div id="quick-edit-<?php echo $booking['booking_id']; ?>" class="quick-edit">
                                            <form method="post" action="manage_bookings.php">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                <div>
                                                    <label for="payment_status_<?php echo $booking['booking_id']; ?>">Payment Status:</label>
                                                    <select name="payment_status" id="payment_status_<?php echo $booking['booking_id']; ?>" required>
                                                        <option value="pending" <?php echo ($booking['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="completed" <?php echo ($booking['payment_status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="failed" <?php echo ($booking['payment_status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                                                    </select>
                                                </div>
                                                <div style="margin-top: 10px;">
                                                    <button type="submit" name="update_booking" class="update-btn">Update</button>
                                                    <button type="button" onclick="toggleQuickEdit(<?php echo $booking['booking_id']; ?>)" class="cancel-edit-btn">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-bookings">No bookings found in the system.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>