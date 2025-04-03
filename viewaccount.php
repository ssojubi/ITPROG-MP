<?php
session_start();
include("connection.php");

$email = $_SESSION['email'];

$sql = "SELECT account_name, birth_date, contact_number FROM accounts WHERE email_address = '$email'";
$result = $conn->query($sql);
list($name, $birthdate, $contact) = mysqli_fetch_row($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - The Premiere Club</title>
    <link rel="stylesheet" href="signuppage.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #252524;
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        label {
            text-align: left;
            display: block;
            font-weight: bold;
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
        .accountinfo {
            display: grid;
            grid-template-areas: "details bookings";
            grid-template-columns: 1fr 3fr;
        }
        .accountinfo > div.accountdetails {
            grid-area: details;
        }
        .accountinfo > div.accountbookings {
            grid-area: bookings;
        }
        .accountinfo p {
            text-align: left;
            margin-bottom: 15px;
            display: block;
        }
        .accountdetails, .accountbookings{
            background: #333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(255, 255, 255, 0.1);
            text-align: left;
            margin: 20px;
        }
        h2 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>
    
    <section class="hero">
    </section>
    
    <div class="accountinfo">
        <div class="accountdetails">
            <h2>Account Details</h2>
            <label for="name">Full Name</label>
            <p><?php echo $name; ?></p>
            <label for="email">Email Address</label>
            <p><?php echo $email; ?></p>
            <label for="contact">Birth Date</label>
            <p><?php echo $birthdate; ?></p>
            <label for="birthdate">Contact Number</label>
            <p><?php echo $contact; ?></p>

            <form id="edit-details" action="editaccount.php">
                <button type="submit" name="editdetails" value="editdetails">Edit Details</button>
            </form>
            <br>
            <form id="change-password" action="changepassword.php">
                <button type="submit" name="changepass" value="changepass">Change Password</button>
            </form>
        </div>
        <div class="accountbookings">
            <h2>Bookings</h2>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
        <a href="#">Sign Up</a>
    </footer>
</body>
</html>
