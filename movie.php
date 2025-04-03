<?php
session_start();
include("connection.php");

$movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : null;

if (!$movie_id) {
    echo "No movie selected.";
    exit;
}

$sql = "SELECT * FROM movies WHERE movie_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    echo "Movie not found.";
    exit;
}

$sql = "SELECT s.showtime_id, s.time, s.price, c.name AS cinema_name 
        FROM showtimes s 
        JOIN cinemas c ON s.cinema_id = c.cinema_id 
        WHERE s.movie_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$showtimes_result = $stmt->get_result();
$showtimes = [];
while ($row = $showtimes_result->fetch_assoc()) {
    $showtimes[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($movie["title"]); ?></title>
    <link rel="stylesheet" href="moviePageStyle.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet" />
    <script>
        function selectShowtime(button, showtimeId, cinemaName, time, price) { // *changed to allow storing of showtime when showtime-btn is selected
            // Remove 'selected' class from all buttons
            document.querySelectorAll(".showtime-btn").forEach(btn => btn.classList.remove("selected"));
    
            // Add 'selected' class to clicked button
            button.classList.add("selected");

            document.getElementById("ticket-container").style.display = "block";
            document.getElementById("ticket-name").innerText = cinemaName + " - " + time;
            document.getElementById("ticket-price").innerText = price.toFixed(2);
            document.getElementById("ticket-qty").value = 0;
            document.getElementById("ticket-subtotal").innerText = "0.00";
        }


        function updateSubtotal() {
            let qty = parseInt(document.getElementById("ticket-qty").value);
            let price = parseFloat(document.getElementById("ticket-price").innerText);
            let subtotal = qty * price;
            document.getElementById("ticket-subtotal").innerText = subtotal.toFixed(2);
        }

        function proceedToSeatSelection() { // *changed to send data and change file to seatplan.php
            let qty = document.getElementById("ticket-qty").value;
            if (qty <= 0) {
                alert("Please select at least one ticket.");
                return;
            }

            // Get the selected showtime
            let selectedShowtime = document.querySelector(".showtime-btn.selected");
            if (!selectedShowtime) {
                alert("Please select a showtime.");
                return;
            }

            let showtimeId = selectedShowtime.getAttribute("data-showtime-id");

            // Check if showtimeId exists
            if (!showtimeId) {
                alert("Something went wrong. Please try again.");
                return;
            }

            // Redirect to seat selection page with showtime_id and qty
            window.location.href = `seatplan.php?showtime_id=${showtimeId}&qty=${qty}`;
        }
    </script>
</head>
<body>
    <?php include("header.php");?>

    <div class="movie-content">
        <div class="movie-image">
            <img src="<?php echo htmlspecialchars($movie["poster"]); ?>" alt="<?php echo htmlspecialchars($movie["title"]); ?>">
        </div>
        <div class="movie-details">
            <h1><?php echo htmlspecialchars($movie["title"]); ?></h1>
            <div class="movie-info">Duration: <?php echo htmlspecialchars($movie["duration"]); ?></div>
            <div class="movie-info">Genre: <?php echo htmlspecialchars($movie["genre"]); ?></div>
            <div class="movie-info">Rated: <?php echo htmlspecialchars($movie["rating"]); ?></div>
            <p><?php echo htmlspecialchars($movie["description"]); ?></p>

            <form action="seatplan.php" method="GET">
                <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">

                <div id="showtime-container">
                    <p id="current-date"><?php echo date("l, d F Y"); ?></p>
                    <div class="showtime-options"> <!-- *changed to allow usage of showtime id after update -->
                        <?php foreach ($showtimes as $showtime): ?>
                            <button type="button" class="showtime-btn" 
                                data-showtime-id="<?php echo $showtime['showtime_id']; ?>"
                                onclick="selectShowtime(this, <?php echo $showtime['showtime_id']; ?>, 
                                                        '<?php echo htmlspecialchars($showtime['cinema_name']); ?>', 
                                                        '<?php echo htmlspecialchars($showtime['time']); ?>', 
                                                        <?php echo $showtime['price']; ?>)">
                                <?php echo htmlspecialchars($showtime['cinema_name']) . " - " . htmlspecialchars($showtime['time']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="ticket-container" style="display: none;">
                    <h3>Selected Tickets</h3>
                    <table>
                        <tr>
                            <th>Tickets</th>
                            <th>Cost</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                        <tr>
                            <td id="ticket-name">-</td>
                            <td id="ticket-price">0.00</td>
                            <td>
                                <button type="button" onclick="document.getElementById('ticket-qty').stepDown(); updateSubtotal();">-</button>
                                <input type="number" id="ticket-qty" value="0" min="0" max="40" oninput="this.value = Math.max(0, Math.min(40, this.value)); updateSubtotal();" />
                                <button type="button" onclick="document.getElementById('ticket-qty').stepUp(); updateSubtotal();">+</button>
                            </td>
                            <td id="ticket-subtotal">0.00</td>
                        </tr>
                    </table>

                    <div id="proceed-btn-container"> <!-- *added button to submit data to seatplan.php -->
                        <button id="proceed-btn" type="button" onclick="proceedToSeatSelection()">Proceed to Seat Selection</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
        <a href="#">Login</a> | <a href="#">Sign Up</a>
    </footer>
</body>
</html>