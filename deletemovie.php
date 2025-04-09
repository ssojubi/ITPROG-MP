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
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #e68900;
        }
        .movie-card {
            background: #444;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            margin-left: 25%;
            margin-right: 25%;
            border-left: 4px solid #ff4500;
            text-align: left;
            display:grid;
            grid-template-areas:
            "title title"
            "img content";
        }
        .movie-card h3 {
            margin-top: 0;
            color: #ff4500;
            grid-area: title;
        }
        .movie-detail {
            display:flex;
            margin-top: 8px;
            margin-bottom: 8px;
            margin-left:10px;
            color: white;
        }
        .info {
            grid-area: content;
        }
        .movie-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        .movie-value {
            flex-grow: 1;
        }
        .movie-img {
            margin-top: 8px;
            margin-bottom: 8px;
            color: white;
            border: none;
            border-radius: 10px;
            transition: all ease-in-out 0.5s;
            background-size: cover;
            width: 150px;
            height: 300px;
            object-fit: cover;
            grid-area: img;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>
        <div class="movie-card">
            <h3 class="movie-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    
            <span class="movie-value"><img class="movie-img" src="<?php echo htmlspecialchars($row['poster']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" /></span>

            <div class="info">
                <div class="movie-detail">
                    <span class="movie-label">Duration:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($row['duration']); ?></span>
                </div>
                        
                <div class="movie-detail">
                    <span class="movie-label">Genre:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($row['genre']); ?></span>
                </div>
                        
                <div class="movie-detail">
                    <span class="movie-label">Rating:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($row['rating']); ?></span>
                </div>
                        
                <div class="movie-detail">
                    <span class="movie-label">Description:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($row['description']); ?></span>
                </div>      
                        
                <div class="movie-detail">
                    <span class="movie-label">Status:</span>
                    <span class="movie-value">
                        <?php if ($row['show_status'] == 'finished'): ?>
                            <span class="status-completed">Finished</span>
                        <?php elseif ($row['show_status'] == 'showing'): ?>
                            <span class="status-pending">Now Showing</span>
                        <?php else: ?>
                            <span class="status-failed">Coming Soon</span>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="movie-detail">
                    <span class="movie-label">Trailer Link:</span>
                    <span class="movie-value"><?php echo htmlspecialchars($row['trailer_link']); ?></span>
                </div>
            </div>
        </div>
    <div class="container">
        <h3> Are you sure you want to delete this movie? </h3>

        <form id="moviedelete-form" action="deletemovieconfirm.php" method="POST">
            <input type="hidden" name="movieid" id="movieid" value="<?php echo $movieid; ?>">
            <input type="hidden" name="poster" id="poster" value="<?php echo $row['poster']; ?>">
            <button type="submit" name="delete" value="delete">Confirm</button>
        </form>

        <form id="previous-form" action="allmovies.php">
            <button type="submit" name="previous" value="previous">Cancel</button>
        </form>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>