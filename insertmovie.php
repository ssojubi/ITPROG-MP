<?php
$conn = mysqli_connect("localhost", "root", "", port: 3377) or
die("Connection failed: " . mysqli_connect_error());
$use = mysqli_select_db ($conn, "dbmoviesystem");

$title = "The Monkey";
$duration = "1 hour 39 mins";
$genre = "Dark Comedy/Splatter Horror/Horror";
$rating = "R";
$description = "When twin brothers Bill and Hal find their fathers old monkey toy in the attic, a series of gruesome deaths start. The siblings decide to throw the toy away and move on with their lives, growing apart over the years.";
$poster = "stich-poster.jpg";

$insert = "INSERT INTO movies (title, duration, genre, rating, description, poster) 
           VALUES ('$title', '$duration', '$genre', '$rating', '$description', '$poster')";

if (mysqli_query($conn, $insert)) {
    echo "Movie added successfully!";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>