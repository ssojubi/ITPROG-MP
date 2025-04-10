<?php
    session_start();
    include("connection.php");
    
    $showtimeid = $_POST['showtimeid'];
    $movieid = $_POST['movieid'];
    $quantitybooked = $_POST['quantitybooked'];

    if(isset($_POST['delete'])) {
        $sql = "DELETE FROM showtimes
        WHERE showtime_id = '$showtimeid'";

        $_SESSION['movieid'] = $movieid;

        if($quantitybooked > 0) {
            $_SESSION['error_message'] = "Can't delete showtimes with bookings.";
            header("location:viewshowtimes.php");
        }
        else {
            $result = $conn->query($sql);
            unset($_SESSION['error_message']);
            header("location:viewshowtimes.php");
        }
    }
?>