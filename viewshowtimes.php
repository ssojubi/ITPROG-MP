<?php
session_start();
include("connection.php");

if(isset($_SESSION['type']) && $_SESSION['type'] != 'admin')
    header("location:mainpage.php");

if(isset($_SESSION['error_message']))
    $error = $_SESSION['error_message'];

if(isset($_SESSION['movieid'])) {
    $movieid = $_SESSION['movieid'];
    unset($_SESSION['movieid']);
}
else
    $movieid = $_POST['movieid'];

$sql = "SELECT * FROM movies WHERE movie_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $movieid);
$stmt->execute();
$result1 = $stmt->get_result();

$movie = $result1->fetch_assoc();

$sql = "SELECT s.showtime_id AS showtime_id, c.name AS cinema_name, s.time AS show_time, s.price AS show_price, s.showtime_date AS show_date, COUNT(bs.booking_id) AS quantity_booked
FROM showtimes s
JOIN cinemas c
ON s.cinema_id = c.cinema_id
LEFT JOIN booking_seats bs
ON s.showtime_id = bs.showtime_id
WHERE s.movie_id = ?
GROUP BY s.showtime_id
ORDER BY s.showtime_date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $movieid);
$stmt->execute();
$result2 = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - The Premiere Club</title>
    <link rel="stylesheet" href="signuppage.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #252524;
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            background: #333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(255, 255, 255, 0.1);
            width: 350px;
            text-align: center;
            margin: 80px auto;
        }
        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
        }
        .form-group input, select, option, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #666;
            border-radius: 5px;
            font-size: 14px;
            background-color: #444;
            color: white;
            resize: none;
        }
        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
        button {
            width: 150px;
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
        .movie-card {
            background: #444;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            margin-left: 25%;
            margin-right: 25%;
            border-left: 4px solid #ff4500;
            text-align: left;
            display:grid;
            grid-template-areas:
            "title title"
            "img content";
        }
        .movie-card h3 {
            margin-top: 0;
            color: #ff4500;
            grid-area: title;
        }
        .movie-detail {
            display:flex;
            margin-top: 8px;
            margin-bottom: 8px;
            margin-left:10px;
            color: white;
        }
        .info {
            grid-area: content;
        }
        .movie-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        .movie-value {
            flex-gmovie: 1;
        }
        .movie-img {
            margin-top: 8px;
            margin-bottom: 8px;
            color: white;
            border: none;
            border-radius: 10px;
            transition: all ease-in-out 0.5s;
            background-size: cover;
            width: 150px;
            height: 300px;
            object-fit: cover;
            grid-area: img;
        }
        table {
            width: 70%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        table th {
            background-color: #333;
            padding: 12px;
            text-align: center;
            font-weight: 600;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #444;
        }
        table tr:hover {
            background-color: #333;
        }
        .no-results {
            width: 70%;
            text-align: center;
            padding: 40px;
            background-color: #333;
            border-radius: 8px;
            font-style: italic;
            color: #999;
            margin-left: auto;
            margin-right: auto;
        }
        .button-list {
            display: inline-grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .showtimes {
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>
    <form id="previous-form" action="allmovies.php" method="post">
        <button type="submit" name="previous" value="previous">Go Back</button>
    </form>
        <div class="movie-card">
            <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                    
            <span class="movie-value"><img class="movie-img" src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" /></span>

            <div class="info">
                <div class="movie-detail">
                    <span class="movie-label">Duration:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($movie['duration']); ?></span>
                </div>
                        
                <div class="movie-detail">
                    <span class="movie-label">Genre:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($movie['genre']); ?></span>
                </div>
                        
                <div class="movie-detail">
                    <span class="movie-label">Rating:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($movie['rating']); ?></span>
                </div>
                        
                <div class="movie-detail">
                    <span class="movie-label">Description:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($movie['description']); ?></span>
                </div>      
                        
                <div class="movie-detail">
                    <span class="movie-label">Status:</span>
                    <span class="movie-value">
                        <?php if ($movie['show_status'] == 'finished'): ?>
                            <span class="status-completed">Finished</span>
                        <?php elseif ($movie['show_status'] == 'showing'): ?>
                            <span class="status-pending">Now Showing</span>
                        <?php else: ?>
                            <span class="status-failed">Coming Soon</span>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="movie-detail">
                    <span class="movie-label">Trailer Link:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($movie['trailer_link']); ?></span>
                </div>
            </div>
        </div>
    
    <div class="showtimes">
        <h2> Showtimes </h2><br>
        <form id="addshowtime-form" action="addshowtime.php" method="POST">
            <input type="hidden" name="movieid" id="movieid" value="<?php echo $movieid; ?>">
            <button type="submit" name="add" value="add">Add Showtime</button>
        </form>
    </div>

    <?php if($result2->num_rows > 0): ?>
        <table>
            <tr>
                <th>Date</th>
                <th>Cinema</th>
                <th>Time</th>
                <th>Price</th>
                <th>Quantity Booked</th>
                <th>Actions</th>
            </tr>
            <?php while($showtimes = $result2->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($showtimes['show_date'])); ?></td>
                    <td><?php echo $showtimes['cinema_name']; ?></td>
                    <td><?php echo $showtimes['show_time']; ?></td>
                    <td><?php echo $showtimes['show_price']; ?></td>
                    <td><?php echo $showtimes['quantity_booked']; ?></td>
                    <td>
                        <div class="button-list">
                            <form id="editshowtime-form" action="editshowtime.php" method="POST">
                                <input type="hidden" name="showtimeid" id="showtimeid" value="<?php echo $showtimes['showtime_id']; ?>">
                                <button type="submit" name="edit" value="edit">Edit</button>
                            </form>
                            <form id="deleteshowtime-form" action="deleteshowtime.php" method="POST">
                                <input type="hidden" name="movieid" id="movieid" value="<?php echo $movieid; ?>">
                                <input type="hidden" name="showtimeid" id="showtimeid" value="<?php echo $showtimes['showtime_id']; ?>">
                                <button type="submit" name="delete" value="delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="no-results">
            <p>No showtimes currently.</p>
        </div>
    <?php endif; ?>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>