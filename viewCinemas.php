<?php
session_start();
include("connection.php");

if (!isset($_SESSION['email'])) {
    // Store current URL in session to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: loginpage.php");
    exit();
}

// Fetch all cinemas from the database
$sql = "SELECT cinema_id, name, description, image FROM cinemas";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premiere Club - Cinemas</title>
    <link rel="stylesheet" href="mainpage.css">
    <style>
        .cinema-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
            padding: 60px 0;
            background-color: #252524;
        }
        .cinema-container h1{
            font-size: 2.8rem;
            color: white;
        }
        .cinema-card {
            display: flex;
            align-items: center;
            gap: 20px;
            width: 80%;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 20px 35px 35px rgba(0, 0, 0, 0.5);
        }
        .cinema-card img {
            width: 400px;
            height: 240px;
            border-radius: 10px;
            object-fit: cover;
        }
        .cinema-info {
            flex: 1;
        }
        .cinema-info h2 {
            margin: 0;
            color: white;
        }
        .cinema-info p {
            margin: 10px 0;
            color: white;
        }
        .cinema-info button {
            padding: 10px 15px;
            background-color: #ff4500;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .cinema-info button:hover {
            background-color: #d03c00;
        }
    </style>
</head>
<body>
    <?php include("header.php");?>

    <section class="cinema-container">
        <h1>CINEMAS</h1>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <div class="cinema-card">
                <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <div class="cinema-info">
                    <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <button onclick="window.location.href='showtimes.php?cinema_id=<?php echo urlencode($row['cinema_id']); ?>'">See what's playing</button>
                </div>
            </div>
        <?php endwhile; ?>
    </section>

    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>

    <?php $conn->close(); ?>
</body>
</html>