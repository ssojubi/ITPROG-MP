<?php
    session_start();
    include("connection.php");
    
    $movieid = $_POST['movieid'];
    $poster = $_POST['poster'];

    if(isset($_POST['delete'])) {
        $sql = "DELETE FROM movies
        WHERE movie_id = '$movieid'";

        $result = $conn->query($sql);
        unlink($poster);
        unset($_SESSION['error_message']);
        header("location:allmovies.php");
    }
?>