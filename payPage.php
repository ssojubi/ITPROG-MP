<?php
session_start();
include("connection.php");

// Get parameters from URL
$movie_id = isset($_GET['movie_id']) ? $_GET['movie_id']: 1;
$showtime_id = isset($_GET['showtime_id']) ? $_GET['showtime_id'] : 1; // Default to 1 if not provided


if (isset($_GET['seats'])) {
    $seats = explode(',', $_GET['seats']);
} else {
    // Default seats for testing
    $seats = array("A1", "A2");
}

// Initialize variables
$movie_title = "";
$movie_date = date("m/d/Y");
$movie_time = "";
$movie_price = 0;
$cinema_name = "";

// Get movie title from database
$movie_sql = "SELECT title FROM movies WHERE movie_id = $movie_id";
$movie_result = mysqli_query($conn, $movie_sql);

if ($movie_result && mysqli_num_rows($movie_result) > 0) {
    $movie_row = mysqli_fetch_assoc($movie_result);
    $movie_title = $movie_row['title'];
} else {
    $movie_title = "Movie not found";
}

// Directly query the showtimes table
$time_sql = "SELECT s.time, s.price FROM showtimes s WHERE s.showtime_id = $showtime_id";
$time_result = mysqli_query($conn, $time_sql);

if ($time_result && mysqli_num_rows($time_result) > 0) {
    $time_row = mysqli_fetch_assoc($time_result);
    $movie_time = $time_row['time'];
    $movie_price = $time_row['price'];
} else {
    $movie_time = "No time found";
    $movie_price = 0;
}

// Separate query for cinema name
$cinema_sql = "SELECT c.name FROM cinemas c 
                  JOIN showtimes s ON c.cinema_id = s.cinema_id 
                  WHERE s.showtime_id = $showtime_id";
$cinema_result = mysqli_query($conn, $cinema_sql);

if ($cinema_result && mysqli_num_rows($cinema_result) > 0) {
    $cinema_row = mysqli_fetch_assoc($cinema_result);
    $cinema_name = $cinema_row['name'];
} else {
    $cinema_name = "Cinema not found";
}

// Calculate total
$num_seats = count($seats);
$total_price = $movie_price * $num_seats;

// Format seats array into comma-separated string
$seats_str = implode(", ", $seats);

// Format time for display (if it exists)
if (!empty($movie_time) && $movie_time != "No time found") {
    $movie_time_display = date("h:i A", strtotime($movie_time));
} else {
    $movie_time_display = "Time not available";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Page - <?php echo htmlspecialchars($movie_title); ?></title>
    <link rel="stylesheet" href="payPageStyle.css" />
    <link rel="stylesheet" href="mainpage.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet" />
</head>

<body>
    <header>
        <div class="circle-logo">
            <img src="logo.png" alt="Logo" />
        </div>
        <div class="logo">
            <h1>The Premiere Club</h1>
        </div>
    </header>
    <div class="payment-cont">
        <div class="billing-cont">
            <div class="billing-text">
                <h2>Billing Address</h2>
            </div>
            <form method="post" action="payPageSuccess.php"> <!-- *changed from process_payment.php ??? -->
                <input type="hidden" name="movie_id" value="<?php echo htmlspecialchars($movie_id); ?>" />
                <input type="hidden" name="showtime_id" value="<?php echo htmlspecialchars($showtime_id); ?>" />
                <input type="hidden" name="seats" value="<?php echo htmlspecialchars($seats_str); ?>" />
                <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($total_price); ?>" />

                <div class="border-cont">
                    <div class="input-group">
                        <input type="text" name="first_name" placeholder="First name" required />
                        <input type="text" name="last_name" placeholder="Last name" required />
                    </div>
                    <input type="text" name="address" placeholder="Address" required />
                    <input type="text" name="apartment" placeholder="Apartment, suite, etc. (optional)" />
                    <div class="input-group">
                        <input type="text" name="postal_code" placeholder="Postal code" required />
                        <input type="text" name="city" placeholder="City" required />
                    </div>
                    <select name="region" required>
                        <option value="">Region</option>
                        <option value="NCR">NCR</option>
                        <option value="Region I">Region I</option>
                        <option value="Region II">Region II</option>
                        <option value="Region III">Region III</option>
                        <option value="Region IV-A">Region IV-A</option>
                        <option value="Region IV-B">Region IV-B</option>
                        <option value="Region V">Region V</option>
                    </select>
                </div>
                <div class="billing-text">
                    <h2>Payment Method</h2>
                </div>
                <div class="border-cont">
                    <label>Credit Card Details</label>
                    <input type="text" name="card_number" placeholder="Card Number" required />
                    <input type="text" name="card_holder" placeholder="Card Holder Name" required />
                    <div class="input-group">
                        <input type="text" name="expiry_date" placeholder="Expiry Date (MM/YY)" required />
                        <input type="text" name="cvv" placeholder="CVV" required />
                    </div>
                </div>
        </div>
        <!-- RIGHT SIDE -->
        <div class="order-cont">
            <div class="billing-text">
                <h2>Order Summary</h2>
            </div>
            <div class="border-cont summary">
                <p><strong>Movie:</strong> <?php echo htmlspecialchars($movie_title); ?></p>
                <p><strong>Cinema:</strong> <?php echo htmlspecialchars($cinema_name); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($movie_date); ?></p>
                <p><strong>Time:</strong> <?php echo htmlspecialchars($movie_time_display); ?></p>
                <p><strong>Seats:</strong> <?php echo htmlspecialchars($seats_str); ?></p>
                <p><strong>Price per Ticket:</strong> ₱<?php echo number_format($movie_price, 2); ?></p>
                <hr />
                <p><strong>Total:</strong> ₱<?php echo number_format($total_price, 2); ?></p>
            </div>
            <button type="submit" class="pay-btn">Pay Now</button>
            </form>
        </div>
    </div>
</body>

</html>