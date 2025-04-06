<?php
// Database connection
session_start();
include("connection.php");

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    // Store current URL in session to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: loginpage.php");
    exit();
}

// Get movie & showtime ID
$showtime_id = isset($_GET['showtime_id']) ? intval($_GET['showtime_id']) : 1;
$qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1; // *added to handle the number of tickets
if (!isset($_GET['showtime_id']) || empty($_GET['showtime_id'])) {
    die("Error: No showtime selected. Please go back and select a showtime.");
}

// Get cinema ID and movie ID
$sql = "SELECT cinema_id, movie_id FROM showtimes WHERE showtime_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $showtime_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$showtime = mysqli_fetch_assoc($result);
$cinema_id = $showtime['cinema_id'];
$movie_id = $showtime['movie_id'];
mysqli_stmt_close($stmt);

// Get movie title
$sql = "SELECT title FROM movies WHERE movie_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $movie_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$movie = mysqli_fetch_assoc($result);
$movie_title = $movie['title'];
mysqli_stmt_close($stmt);

// Get all seats with their status
$sql = "SELECT s.seat_id, s.seat_row, s.seat_number, 
               CASE WHEN bs.seat_id IS NOT NULL THEN 'sold' ELSE 'available' END AS seat_stat
        FROM seats s
        LEFT JOIN (
            SELECT bs.seat_id
            FROM booking_seats bs
            WHERE bs.showtime_id = ? AND bs.payment_status = 'completed'
        ) bs ON s.seat_id = bs.seat_id
        WHERE s.cinema_id = ?
        ORDER BY s.seat_row, s.seat_number";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $showtime_id, $cinema_id);
mysqli_stmt_execute($stmt);
$seats_result = mysqli_stmt_get_result($stmt);
$seats = [];
while ($seat = mysqli_fetch_assoc($seats_result)) {
    $seats[$seat['seat_row']][] = $seat;
}
mysqli_stmt_close($stmt);

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie_title); ?> - Select Your Seat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f9f9f9;
            padding: 20px;
        }

        .screen {
            background-color: red;
            color: white;
            padding: 10px;
            font-weight: bold;
            margin-bottom: 80px;
        }

        .seat-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .seat-row {
            display: flex;
            justify-content: center;
            margin-bottom: 5px;
        }

        .seat-label {
            font-weight: bold;
            margin-right: 10px;
            margin-top: 13px;
        }

        .seat {
            width: 30px;
            height: 30px;
            margin: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
        }

        .sold {
            background-color: rgb(168, 166, 166);
            color: white;
            cursor: not-allowed;
        }

        .available {
            background-color: #444444;
            color: white;
            cursor: pointer;
        }

        .selected {
            background-color: red;
            color: white;
        }

        .legend {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin: 0 15px;
        }

        .legend-item .seat {
            width: 20px;
            height: 20px;
            margin-right: 5px;
        }

        .back-btn-container {
            text-align: left;
            margin-bottom: 15px;
        }

        .back-btn {
            background-color: #ff3b3b;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            border-radius: 15px;
        }

        .pay-cont {
            text-align: center;
            margin-top: 30px;
        }

        .pay-btn {
            background-color: red;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }

        .pay-btn:disabled {
            background-color: #ff9999;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <h1><?php echo htmlspecialchars($movie_title); ?> - Select Your Seat</h1>
    <div class="back-btn-container">
        <button class="back-btn" onclick="history.back();">&#8592; Go back</button>
    </div>

    <div class="screen">SCREEN</div>

    <div class="seat-container">
        <?php foreach ($seats as $row_label => $row): ?>
            <div class="seat-row">
                <span class="seat-label"><?php echo $row_label; ?></span>
                <?php foreach ($row as $seat): ?>
                    <?php
                    // Define seat classes based on booking status
                    $seat_class = ($seat['seat_stat'] === 'sold') ? 'seat sold' : 'seat available';
                    $seat_code = $row_label . $seat['seat_number'];
                    ?>
                    <div class="<?php echo $seat_class; ?>" data-seat="<?php echo $seat_code; ?>">
                        <?php echo $seat['seat_number']; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="legend">
        <div class="legend-item">
            <div class="seat selected"></div> Your Seat
        </div>
        <div class="legend-item">
            <div class="seat available"></div> Available
        </div>
        <div class="legend-item">
            <div class="seat sold"></div> Sold
        </div>
    </div>
    <div class="pay-cont">
        <button id="pay-btn" class="pay-btn" onclick="proceedToPayment()" disabled>
            Proceed to Payment
        </button>
    </div>
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>

</body>

</html>
<script>
    // Initialize array to store selected seats
    let selectedSeats = [];
    let maxSeats = <?php echo $qty; ?>; // *added to handle the number of tickets to be sold 'qty'

    document.addEventListener('DOMContentLoaded', function() {
        const availableSeats = document.querySelectorAll('.seat.available');

        availableSeats.forEach(seat => {
            seat.addEventListener('click', function() {
                const seatCode = this.getAttribute('data-seat');

                if (this.classList.contains('selected')) {
                    // Deselect seat
                    this.classList.remove('selected');
                    selectedSeats = selectedSeats.filter(s => s !== seatCode);
                } else {
                    // Check if maximum seats are already selected
                    if (selectedSeats.length >= maxSeats) {
                        alert(`You can only select ${maxSeats} seat(s).`);
                        return;
                    }
                    // Select seat
                    this.classList.add('selected');
                    selectedSeats.push(seatCode);
                }

                // Update button state
                updateProceedButton();
            });
        });

        // Initial button state update
        updateProceedButton();
    });

    function updateProceedButton() {
        const proceedBtn = document.getElementById('pay-btn');

        if (selectedSeats.length === maxSeats) {
            proceedBtn.disabled = false;
            proceedBtn.style.opacity = '1';
        } else {
            proceedBtn.disabled = true;
            proceedBtn.style.opacity = '0.5';
        }
    }

    function proceedToPayment() {
        if (selectedSeats.length !== maxSeats) {
            alert(`Please select exactly ${maxSeats} seat(s).`);
            return;
        }

        // Get the showtime_id from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const showtime_id = urlParams.get('showtime_id');
        const movie_id = <?php echo $movie_id; ?>;

        // Redirect to payment page with selected seats
        window.location.href = `payPage.php?showtime_id=${showtime_id}&seats=${selectedSeats.join(',')}&movie_id=${movie_id}`;
    }
</script>