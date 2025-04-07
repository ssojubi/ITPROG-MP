<?php
session_start();
include("connection.php");

$sql = "SELECT movie_id, title, poster FROM movies";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premiere Club - Main</title>
    <link rel="stylesheet" href="mainpage.css">
</head>
<body>
    <?php include("header.php");?>

    <section class="hero">
        <div class="hero-text">
            <h2>Welcome to The Premiere Club</h2>
            <p>At The Premiere Club, every movie is a first-class experience.</p>
        </div>
    </section>

    <!-- <section class="movie-section" id="movie">
        <h2>NOW SHOWING</h2>
        
        <div class="movie-list">
            <a href="movie.php?movie_id=1" class="movie">
                <img src="cap-poster.jpg" alt="Captain America" />
            </a>
            
            <a href="movie.php?movie_id=2" class="movie">
                <img src="ballerina.jpg" alt="Ballerina" />
            </a>
            
            <a href="movie.php?movie_id=3" class="movie">
                <img src="super-poster.jpeg" alt="Super Movie" />
            </a>
            <a href="movie.php?movie_id=4" class="movie">
                <img src="red-poster.jpg" alt="Captain America" />
            </a>
            
            <a href="movie.php?movie_id=5" class="movie">
                <img src="thunder-poster.jpeg" alt="Ballerina" />
            </a>
            
            <a href="movie.php?movie_id=6" class="movie">
                <img src="httyd-poster.jpg" alt="Super Movie" />
            </a>
            <a href="movie.php?movie_id=7" class="movie">
                <img src="fast-poster.jpg" alt="Captain America" />
            </a>
            
            <a href="movie.php?movie_id=8" class="movie">
                <img src="hit-poster.jpg" alt="Ballerina" />
            </a>
            
            <a href="movie.php?movie_id=9" class="movie">
                <img src="jw-poster.jpg" alt="Super Movie" />
            </a>
            
            <a href="movie.php?movie_id=10" class="movie">
                <img src="stich-poster.jpg" alt="Super Movie" />
            </a>
        </div>
        <div class="view-all-movies">
            <button onclick="window.location.href='movies.html'">View All Movies</button>
        </div>
    </section> -->

    <section class="movie-section" id="movie">
        <h2>NOW SHOWING</h2>
        
        <div class="movie-list">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <a href="movie.php?movie_id=<?php echo $row['movie_id']; ?>" class="movie">
                    <img src="<?php echo htmlspecialchars($row['poster']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" />
                </a>
            <?php endwhile; ?>
        </div>
        <div class="view-all-movies">
            <button onclick="window.location.href='allMovies.php'">View All Movies</button>
        </div>
    </section>
    
    <section class="movie-description">
        <div class="description-container">
            <div class="slideshow-container">
                <div class="slide fade">
                    <img class="img-con" src="director.jpg" alt="Director's Club Cinema">
                </div>
                <div class="slide fade">
                    <img class="img-con" src="imax.jpg" alt="IMAX Cinema">
                </div>
                <div class="slide fade">
                    <img class="img-con" src="tempur.jpg" alt="Premiere Club Cinema">
                </div>
            </div>
            <div class="text-content">
                <h2 id="slide-header">Manila Branch</h2>
                <p id="slide-description">Experience world-class cinema in the heart of Manila.</p>
            </div>
        </div>
    </section>


    <section class="coming-movie">
        <h2>COMING SOON</h2>

        <div class="carousel">
            <div class="carousel-cont">
                <img src="cs-28yrslater.jpg" alt="">
                <img src="cs-wickedforgood.jpg" alt="">
                <img src="cs-theritual.jpg" alt="">
                <img src="cs-conjuring-lastrites.jpg" alt="">
                <img src="cs-toystory5.png" alt="">
                <img src="cs-ff4.jpg" alt="">
                <img src="cs-avenger-secretwars.jpg" alt="">
                <img src="cs-finaldestination.jpg" alt="">
                <img src="cs-shrek5.jpg" alt="">
            </div>

        </div>



    </section>

    <script>
        let slideIndex = 0;
        const headers = ["Director's Club Cinema", "IMAX Cinema", "Premiere Club Cinema"];
        const descriptions = [
            "Experience world-class cinema. Dive deeper into the world of cinematic visuals.",
            "Enjoy cutting-edge technology, surround sound environment and comfort at IMAX Cinemas.",
            "Premium movie experience with luxurious seating and sound. Made for a much more private and intimate cinema viewing experience."
        ];
        function showSlides() {
            let slides = document.getElementsByClassName("slide");
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slideIndex++;
            if (slideIndex > slides.length) { slideIndex = 1; }
            slides[slideIndex - 1].style.display = "block";
            document.getElementById("slide-header").innerText = headers[slideIndex - 1];
            document.getElementById("slide-description").innerText = descriptions[slideIndex - 1];
            setTimeout(showSlides, 3000);
        }
        showSlides();
    </script>

    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>
