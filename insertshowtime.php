<?php
$conn = mysqli_connect("localhost", "root", "", "dbmoviesystem", port: 3377) or
die("Connection failed: " . mysqli_connect_error());



$movie_id = 4; 
$cinema_id = 3; 
$time = "2025-03-10 3:00"; 
$price = 500.00;


$insert = "INSERT INTO showtimes (movie_id, cinema_id, time, price) 
           VALUES ('$movie_id', '$cinema_id', '$time', '$price')";

if (mysqli_query($conn, $insert)) {
    echo "Showtime added successfully!";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>