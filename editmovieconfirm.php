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
    $fileRealName = $fileOldName; // Default value in case no new poster is uploaded

    // Check if a new poster is uploaded
    if($file['size'] != 0) {
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];

        $fileExt = explode('.', $fileName);
        $fileActualExt = strtolower(end($fileExt));

        $allowed = array('jpg', 'jpeg', 'png');

        // Generate new file name
        $fileRealName = reset($fileExt) . "." . $fileActualExt;

        if (in_array($fileActualExt, $allowed)) {
            if ($fileError === 0) {
                if ($fileSize < 500000) {
                    move_uploaded_file($fileTmpName, $fileRealName);
                    // Delete old poster
                    unlink($fileOldName);
                } else {
                    $_SESSION['error_message'] = "Image size is too large.";
                    header("location:editmovie.php?movieid=$movieid");
                    exit();
                }
            } else {
                $_SESSION['error_message'] = "Upload error.";
                header("location:editmovie.php?movieid=$movieid");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid image type.";
            header("location:editmovie.php?movieid=$movieid");
            exit();
        }
    }

    // Prepare SQL query to update movie information
    if ($file['size'] != 0) {
        // If poster is updated, include the poster in the query
        $sql = "UPDATE movies 
                SET title = ?, duration = ?, genre = ?, rating = ?, description = ?, show_status = ?, trailer_link = ?, poster = ? 
                WHERE movie_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssi", $title, $duration, $genre, $rating, $description, $status, $trailerlink, $fileRealName, $movieid);
    } else {
        // If poster is not updated, exclude poster from the query
        $sql = "UPDATE movies 
                SET title = ?, duration = ?, genre = ?, rating = ?, description = ?, show_status = ?, trailer_link = ? 
                WHERE movie_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $title, $duration, $genre, $rating, $description, $status, $trailerlink, $movieid);
    }

    // Execute the query
    if ($stmt->execute()) {
        unset($_SESSION['error_message']);
        header("location:allmovies.php");
    } else {
        $_SESSION['error_message'] = "Failed to update movie.";
        header("location:editmovie.php?movieid=$movieid");
    }
}
?>