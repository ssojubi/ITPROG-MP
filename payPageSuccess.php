<?php
session_start();
include("connection.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: loginpage.php");
    exit();
}

// Check if payment information exists in session
if (!isset($_SESSION['payment_info'])) {
    header("Location: mainpage.php");
    exit();
}

$user_email = $_SESSION['email'];
$payment_info = $_SESSION['payment_info'];

// Extract payment information
$movie_id = $payment_info['movie_id'];
$showtime_id = $payment_info['showtime_id'];
$seats = $payment_info['seats'];
$seats_str = $payment_info['seats_str'];
$total_price = $payment_info['total_price'];
$name = $payment_info['name'];
$booking_reference = $payment_info['booking_reference'];
$cinema_id = $payment_info['cinema_id'];
$movie_title = $payment_info['movie_title'];
$cinema_name = $payment_info['cinema_name'];
$movie_time = $payment_info['movie_time'];

// Track if all operations are successful
$all_success = true;
$error_message = "";

// Begin transaction to ensure all operations succeed or fail together
mysqli_begin_transaction($conn);

try {
    // Process each seat
    foreach ($seats as $seat_string) {
        // Parse seat string 
        preg_match('/^([A-Z])(\d+)$/', trim($seat_string), $matches);
        $seat_row = $matches[1];
        $seat_number = intval($matches[2]);
        
        // Get the seat_id
        $seat_query = "SELECT seat_id FROM seats WHERE cinema_id = '$cinema_id' AND seat_row = '$seat_row' AND seat_number = $seat_number";
        $seat_result = mysqli_query($conn, $seat_query);
        
        if ($seat_data = mysqli_fetch_assoc($seat_result)) {
            $seat_id = $seat_data['seat_id'];
            
            // Update seat status to booked
            $update_query = "UPDATE seats SET status = 'booked' WHERE seat_id = $seat_id";
            mysqli_query($conn, $update_query);
            
            // Calculate single seat price
            $single_seat_price = $total_price / count($seats);
            
            // Insert booking
            $insert_query = "INSERT INTO booking_seats (booking_reference, customer_email, movie_id, showtime_id, seat_id, total_price, booking_timestamp, payment_status) 
                            VALUES ('$booking_reference', '$user_email', $movie_id, $showtime_id, $seat_id, $single_seat_price, NOW(), 'completed')";
            $insert_result = mysqli_query($conn, $insert_query);
            
            if (!$insert_result) {
                $all_success = false;
                $error_message = "Database error: " . mysqli_error($conn);
                break;
            }
        } else {
            $all_success = false;
            $error_message = "Seat not found: " . $seat_string;
            break;
        }
    }
    
    // If we got here, commit the transaction
    mysqli_commit($conn);
    
} catch (Exception $e) {
    // An error occurred, rollback the transaction
    mysqli_rollback($conn);
    $all_success = false;
    $error_message = $e->getMessage();
}

// Clear the payment info from session if successful
if ($all_success) {
    unset($_SESSION['payment_info']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - The Premiere Club</title>
    <link rel="stylesheet" href="payPageStyle.css" />
    <link rel="stylesheet" href="mainpage.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        .success-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .success-icon {
            text-align: center;
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .success-message {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
            color: #333;
        }
        
        .booking-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .booking-details h3 {
            margin-top: 0;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .booking-details p {
            margin: 8px 0;
        }
        
        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #ff4500;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #d03c00;
        }
        
        .btn-secondary {
            background-color: #f1f3f4;
            color: #202124;
        }
        
        .btn-secondary:hover {
            background-color: #e8eaed;
        }
        
        .error-container {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <header>
        <div class="circle-logo"></div>
        <div class="logo">
            <h1>The Premiere Club</h1>
        </div>
    </header>
    
    <div class="success-container">
        <?php if ($all_success): ?>
            <div class="success-icon">✓</div>
            <div class="success-message">Payment Successful!</div>
            
            <div class="booking-details">
                <h3>Booking Details</h3>
                <p><strong>Booking Reference:</strong> <?php echo htmlspecialchars($booking_reference); ?></p>
                <p><strong>Movie:</strong> <?php echo htmlspecialchars($movie_title); ?></p>
                <p><strong>Cinema:</strong> <?php echo htmlspecialchars($cinema_name); ?></p>
                <p><strong>Date:</strong> <?php echo date("m/d/Y"); ?></p>
                <p><strong>Time:</strong> <?php echo htmlspecialchars($movie_time); ?></p>
                <p><strong>Seats:</strong> <?php echo htmlspecialchars($seats_str); ?></p>
                <p><strong>Total Amount:</strong> ₱<?php echo number_format($total_price, 2); ?></p>
            </div>
            
            <p>A confirmation email has been sent to your registered email address. Please keep your booking reference for future reference.</p>
            
            <div class="buttons">
                <a href="mainpage.php" class="btn btn-secondary">Back to Home</a>
                <a href="#viewaccount.php" class="btn btn-primary">View My Bookings</a>
            </div>
        <?php else: ?>
            <div class="error-container">
                <h3>Oops! Something went wrong</h3>
                <p>We encountered an error while processing your booking: <?php echo htmlspecialchars($error_message); ?></p>
                <p>Please contact our customer support for assistance.</p>
            </div>
            
            <div class="buttons">
                <a href="mainpage.php" class="btn btn-secondary">Back to Home</a>
                <!-- <a href="#" class="btn btn-primary">Contact Support</a> -->
            </div>
        <?php endif; ?>
    </div>
</body>
</html>