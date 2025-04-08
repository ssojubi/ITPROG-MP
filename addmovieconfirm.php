<?php
    session_start();
    include("connection.php");
    
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $rating = $_POST['rating'];
    $description = $_POST['description'];
    $trailerlink = $_POST['trailerlink'];

    if(isset($_POST['add'])) {
        $insert = "INSERT INTO movies VALUES (11, '$title', 'TBA', '$genre', '$rating', '$description', 'ballerina.jpg', '$trailerlink', 'comingsoon')";

        $result = $conn->query($insert);
        unset($_SESSION['error_message']);
        header("location:allmovies.php");
    }
?>