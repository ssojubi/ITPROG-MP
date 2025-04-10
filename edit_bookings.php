<?php
session_start();
include("connection.php");

// Redirect if not logged in or not admin
if(!isset($_SESSION['email']) || !isset($_SESSION['type']) || $_SESSION['type'] != 'admin') {
    header("location:login.php");
    exit();
}

// Check if booking ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("location:manage_bookings.php");
    exit();
}

$booking_id = $_GET['id'];

// Process form submission
if(isset($_POST['update_booking'])) {
    $seat_id = $_POST['seat_id'];
    $showtime_id = $_POST['showtime_id'];
    $payment_status = $_POST['payment_status'];
    
    // Validation
    $errors = [];
    
    if(empty($seat_id)) {
        $errors[] = "Seat is required";
    }
    
    if(empty($showtime_id)) {
        $errors[] = "Showtime is required";
    }
    
    // If no errors, update booking
    if(empty($errors)) {
        // First check if the chosen seat is available for the selected showtime
        if($seat_id != $_POST['original_seat_id'] || $showtime_id != $_POST['original_showtime_id']) {
            $checkSeatQuery = "SELECT * FROM booking_seats 
                              WHERE seat_id = ? AND showtime_id = ? AND booking_id != ?";
            $stmt = $conn->prepare($checkSeatQuery);
            $stmt->bind_param("iii", $seat_id, $showtime_id, $booking_id);
            $stmt->execute();
            $stmt->store_result();
            
            if($stmt->num_rows > 0) {
                $errors[] = "The selected seat is already booked for this showtime";
            }
            $stmt->close();
        }
        
        if(empty($errors)) {
            // Update booking details
            $updateQuery = "UPDATE booking_seats SET 
                          seat_id = ?,
                          showtime_id = ?, 
                          payment_status = ?
                          WHERE booking_id = ?";
            
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("iisi", $seat_id, $showtime_id, $payment_status, $booking_id);
            
            if($stmt->execute()) {
                $success = "Booking updated successfully!";
            } else {
                $error = "Error updating booking: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Get booking data
$query = "SELECT 
    bs.booking_id,
    bs.booking_reference,
    bs.customer_email,
    bs.movie_id,
    bs.showtime_id,
    bs.seat_id,
    m.title AS movie_title,
    c.cinema_id,
    c.name AS cinema_name,
    s.seat_row,
    s.seat_number,
    DATE_FORMAT(st.showtime_date, '%Y-%m-%d') AS showtime_date,
    st.time,
    bs.total_price,
    bs.payment_status,
    a.account_name AS customer_name
FROM booking_seats bs
JOIN movies m ON bs.movie_id = m.movie_id
JOIN showtimes st ON bs.showtime_id = st.showtime_id
JOIN seats s ON bs.seat_id = s.seat_id
JOIN cinemas c ON st.cinema_id = c.cinema_id
LEFT JOIN accounts a ON bs.customer_email = a.email_address
WHERE bs.booking_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    header("location:manage_bookings.php");
    exit();
}

$booking = $result->fetch_assoc();
$stmt->close();

// Get available seats for the current cinema
$seatsQuery = "SELECT s.seat_id, s.seat_row, s.seat_number, s.cinema_id
              FROM seats s
              WHERE s.cinema_id = ?
              ORDER BY s.seat_row, s.seat_number";

$stmt = $conn->prepare($seatsQuery);
$stmt->bind_param("i", $booking['cinema_id']);
$stmt->execute();
$seatsResult = $stmt->get_result();
$stmt->close();

// Get showtimes for the same movie, include cinema_id for each showtime
$showtimesQuery = "SELECT st.showtime_id, st.showtime_date, st.time, c.name AS cinema_name, c.cinema_id
                  FROM showtimes st
                  JOIN cinemas c ON st.cinema_id = c.cinema_id
                  WHERE st.movie_id = ?
                  ORDER BY st.showtime_date, st.time";

$stmt = $conn->prepare($showtimesQuery);
$stmt->bind_param("i", $booking['movie_id']);
$stmt->execute();
$showtimesResult = $stmt->get_result();
$showtimes = [];
while($row = $showtimesResult->fetch_assoc()) {
    $showtimes[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - The Premiere Club</title>
    <link rel="stylesheet" href="mainpage.css">
    <link rel="stylesheet" href="edit_user.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Edit Booking</h1>
            <a href="manage_bookings.php" class="back-button">← Back to Bookings</a>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($errors) && !empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach($errors as $err): ?>
                        <li><?php echo $err; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="booking-meta">
            <p><strong>Booking Reference:</strong> <?php echo htmlspecialchars($booking['booking_reference']); ?></p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($booking['customer_name'] ?? $booking['customer_email']); ?></p>
            <p><strong>Movie:</strong> <?php echo htmlspecialchars($booking['movie_title']); ?></p>
            <p><strong>Current Cinema:</strong> <?php echo htmlspecialchars($booking['cinema_name']); ?></p>
            <p><strong>Price:</strong> ₱<?php echo number_format($booking['total_price'], 2); ?></p>
        </div>
        
        <div class="form-card">
            <form method="POST">
                <input type="hidden" name="original_seat_id" value="<?php echo $booking['seat_id']; ?>">
                <input type="hidden" name="original_showtime_id" value="<?php echo $booking['showtime_id']; ?>">
                <input type="hidden" id="original_cinema_id" value="<?php echo $booking['cinema_id']; ?>">
                
                <div class="form-section">
                    <h3 class="section-title">Booking Details</h3>
                    
                    <div class="form-group">
                        <label for="showtime_id">Showtime</label>
                        <select id="showtime_id" name="showtime_id" class="form-control" required>
                            <?php foreach($showtimes as $showtime): ?>
                                <?php 
                                $formattedDate = date('M d, Y', strtotime($showtime['showtime_date']));
                                $formattedTime = date('h:i A', strtotime($showtime['time']));
                                $isSelected = $showtime['showtime_id'] == $booking['showtime_id'];
                                ?>
                                <option value="<?php echo $showtime['showtime_id']; ?>" 
                                       data-cinema-id="<?php echo $showtime['cinema_id']; ?>"
                                       <?php echo $isSelected ? 'selected' : ''; ?>>
                                    <?php echo $formattedDate . ' at ' . $formattedTime . ' - ' . $showtime['cinema_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text">Note: Changing the showtime may affect seat availability and cinema layout</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="seat_id">Seat</label>
                        <select id="seat_id" name="seat_id" class="form-control" required>
                            <?php $seatsResult->data_seek(0); ?>
                            <?php while($seat = $seatsResult->fetch_assoc()): ?>
                                <?php 
                                // Check if this seat is booked for the same showtime (excluding current booking)
                                $seatBookedQuery = "SELECT * FROM booking_seats 
                                                   WHERE seat_id = ? AND showtime_id = ? AND booking_id != ?";
                                $stmt = $conn->prepare($seatBookedQuery);
                                $stmt->bind_param("iii", $seat['seat_id'], $booking['showtime_id'], $booking_id);
                                $stmt->execute();
                                $stmt->store_result();
                                $isBooked = $stmt->num_rows > 0;
                                $stmt->close();
                                
                                $isSelected = $seat['seat_id'] == $booking['seat_id'];
                                $disabled = $isBooked && !$isSelected ? 'disabled' : '';
                                $label = $seat['seat_row'] . $seat['seat_number'];
                                if($isBooked && !$isSelected) {
                                    $label .= ' (Unavailable)';
                                }
                                ?>
                                <option value="<?php echo $seat['seat_id']; ?>" 
                                        <?php echo $isSelected ? 'selected' : ''; ?> 
                                        <?php echo $disabled; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Payment Status</h3>
                    
                    <div class="form-group">
                        <label for="payment_status">Status</label>
                        <select id="payment_status" name="payment_status" class="form-control">
                            <option value="pending" <?php echo $booking['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $booking['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $booking['payment_status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" name="update_booking" class="btn btn-primary">Update Booking</button>
                </div>
            </form>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>

    <script>
        // Add dynamic seat validation when showtime changes
        document.getElementById('showtime_id').addEventListener('change', function() {
            const showtimeId = this.value;
            const originalShowtimeId = <?php echo $booking['showtime_id']; ?>;
            const bookingId = <?php echo $booking_id; ?>;
            const selectedOption = this.options[this.selectedIndex];
            const newCinemaId = selectedOption.getAttribute('data-cinema-id');
            const originalCinemaId = document.getElementById('original_cinema_id').value;
            
            // Check if showtime has changed
            if(showtimeId != originalShowtimeId) {
                updateSeatsForCinema(newCinemaId, showtimeId, bookingId);
            }
        });
        
        function updateSeatsForCinema(cinemaId, showtimeId, bookingId) {
            // First, get all seats for the new cinema
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'get_cinema_seats.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if(this.status === 200) {
                    const seats = JSON.parse(this.responseText);
                    const seatSelect = document.getElementById('seat_id');
                    
                    // Clear current options
                    seatSelect.innerHTML = '';
                    
                    // Now get booked seats for this showtime
                    const xhr2 = new XMLHttpRequest();
                    xhr2.open('POST', 'check_seat_availability.php', true);
                    xhr2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr2.onload = function() {
                        if(this.status === 200) {
                            const bookedSeats = JSON.parse(this.responseText);
                            
                            // Add new options
                            seats.forEach(function(seat) {
                                const option = document.createElement('option');
                                option.value = seat.seat_id;
                                
                                // Check if this seat is booked
                                const isBooked = bookedSeats.includes(parseInt(seat.seat_id));
                                let label = seat.seat_row + seat.seat_number;
                                
                                if(isBooked) {
                                    label += ' (Unavailable)';
                                    option.disabled = true;
                                }
                                
                                option.textContent = label;
                                seatSelect.appendChild(option);
                            });
                            
                            // Select first available seat if any
                            for(let i = 0; i < seatSelect.options.length; i++) {
                                if(!seatSelect.options[i].disabled) {
                                    seatSelect.selectedIndex = i;
                                    break;
                                }
                            }
                        }
                    };
                    xhr2.send('showtime_id=' + showtimeId + '&booking_id=' + bookingId);
                }
            };
            xhr.send('cinema_id=' + cinemaId);
        }
    </script>
</body>
</html>