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
    <title>Sign Up - The Premiere Club</title>
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
    </section>
    
    <div class="container">
        <h2>Edit Account</h2>
        <form id="accountedit-form" action="editaccountconfirm.php" method="POST">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <?php echo '<input type="text" id="fullname" name="fullname" value="'. $name. '" required>'; ?>
            </div>
            <div class="form-group">
                <label for="birthdate">Birthdate</label>
                <?php echo '<input type="date" id="birthdate" name="birthdate" value="'. $birthdate. '" required>'; ?>
            </div>
            <div class="form-group">
                <label for="contact">Contact Number</label>
                <?php echo '<input type="tel" id="contact" name="contact" value="'. $contact. '" required>'; ?>
            </div>
            <div class="form-group">
                <label for="password">Enter Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="edit" value="edit">Submit</button>
        </form>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>