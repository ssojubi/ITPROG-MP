<?php
session_start();
include("connection.php");

if(isset($_POST['previous']))
    unset($_SESSION['error_message']);

if(isset($_SESSION['error_message']))
    $error = $_SESSION['error_message'];

$sql = "";

if(isset($_POST['status']))
    $status = $_POST['status'];
else
    $status = " ";

if(isset($_SESSION['type']) && $_SESSION['type'] == 'admin') {
    $sql = "SELECT * FROM movies WHERE show_status = ? ORDER BY title";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
}
else {
    $sql = "SELECT movie_id, title, poster, description, trailer_link FROM movies WHERE show_status = 'showing' OR 'comingsoon'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premiere Club - Movies</title>
    <link rel="stylesheet" href="mainpage.css">
    <style>
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            box-shadow: 20px 35px 35px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            max-width: 1200px;
            width: 90%;
            border-radius: 10px;
        }
        .popup iframe {
            width: 100%;
            height: 500px; 
        }
        .popup .close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 30px;
            background: none;
            border: none;
            color: black;
            z-index: 1002; 
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000; 
        }

        .popup button {
            background-color: #ff4500;
            color: white;
            padding: 10px 20px;
            font-size: 1.2rem;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin-top: 10px; 
        }

        .popup h2, p {
            margin-top: 10px; 
        }

        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }

        .movie-card {
            background: #444;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            width: 90%;
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
            flex-grow: 1;
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
        .movie-list-admin {
            display: inline-grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            justify-items: center;
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
        .button-list {
            display: inline-grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>

    <?php if(isset($_SESSION['type']) && $_SESSION['type'] == 'admin'): ?>
    <section class="movie-section" id="movie">
        <div class="button-list">
            <form method="post" action="allmovies.php">
                <input type="hidden" name="status" value="finished">
                <button type="submit" name="finished" value="finished">Finished</button>
            </form>

            <form method="post" action="allmovies.php">
                <input type="hidden" name="status" value="showing">
                <button type="submit" name="finished" value="finished">Showing</button>
            </form>

            <form method="post" action="allmovies.php">
                <input type="hidden" name="status" value="comingsoon">
                <button type="submit" name="finished" value="finished">Coming Soon</button>
            </form>
        </div>

        <form method="post" action="addmovie.php">
            <button type="submit" name="addmovie" value="addmovie">Add Movie</button>
        </form>

        <div class="error" id="error"><?php if(isset($_SESSION['error_message'])){ echo $error; }?></div><br>

        <div class="movie-list-admin">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <div class="movie-card">
                    <h3 class="movie-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    
                    <span class="movie-value"><img class="movie-img" src="<?php echo htmlspecialchars($row['poster']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" /></span>

                    <div class="info">
                        <div class="movie-detail">
                            <span class="movie-label">Duration:</span>
                            <span class="movie-value"><?php echo htmlspecialchars($row['duration']); ?></span>
                        </div>
                        
                        <div class="movie-detail">
                            <span class="movie-label">Genre:</span>
                            <span class="movie-value"><?php echo htmlspecialchars($row['genre']); ?></span>
                        </div>
                        
                        <div class="movie-detail">
                            <span class="movie-label">Rating:</span>
                            <span class="movie-value"><?php echo htmlspecialchars($row['rating']); ?></span>
                        </div>
                        
                        <div class="movie-detail">
                            <span class="movie-label">Description:</span>
                            <span class="movie-value"><?php echo htmlspecialchars($row['description']); ?></span>
                        </div>      
                        
                        <div class="movie-detail">
                            <span class="movie-label">Status:</span>
                            <span class="movie-value">
                                <?php if ($row['show_status'] == 'finished'): ?>
                                    <span class="status-completed">Finished</span>
                                <?php elseif ($row['show_status'] == 'showing'): ?>
                                    <span class="status-pending">Now Showing</span>
                                <?php else: ?>
                                    <span class="status-failed">Coming Soon</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="movie-detail">
                            <span class="movie-label">Trailer Link:</span>
                            <span class="movie-value"><?php echo htmlspecialchars($row['trailer_link']); ?></span>
                        </div>
                        
                        <div class="button-list">
                            <form method="post" action="viewshowtimes.php">
                                <input type="hidden" name="movieid" value="<?php echo htmlspecialchars($row['movie_id']); ?>">
                                <button type="submit" name="viewshowtimes" value="viewshowtimes">View Showtimes</button>
                            </form>

                            <form method="post" action="editmovie.php">
                                <input type="hidden" name="movieid" value="<?php echo htmlspecialchars($row['movie_id']); ?>">
                                <button type="submit" name="editmovie" value="editmovie">Edit Movie</button>
                            </form>

                            <form method="post" action="deletemovie.php">
                                <input type="hidden" name="movieid" value="<?php echo htmlspecialchars($row['movie_id']); ?>">
                                <button type="submit" name="deletemovie" value="deletemovie">Delete Movie</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
    <?php else: ?>
    <section class="movie-section" id="movie">
        <h2>NOW SHOWING</h2>
        <div class="movie-list">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <a href="#" class="movie" onclick="openPopup('<?php echo $row['trailer_link']; ?>', '<?php echo addslashes($row['title']); ?>', '<?php echo addslashes($row['description']); ?>', '<?php echo $row['movie_id']; ?>')">
                <img src="<?php echo htmlspecialchars($row['poster']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" />
                </a>
            <?php endwhile; ?>
        </div>
    </section>

    <div class="overlay" id="overlay" onclick="closePopup()"></div>
    <div class="popup" id="popup">
        <span class="close" onclick="closePopup()">&times;</span>
        <div id="trailer-container"></div>
        <h2 id="popup-title"></h2>
        <p id="popup-description"></p>
        <button id="buyTicketsButton">Buy Tickets</button>
    </div>

    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>

    <script>
        function openPopup(trailerLink, title, description, movieId) {
            document.getElementById('popup-title').innerText = title;
            document.getElementById('popup-description').innerText = description;
            document.getElementById('trailer-container').innerHTML = `<iframe src="${trailerLink}" frameborder="0" allowfullscreen></iframe>`;

            document.getElementById('buyTicketsButton').onclick = function() {
                window.location.href = 'movie.php?movie_id=' + movieId;
            };

            document.getElementById('overlay').style.display = 'block';
            document.getElementById('popup').style.display = 'block';
        }
        
        function closePopup() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('popup').style.display = 'none';
            document.getElementById('trailer-container').innerHTML = '';
        }

        
        document.getElementById('overlay').addEventListener('click', closePopup);
    </script>
    <?php endif; ?>
</body>
</html>