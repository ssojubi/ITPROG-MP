<?php
    session_start();
    include("connection.php");
    
    $movieid = $_POST['movieid'];
    $title = $_POST['title'];
    $duration = $_POST['duration'];
    $genre = $_POST['genre'];
    $rating = $_POST['rating'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $trailerlink = $_POST['trailerlink'];

    if(isset($_POST['edit'])) {
        $sql = "UPDATE `movies` 
        SET `title` = '$title', 
        `duration` = '$duration', 
        `genre` = '$genre',
        `rating` = '$rating',
        `description` = '$description',
        `show_status` = '$status',
        `trailer_link` = '$trailerlink'
        WHERE `movie_id` = '$movieid'";

        $result = $conn->query($sql);
        unset($_SESSION['error_message']);
        header("location:allmovies.php");
    }
?>