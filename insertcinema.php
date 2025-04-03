<?php
$conn = mysqli_connect("localhost", "root", "", port: 3377) or
die("Connection failed: " . mysqli_connect_error());
$use = mysqli_select_db ($conn, "dbmoviesystem");


$name = "IMAX";

$insert = "INSERT INTO cinemas (name) 
           VALUES ('$name')";

if (mysqli_query($conn, $insert)) {
    echo "Cinema added successfully!";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>