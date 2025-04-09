<?php
    session_start();
    include("connection.php");

    if(isset($_POST['edit'])) {
        $movieid = $_POST['movieid'];
        $title = $_POST['title'];
        $duration = $_POST['duration'];
        $genre = $_POST['genre'];
        $rating = $_POST['rating'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $trailerlink = $_POST['trailerlink'];

        $fileOldName = $_POST['oldposter'];

        $file = $_FILES['poster'];

        $fileName = $_FILES['poster']['name'];
        $fileTmpName = $_FILES['poster']['tmp_name'];
        $fileSize = $_FILES['poster']['size'];
        $fileError = $_FILES['poster']['error'];
        $fileType = $_FILES['poster']['type'];

        $fileExt = explode('.', $fileName);
        $fileActualExt = strtolower(end($fileExt));

        $allowed = array('jpg', 'jpeg', 'png');

        $fileRealName = reset($fileExt). ".". $fileActualExt;
        
        if($fileSize != 0) {
            if(in_array($fileActualExt, $allowed)) {
                if($fileError === 0){
                    if($fileSize < 500000) {
                    move_uploaded_file($fileTmpName, $fileRealName);
                    }
                    else {
                        $_SESSION['error_message'] = "Image size is too large.";
                        header("location:addmovie.php");
                    }
                }
                else {
                    $_SESSION['error_message'] = "Upload error.";
                    header("location:addmovie.php");
                }
            }
            else {
                $_SESSION['error_message'] = "Invalid image type.";
                header("location:addmovie.php");
            }

            $sql = "UPDATE `movies` 
            SET `title` = '$title', 
            `duration` = '$duration', 
            `genre` = '$genre',
            `rating` = '$rating',
            `description` = '$description',
            `show_status` = '$status',
            `trailer_link` = '$trailerlink',
            `poster` = '$fileRealName'
            WHERE `movie_id` = '$movieid'";

            unlink($fileOldName);
        }
        else {
            $sql = "UPDATE `movies` 
            SET `title` = '$title', 
            `duration` = '$duration', 
            `genre` = '$genre',
            `rating` = '$rating',
            `description` = '$description',
            `show_status` = '$status',
            `trailer_link` = '$trailerlink'
            WHERE `movie_id` = '$movieid'";
        }
        
        $result = $conn->query($sql);
        unset($_SESSION['error_message']);
        header("location:allmovies.php");
    }
?>