<?php
    session_start();
    include("connection.php");

    if(isset($_POST['edit'])) {
        $showtimeid = $_POST['showtimeid'];
        $movieid = $_POST['movieid'];
        $cinemaid = $_POST['cinema'];
        $showdate = $_POST['date'];
        $showtime = $_POST['time'];
        $showprice = $_POST['price'];

        $sql = "UPDATE `showtimes` 
        SET `cinema_id` = '$cinemaid', 
        `showtime_date` = '$showdate', 
        `time` = '$showtime',
        `price` = '$showprice'
        WHERE `showtime_id` = '$showtimeid'";
        
        $result = $conn->query($sql);

        $sql = "UPDATE `booking_seats` 
        SET `total_price` = '$showprice'
        WHERE `showtime_id` = '$showtimeid'";

        $result = $conn->query($sql);

        $_SESSION['movieid'] = $movieid;
        unset($_SESSION['error_message']);
        header("location:viewshowtimes.php");
    }
?>