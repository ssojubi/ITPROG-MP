<?php
session_start();
include("connection.php");

if(isset($_SESSION['type']) && $_SESSION['type'] != 'admin')
    header("location:mainpage.php");

if(isset($_SESSION['error_message']))
    $error = $_SESSION['error_message'];

$movieid = $_POST['movieid'];
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
        .form-group input, select, option, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #666;
            border-radius: 5px;
            font-size: 14px;
            background-color: #444;
            color: white;
            resize: none;
        }
        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
        button {
            width: 150px;
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
    <form id="previous-form" action="viewshowtimes.php" method="post">
        <input type="hidden" id="movieid" name="movieid" value="<?php echo $movieid; ?>">
        <button type="submit" class="previous" name="previous" value="previous">Go Back</button>
    </form>
    <div class="container">
        <h2>Add Showtime</h2>
        <form id="showtimeadd-form" action="addshowtimeconfirm.php" method="POST">
            <input type="hidden" id="movieid" name="movieid" value="<?php echo $movieid; ?>">
            <div class="form-group">
                <label for="cinema">Cinema</label>
                <select id="cinema" name="cinema">
                    <option value="1">Premiere Club</option>
                    <option value="2">Directors Club</option>
                    <option value="3">IMAX</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <?php echo '<input type="date" id="date" name="date" required>'; ?>
            </div>
            <div class="form-group">
                <label for="time">Time</label>
                <?php echo '<input type="time" id="time" name="time" required>'; ?>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <?php echo '<input type="number" id="price" name="price" required>'; ?>
            </div>
            <div class="error" id="error"><?php if(isset($_SESSION['error_message'])){ echo $error; }?></div>
            <button type="submit" name="add" value="add">Submit</button>
        </form>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>