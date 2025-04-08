<?php
session_start();
include("connection.php");

if(isset($_SESSION['type']) && $_SESSION['type'] != 'admin')
    header("location:mainpage.php");

if(isset($_SESSION['error_message']))
    $error = $_SESSION['error_message'];

$movieid = $_POST['movieid'];

$sql = "SELECT * FROM movies WHERE movie_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $movieid);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
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
    
    <div class="container">
        <h2>Edit Movie</h2>
        <form id="movieedit-form" action="editmovieconfirm.php" method="POST">
            <input type="hidden" id="movieid" name="movieid" value="<?php echo $row['movie_id']; ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <?php echo '<input type="text" id="title" name="title" value="'. $row['title']. '" required>'; ?>
            </div>
            <div class="form-group">
                <label for="duration">Duration</label>
                <?php echo '<input type="text" id="duration" name="duration" value="'. $row['duration']. '" required>'; ?>
            </div>
            <div class="form-group">
                <label for="genre">Genre</label>
                <?php echo '<input type="text" id="genre" name="genre" value="'. $row['genre']. '" required>'; ?>
            </div>
            <div class="form-group">
                <label for="rating">Rating</label>
                <?php echo '<input type="text" id="rating" name="rating" value="'. $row['rating']. '" required>'; ?>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <?php echo '<textarea id="description" name="description" cols="40" rows="10" required>'. $row['description']. '</textarea>'; ?>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="finished">Finished</option>
                    <option value="showing">Showing</option>
                    <option value="comingsoon">Coming Soon</option>
                </select>
            </div>
            <div class="form-group">
                <label for="trailer">Trailer Link</label>
                <?php echo '<input type="text" id="trailerlink" name="trailerlink" value="'. $row['trailer_link']. '" required>'; ?>
            </div>
            <div class="error" id="error"><?php if(isset($_SESSION['error_message'])){ echo $error; }?></div>
            <button type="submit" name="edit" value="edit">Submit</button>
        </form>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>