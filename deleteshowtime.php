<?php
    session_start();
    include("connection.php");
    
    $showtimeid = $_POST['showtimeid'];
    $movieid = $_POST['movieid'];

    if(isset($_POST['delete'])) {
        $sql = "DELETE FROM showtimes
        WHERE showtime_id = '$showtimeid'";

        $result = $conn->query($sql);
        $_SESSION['movieid'] = $movieid;
        unset($_SESSION['error_message']);
        header("location:viewshowtimes.php");
    }
?>