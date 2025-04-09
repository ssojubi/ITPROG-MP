<?php
    session_start();
    include("connection.php");
    
    if(isset($_POST['add'])) {
        $title = $_POST['title'];
        $genre = $_POST['genre'];
        $rating = $_POST['rating'];
        $description = $_POST['description'];
        $trailerlink = $_POST['trailerlink'];

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

        $insert = "INSERT INTO movies VALUES (11, '$title', 'TBA', '$genre', '$rating', '$description', '$fileRealName', '$trailerlink', 'comingsoon')";

        $result = $conn->query($insert);
        unset($_SESSION['error_message']);
        header("location:allmovies.php");
    }
?>