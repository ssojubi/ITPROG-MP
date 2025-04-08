<?php
session_start();
include("connection.php");

if(isset($_SESSION['type']) && $_SESSION['type'] == 'admin') {
    $sql = "SELECT * FROM movies";
}
else {
    $sql = "SELECT movie_id, title, poster, description, trailer_link FROM movies WHERE show_status = 'showing' OR 'comingsoon'";
}

$result = $conn->query($sql);
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

        .movie-card {
            background: #444;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #ff4500;
            text-align: left;
        }
        .movie-card h3 {
            margin-top: 0;
            color: #ff4500;
        }
        .movie-detail {
            display: flex;
            margin-bottom: 8px;
        }
        .movie-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        .movie-value {
            flex-grow: 1;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>

    <?php 
        if(isset($_SESSION['type']) && $_SESSION['type'] == 'admin') {
    ?>
    <section class="movie-section" id="movie">
        <h2>ALL MOVIES</h2>
        <div class="movie-list">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <div class="movie-card">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    
                    <div class="movie-detail">
                        <span class="movie-value">₱<?php echo htmlspecialchars($row['poster']); ?></span>
                    </div>

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
                </div>

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
    <?php
        }
        else {
    ?>
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
    <?php 
        }
    ?>
</body>
</html>