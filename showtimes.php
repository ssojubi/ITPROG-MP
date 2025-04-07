<?php
session_start();
include("connection.php");

if (!isset($_GET['cinema_id'])) {
    die("Invalid cinema.");
}

$cinema_id = $_GET['cinema_id'];


$cinema_query = "SELECT name FROM cinemas WHERE cinema_id = $cinema_id";
$cinema_result = $conn->query($cinema_query);
$cinema = $cinema_result->fetch_assoc();

// Fetch showtimes for the selected cinema
$sql = "SELECT movies.movie_id, movies.title, movies.poster, showtimes.time 
        FROM showtimes 
        INNER JOIN movies ON showtimes.movie_id = movies.movie_id
        WHERE showtimes.cinema_id = $cinema_id
        ORDER BY showtimes.time ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $cinema['name']; ?> - Showtimes</title>
    <link rel="stylesheet" href="mainpage.css">

    <style>
    .showtime-section {
        display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
            padding: 60px 0;
            background-color: #252524;
            
        }
        .showtime-section h2 {
            font-size: 2.8rem;
            color: white;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .showtime-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .showtime-card {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }
        .showtime-card img {
            width: 200px;
            height: 300px;
            border-radius: 5px;
            margin-right: 20px;
            box-shadow: 20px 35px 35px rgba(0, 0, 0, 0.5);
        }
        .showtime-info h3 {
            font-size: 1.5rem;
            margin: 0;
            color: white;
        }
        .showtime-info p {
            font-size: 1rem;
            color: white;
        }
        .showtime-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .showtime-buttons a {
            padding: 10px 15px;
            font-weight: bold;
            background-color: #ff4500;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .showtime-buttons a:hover {
            background-color: #d03c00;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>

    <section class="showtime-section">
        <h2>Showtimes at <?php echo $cinema['name']; ?></h2>
        <div class="showtime-list">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <div class="showtime-card">
                    <img src="<?php echo htmlspecialchars($row['poster']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                    <div class="showtime-info">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p>Date: </p>
                        <div class="showtime-buttons">
                            <a href="movie.php?movie_id=<?php echo $row['movie_id']; ?>">
                                <?php echo date("h:i A", strtotime($row['time'])); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>