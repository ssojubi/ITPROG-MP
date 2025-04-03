<?php
$booking_reference = isset($_GET['ref']) ? $_GET['ref'] : 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Booking Successful - The Premiere Club</title>
    <link rel="stylesheet" href="payPageStyle.css" />
    <link rel="stylesheet" href="mainpage.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet" />
    <style>
        .success-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            text-align: center;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .success-message {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }

        .booking-details {
            margin: 30px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            text-align: left;
        }

        .home-btn {
            background-color: #ff3b3b;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .home-btn:hover {
            background-color: #e02e2e;
        }
    </style>
</head>

<body>
    <header>
        <div class="circle-logo">
            <img src="logo.png" alt="Logo" />
        </div>
        <div class="logo">
            <h1>The Premiere Club</h1>
        </div>
    </header>

    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1 class="success-message">Booking Successful!</h1>
        <p>Thank you for your purchase. Your booking has been confirmed.</p>

        <div class="booking-details">
            <p><strong>Booking Reference:</strong> <?php echo htmlspecialchars($booking_reference); ?></p>
            <p>A confirmation email has been sent to your email address.</p>
            <p>Please arrive 15 minutes before the movie starts and present this booking reference at the counter.</p>
        </div>

        <a href="mainpage.php">
            <button class="home-btn">Return to Home</button>
        </a>
    </div>

    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>

</html>