<?php
session_start();
include("connection.php");

$movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : null;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); // Default to today if no date specified

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

// Get showtimes for the selected date
$sql = "SELECT s.showtime_id, s.time, s.price, c.name AS cinema_name, s.showtime_date 
        FROM showtimes s 
        JOIN cinemas c ON s.cinema_id = c.cinema_id 
        WHERE s.movie_id = ? AND DATE(s.showtime_date) = ?
        ORDER BY s.time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $movie_id, $selected_date);
$stmt->execute();
$showtimes_result = $stmt->get_result();
$showtimes = [];
while ($row = $showtimes_result->fetch_assoc()) {
    $showtimes[] = $row;
}

// Get available dates for this movie (for the next 7 days)
$sql = "SELECT DISTINCT DATE(showtime_date) as available_date 
        FROM showtimes 
        WHERE movie_id = ? 
        AND showtime_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY showtime_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$dates_result = $stmt->get_result();
$available_dates = [];
while ($row = $dates_result->fetch_assoc()) {
    $available_dates[] = $row['available_date'];
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
    <style>
        
        

        /* Date selector styles */
        .date-selector-container {
            margin: 30px 0 20px 0;
            margin-left: 50px;
        }
        
        .date-selector-container h3 {
            margin-bottom: 15px;
            color: #ddd;
            font-weight: 500;
        }
        
        .date-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            overflow-x: auto;
            padding-bottom: 10px;
            -ms-overflow-style: none;  
            scrollbar-width: none; 
        }
        
        .date-selector::-webkit-scrollbar {
            display: none;
        }
        
        .date-option {
            background-color: lightgray;
            border: 1px solid #444;
            border-radius: 5px;
            padding: 10px 18px;
            cursor: pointer;
            min-width: 110px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .date-option:hover {
            background-color: #3a3a3a;
        }
        
        .date-option.selected {
            background-color:red;
            color: white;
            border-color: red;
        }
        
        .date-option .day-name {
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
            font-size: 16px;
        }
        
        .date-option .date {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Showtimes section styles */
        .showtimes-section h3 {
            font-size: 20px;
            margin-bottom: 15px;
            margin-left: 50px;
            color: #ddd;
        }
        
        .showtime-options {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 30px;
        }
        
        .showtime-btn {
            background-color:lightgray;
            border: 1px solid #444;
            border-radius: 5px;
            padding: 12px 20px;
            color: #3a3a3a;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .showtime-btn:hover {
            background-color: #3a3a3a;
        }
        
        .showtime-btn.selected {
            background-color: red;
            border-color: red;
            color: white;
        }
        
        .no-showtimes {
            padding: 25px;
            margin-left: 50px;
            text-align: center;
            background-color: #2c2c2c;
            border-radius: 5px;
            color: #aaa;
            margin-bottom: 30px;
        }
        
        /* Ticket selection styles */
        #ticket-container {
            background-color: #2c2c2c;
            border-radius: 8px;
            padding: 20px;
            margin-top: 25px;
            color: white;
        }
        
        #ticket-container h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: white;
        }
        
        #ticket-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        #ticket-container th {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #444;
            color: white;
            font-weight: 500;
        }
        
        #ticket-container td {
            padding: 15px 10px;
        }
        
        #ticket-container input[type="number"] {
            width: 50px;
            text-align: center;
            padding: 8px;
            background-color: #3a3a3a;
            border: 1px solid #555;
            border-radius: 3px;
            color: white;
        }
        
        #ticket-container button {
            background-color: #3a3a3a;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 3px;
            cursor: pointer;
        }
        
        #proceed-btn-container {
            text-align: right;
            margin-top: 20px;
        }
        
        #proceed-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        #proceed-btn:hover {
            background-color: red;
        }
        
        /* Footer styles */
        footer {
            text-align: center;
            padding: 20px;
            background-color: #222;
            color: #aaa;
            margin-top: 40px;
        }
        
        footer a {
            color: #ccc;
            text-decoration: none;
            margin: 0 10px;
        }
        
        footer a:hover {
            color: #fff;
        }
        
        /* Responsive design */
        @media (max-width: 900px) {
            .movie-content {
                flex-direction: column;
            }
            
            .movie-image {
                flex: none;
                max-width: 300px;
                margin: 0 auto 30px;
            }
        }
    </style>
    <script>
        function selectDate(date) {
            window.location.href = `movie.php?movie_id=<?php echo $movie_id; ?>&date=${date}`;
        }

        function selectShowtime(button, showtimeId, cinemaName, time, price) {
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

        function proceedToSeatSelection() {
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

            <div class="date-selector-container">
                <h3>Select Date:</h3>
                <div class="date-selector">
                    <?php 
                    // If no available dates in database yet, show next 7 days
                    if (empty($available_dates)) {
                        for ($i = 0; $i < 7; $i++) {
                            $date = date('Y-m-d', strtotime("+$i days"));
                            $formatted_date = date('M d', strtotime($date));
                            $day_name = date('D', strtotime($date));
                            $selected_class = ($date == $selected_date) ? 'selected' : '';
                            echo "<div class='date-option $selected_class' onclick='selectDate(\"$date\")'>
                                    <span class='day-name'>$day_name</span>
                                    <span class='date'>$formatted_date</span>
                                  </div>";
                        }
                    } else {
                        // Show available dates from database
                        foreach ($available_dates as $date) {
                            $formatted_date = date('M d', strtotime($date));
                            $day_name = date('D', strtotime($date));
                            $selected_class = ($date == $selected_date) ? 'selected' : '';
                            echo "<div class='date-option $selected_class' onclick='selectDate(\"$date\")'>
                                    <span class='day-name'>$day_name</span>
                                    <span class='date'>$formatted_date</span>
                                  </div>";
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="showtimes-section">
                <h3>Showtimes for <?php echo date('l, d F Y', strtotime($selected_date)); ?>:</h3>
                
                <?php if (empty($showtimes)): ?>
                    <div class="no-showtimes">
                        <p>No showtimes available for this date. Please select another date.</p>
                    </div>
                <?php else: ?>
                    <div class="showtime-options">
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
                <?php endif; ?>
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

                <div id="proceed-btn-container">
                    <button id="proceed-btn" type="button" onclick="proceedToSeatSelection()">Proceed to Seat Selection</button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>