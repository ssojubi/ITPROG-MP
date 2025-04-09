<?php
    session_start();
    include("connection.php");
    
    if(isset($_POST['add'])) {
        $sql = "SELECT MAX(showtime_id) + 1 AS newshowtimeid FROM showtimes;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result1 = $stmt->get_result();
        $array = $result1->fetch_assoc();

        $showtimeid = $array['newshowtimeid'];
        $movieid = $_POST['movieid'];
        $cinemaid = $_POST['cinema'];
        $showdate = $_POST['date'];
        $showtime = $_POST['time'];
        $showprice = $_POST['price'];

        $insert = "INSERT INTO showtimes VALUES ('$showtimeid', '$movieid', '$cinemaid', '$showtime', '$showprice', '$showdate')";

        $result = $conn->query($insert);
        $_SESSION['movieid'] = $movieid;
        unset($_SESSION['error_message']);
        header("location:viewshowtimes.php");
    }
?>

