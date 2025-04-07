<?php
session_start();
include("connection.php");

$sql = "SELECT movie_id, title, poster, description, trailer_link FROM movies";
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
    </style>
</head>
<body>
    <?php include("header.php");?>

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
</body>
</html>