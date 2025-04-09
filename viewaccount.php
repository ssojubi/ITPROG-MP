<?php
session_start();
include("connection.php");

// Redirect if not logged in
if(!isset($_SESSION['email'])) {
    header("location:login.php");
    exit();
}

// Admin check
$isAdmin = isset($_SESSION['type']) && $_SESSION['type'] == 'admin';

// If not admin, get user information
if(!$isAdmin) {
    $email = $_SESSION['email'];
    
    $sql = "SELECT account_name, birth_date, contact_number FROM accounts WHERE email_address = '$email'";
    $result = $conn->query($sql);
    list($name, $birthdate, $contact) = mysqli_fetch_row($result);
    
    // Get user bookings
    $bookingsQuery = "SELECT 
        bs.booking_reference,
        m.title AS movie_title,
        DATE_FORMAT(st.showtime_date, '%d %b %Y') AS show_date,
        TIME_FORMAT(st.time, '%h:%i %p') AS show_time,
        GROUP_CONCAT(DISTINCT CONCAT(s.seat_row, s.seat_number) ORDER BY s.seat_row, s.seat_number SEPARATOR ', ') AS seats,
        bs.total_price,
        bs.booking_timestamp,
        bs.payment_status,
        c.name
    FROM booking_seats bs
    JOIN movies m ON bs.movie_id = m.movie_id
    JOIN showtimes st ON bs.showtime_id = st.showtime_id
    JOIN seats s ON bs.seat_id = s.seat_id
    JOIN cinemas c ON st.cinema_id = c.cinema_id
    WHERE bs.customer_email = ?
    GROUP BY bs.booking_reference, m.title, st.showtime_date, st.time, bs.total_price, bs.booking_timestamp, bs.payment_status, c.name
    ORDER BY bs.booking_timestamp DESC";
    
    $stmt = mysqli_prepare($conn, $bookingsQuery);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $bookingsResult = mysqli_stmt_get_result($stmt);
} else {
    // For admin, get basic info
    $email = $_SESSION['email'];
    
    $sql = "SELECT account_name, birth_date, contact_number FROM accounts WHERE email_address = '$email'";
    $result = $conn->query($sql);
    list($name, $birthdate, $contact) = mysqli_fetch_row($result);
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isAdmin ? "Admin Dashboard" : "Account"; ?> - The Premiere Club</title>
    <link rel="stylesheet" href="signuppage.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #252524;
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        label {
            text-align: left;
            display: block;
            font-weight: bold;
        }
        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #ff4500;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #e68900;
        }
        .accountinfo {
            display: grid;
            grid-template-areas: "details bookings";
            grid-template-columns: 1fr 3fr;
        }
        .accountinfo > div.accountdetails {
            grid-area: details;
        }
        .accountinfo > div.accountbookings {
            grid-area: bookings;
        }
        .accountinfo p {
            text-align: left;
            margin-bottom: 15px;
            display: block;
        }
        .accountdetails, .accountbookings{
            background: #333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(255, 255, 255, 0.1);
            text-align: left;
            margin: 20px;
        }
        h2 {
            margin-bottom: 15px;
        }
        .booking-card {
            background: #444;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #ff4500;
        }
        .booking-card h3 {
            margin-top: 0;
            color: #ff4500;
        }
        .booking-detail {
            display: flex;
            margin-bottom: 8px;
        }
        .booking-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        .booking-value {
            flex-grow: 1;
        }
        .status-completed {
            background-color: #4CAF50;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
        }
        .status-pending {
            background-color: #FFC107;
            color: black;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
        }
        .status-failed {
            background-color: #F44336;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
        }
        .no-bookings {
            text-align: center;
            padding: 30px;
            font-style: italic;
            color: #999;
        }
        
        /* Admin specific styles */
        .admin-dashboard {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .admin-card {
            background: #444;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            border-bottom: 4px solid #ff4500;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(255, 69, 0, 0.3);
        }
        .admin-card h3 {
            color: #ff4500;
            margin-bottom: 15px;
        }
        .admin-card p {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .admin-card button {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>
    
    <section class="hero">
    </section>
    
    <div class="accountinfo">
        <div class="accountdetails">
            <h2><?php echo $isAdmin ? "Admin Details" : "Account Details"; ?></h2>
            <label for="name">Full Name</label>
            <p><?php echo htmlspecialchars($name); ?></p>
            <label for="email">Email Address</label>
            <p><?php echo htmlspecialchars($email); ?></p>
            <label for="contact">Birth Date</label>
            <p><?php echo htmlspecialchars($birthdate); ?></p>
            <label for="birthdate">Contact Number</label>
            <p><?php echo htmlspecialchars($contact); ?></p>

            <form id="edit-details" action="editaccount.php">
                <button type="submit" name="editdetails" value="editdetails">Edit Details</button>
            </form>
            <form id="change-password" action="changepassword.php">
                <button type="submit" name="changepass" value="changepass">Change Password</button>
            </form>
            
        </div>
        
        <div class="accountbookings">
            <?php if($isAdmin): ?>
                <h2>Admin Dashboard</h2>
                <div class="admin-dashboard">
                    <div class="admin-card">
                        <h3>Users</h3>
                        <form action="manage_users.php">
                            <button type="submit">Manage Users</button>
                        </form>
                    </div>
                    
                    <div class="admin-card">
                        <h3>Bookings</h3>
                        <form action="manage_bookings.php">
                            <button type="submit">Manage Bookings</button>
                        </form>
                    </div>
                    
                    <div class="admin-card">
                        <h3>Movies</h3>
                        <form action="allMovies.php">
                            <button type="submit">Manage Movies</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <h2>Bookings</h2>
                <?php if (mysqli_num_rows($bookingsResult) > 0): ?>
                    <?php while ($booking = mysqli_fetch_assoc($bookingsResult)): ?>
                        <div class="booking-card">
                            <h3><?php echo htmlspecialchars($booking['movie_title']); ?></h3>
                            
                            <div class="booking-detail">
                                <span class="booking-label">Booking Ref:</span>
                                <span class="booking-value"><?php echo htmlspecialchars($booking['booking_reference']); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <span class="booking-label">Cinema:</span>
                                <span class="booking-value"><?php echo htmlspecialchars($booking['name']); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <span class="booking-label">Date & Time:</span>
                                <span class="booking-value"><?php echo htmlspecialchars($booking['show_date'] . ' at ' . $booking['show_time']); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <span class="booking-label">Seats:</span>
                                <span class="booking-value"><?php echo htmlspecialchars($booking['seats']); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <span class="booking-label">Total Price:</span>
                                <span class="booking-value">₱<?php echo number_format($booking['total_price'], 2); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <span class="booking-label">Booked On:</span>
                                <span class="booking-value"><?php echo date('M d, Y g:i A', strtotime($booking['booking_timestamp'])); ?></span>
                            </div>
                            
                            <div class="booking-detail">
                                <span class="booking-label">Status:</span>
                                <span class="booking-value">
                                    <?php if ($booking['payment_status'] == 'completed'): ?>
                                        <span class="status-completed">Completed</span>
                                    <?php elseif ($booking['payment_status'] == 'pending'): ?>
                                        <span class="status-pending">Pending</span>
                                    <?php else: ?>
                                        <span class="status-failed">Failed</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-bookings">You haven't made any bookings yet.</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>