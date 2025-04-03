<?php
session_start();
include("connection.php");

$fullname = $_POST['fullname'];
$birthdate = $_POST['birthdate'];
$contact = $_POST['contact'];
$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM accounts";
$result = $conn->query($sql);
$sameemail = false;
$result2;
$affected = 0;

$insert = "INSERT INTO accounts VALUES ('$email', '$fullname', '$birthdate', '$contact', '$password')";

while($row = mysqli_fetch_array($result)) {
    if($row['email_address'] == $email)
        $sameemail = true;
}

if(!$sameemail) {
    $result2 = mysqli_query($conn, $insert);
    $affected = mysqli_affected_rows($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Confirmed - The Premiere Club</title>
    <link rel="stylesheet" href="signuppage.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #252524;
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            background: #333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(255, 255, 255, 0.1);
            width: 350px;
            text-align: center;
            margin: 80px auto;
        }
        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #666;
            border-radius: 5px;
            font-size: 14px;
            background-color: #444;
            color: white;
        }
        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #ff4500;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #e68900;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>
    
    <section class="hero">
        <div class="hero-text">
            <h2>Join The Premiere Club</h2>
            <p>Become a member and experience first-class cinema.</p>
        </div>
    </section>
    
    <div class="container">
        <?php 
            if ($affected > 0) {
                echo "<h2>Sign Up Confirmed</h2>";
                echo "<h3>Click here to <a href='loginpage.php'>Log In</a></h3>";
            } else {
                echo "<h2>Sign Up Failed</h2>";
                echo "<h3>This email already has an account. <a href='loginpage.php'>Log In</a> Instead.</h3>";
            } 
        ?>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
        <a href="#">Login</a>
    </footer>
</body>
</html>