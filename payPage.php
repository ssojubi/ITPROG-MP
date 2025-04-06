<?php
session_start();
include("connection.php");

// Get parameters from URL
$movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 1;
$showtime_id = isset($_GET['showtime_id']) ? (int)$_GET['showtime_id'] : 1; // Default to 1 if not provided


// Check if user is logged in
if (!isset($_SESSION['email'])) {
    // Store current URL in session to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: loginpage.php");
    exit();
}

$user_email = $_SESSION['email'];
$user_name = $_SESSION['name'];

// Validate movie_id and showtime_id are integers
if (!is_numeric($movie_id) || !is_numeric($showtime_id)) {
    die("Invalid parameters provided.");
}

if (isset($_GET['seats'])) {
    $seats = explode(',', $_GET['seats']);
    // Validate seats format
    foreach ($seats as $seat) {
        if (!preg_match('/^[A-Z][0-9]{1,2}$/', trim($seat))) {
            die("Invalid seat format detected.");
        }
    }
} else {
    // Default seats for testing
    $seats = array("A1", "A2");
}

// Initialize variables
$movie_title = "";
$movie_date = date("m/d/Y");
$movie_time = "";
$movie_price = 0;
$cinema_name = "";
$cinema_id = 0;

// Get movie title from database
$movie_sql = "SELECT title FROM movies WHERE movie_id = ?";
$stmt = mysqli_prepare($conn, $movie_sql);
mysqli_stmt_bind_param($stmt, "i", $movie_id);
mysqli_stmt_execute($stmt);
$movie_result = mysqli_stmt_get_result($stmt);

if ($movie_result && mysqli_num_rows($movie_result) > 0) {
    $movie_row = mysqli_fetch_assoc($movie_result);
    $movie_title = $movie_row['title'];
} else {
    $movie_title = "Movie not found";
}


// Directly query the showtimes table
$time_sql = "SELECT s.time, s.price, s.cinema_id FROM showtimes s WHERE s.showtime_id = ?";
$stmt = mysqli_prepare($conn, $time_sql);
mysqli_stmt_bind_param($stmt, "i", $showtime_id);
mysqli_stmt_execute($stmt);
$time_result = mysqli_stmt_get_result($stmt);

if ($time_result && mysqli_num_rows($time_result) > 0) {
    $time_row = mysqli_fetch_assoc($time_result);
    $movie_time = $time_row['time'];
    $movie_price = $time_row['price'];
    $cinema_id = $time_row['cinema_id'];
} else {
    $movie_time = "No time found";
    $movie_price = 0;
}

// Separate query for cinema name
$cinema_sql = "SELECT c.name FROM cinemas c 
                JOIN showtimes s ON c.cinema_id = s.cinema_id 
                WHERE s.showtime_id = ?";
$stmt = mysqli_prepare($conn, $cinema_sql);
mysqli_stmt_bind_param($stmt, "i", $showtime_id);
mysqli_stmt_execute($stmt);
$cinema_result = mysqli_stmt_get_result($stmt);

if ($cinema_result && mysqli_num_rows($cinema_result) > 0) {
    $cinema_row = mysqli_fetch_assoc($cinema_result);
    $cinema_name = $cinema_row['name'];
} else {
    $cinema_name = "Cinema not found";
}

// Calculate total
$num_seats = count($seats);
$total_price = $movie_price * $num_seats;

// Format seats array into comma-separated string
$seats_str = implode(", ", $seats);

// Format time for display (if it exists)
if (!empty($movie_time) && $movie_time != "No time found") {
    $movie_time_display = date("h:i A", strtotime($movie_time));
} else {
    $movie_time_display = "Time not available";
}

// Initialize errors array
$errors = [];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate billing address
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $apartment = trim($_POST['apartment'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $region = $_POST['region'] ?? '';
    
    // Credit card details
    $card_number = trim($_POST['card_number'] ?? '');
    $card_holder = trim($_POST['card_holder'] ?? '');
    $expiry_date = trim($_POST['expiry_date'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');
    
    // Billing Address Validations
    if (empty($name)) {
        $errors['name'] = "Name is required";
    } elseif (strlen($name) < 2 || strlen($name) > 50) {
        $errors['name'] = "Name must be between 2 and 50 characters";
    }
    
    if (empty($address)) {
        $errors['address'] = "Address is required";
    } elseif (strlen($address) < 5 || strlen($address) > 100) {
        $errors['address'] = "Address must be between 5 and 100 characters";
    }
    
    if (!empty($apartment) && strlen($apartment) > 50) {
        $errors['apartment'] = "Apartment/suite info must be less than 50 characters";
    }
    
    if (empty($postal_code)) {
        $errors['postal_code'] = "Postal code is required";
    } elseif (!preg_match('/^\d{4}$/', $postal_code)) { // Simplified for Philippine postal codes
        $errors['postal_code'] = "Please enter a valid 4-digit Philippine postal code";
    }
    
    if (empty($city)) {
        $errors['city'] = "City is required";
    } elseif (strlen($city) < 2 || strlen($city) > 50) {
        $errors['city'] = "City must be between 2 and 50 characters";
    }
    
    if (empty($region)) {
        $errors['region'] = "Please select a region";
    }
    
    // Credit Card Validations
    if (empty($card_number)) {
        $errors['card_number'] = "Card number is required";
    } else {
        // Remove spaces and dashes
        $card_number = preg_replace('/\s+|-/', '', $card_number);
        
        // Apply Luhn algorithm for credit card validation
        if (!validateCreditCard($card_number)) {
            $errors['card_number'] = "Please enter a valid credit card number";
        }
    }
    
    if (empty($card_holder)) {
        $errors['card_holder'] = "Card holder name is required";
    } elseif (strlen($card_holder) < 2 || strlen($card_holder) > 50) {
        $errors['card_holder'] = "Card holder name must be between 2 and 50 characters";
    }
    
    if (empty($expiry_date)) {
        $errors['expiry_date'] = "Expiry date is required";
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry_date)) {
        $errors['expiry_date'] = "Expiry date must be in MM/YY format";
    } else {
        // Check if card is expired
        list($exp_month, $exp_year) = explode('/', $expiry_date);
        $exp_year = '20' . $exp_year; // Convert to 4-digit year
        
        $current_year = date('Y');
        $current_month = date('m');
        
        if ($exp_year < $current_year || ($exp_year == $current_year && $exp_month < $current_month)) {
            $errors['expiry_date'] = "Card has expired";
        }
    }
    
    if (empty($cvv)) {
        $errors['cvv'] = "CVV is required";
    } elseif (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
        $errors['cvv'] = "CVV must be 3 or 4 digits";
    }
    
    // If no errors, proceed with payment processing
    if (empty($errors)) {
        // Generate a unique booking reference
        $booking_reference = "TPC" . date('YmdHis') . rand(10, 99);
        
        // Store necessary information in session for the success page
        $_SESSION['payment_info'] = [
            'movie_id' => $movie_id,
            'showtime_id' => $showtime_id,
            'seats' => $seats,
            'seats_str' => $seats_str,
            'total_price' => $total_price,
            'name' => $name,
            'booking_reference' => $booking_reference,
            'cinema_id' => $cinema_id,
            'movie_title' => $movie_title,
            'cinema_name' => $cinema_name,
            'movie_time' => $movie_time_display
        ];
        
        // Redirect to success page
        header("Location: payPageSuccess.php");
        exit();
    }
}

// Credit card validation function using Luhn algorithm
function validateCreditCard($number) {
    // Strip any non-digits
    $number = preg_replace('/\D/', '', $number);
    
    // Check if the number contains only digits
    if (!ctype_digit($number)) {
        return false;
    }
    
    // Check length (most cards are between 13-19 digits)
    $length = strlen($number);
    if ($length < 13 || $length > 19) {
        return false;
    }
    
    // Luhn algorithm
    $sum = 0;
    $double = false;
    
    // Loop through each digit from right to left
    for ($i = $length - 1; $i >= 0; $i--) {
        $digit = (int)$number[$i];
        
        if ($double) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        
        $sum += $digit;
        $double = !$double;
    }
    
    // If sum is divisible by 10, the card number is valid
    return ($sum % 10 == 0);
}

// Helper function to display error message
function showError($field) {
    global $errors;
    if (isset($errors[$field])) {
        return '<span class="error">' . htmlspecialchars($errors[$field]) . '</span>';
    }
    return '';
}

// Helper function to retain input value after form submission
function getValue($field) {
    if (isset($_POST[$field])) {
        return htmlspecialchars($_POST[$field]);
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Page - <?php echo htmlspecialchars($movie_title); ?></title>
    <link rel="stylesheet" href="payPageStyle.css" />
    <link rel="stylesheet" href="mainpage.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet" />
    <style>
        .error {
            color: #e74c3c;
            font-size: 0.85em;
            display: block;
            margin-top: 3px;
            margin-bottom: 8px;
        }
        .error-field {
            border: 1px solid #e74c3c !important;
        }
        .user-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }
        .user-info p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <header>
        <div class="circle-logo"></div>
        <div class="logo">
            <h1>The Premiere Club</h1>
        </div>
    </header>
    <div class="payment-cont">
        <div class="billing-cont">
            <div class="billing-text">
                <h2>Billing Address</h2>
            </div>
            <!-- Display logged in user information -->
            <div class="user-info">
                <p><strong>Logged in as:</strong> <?php echo htmlspecialchars($user_name); ?> (<?php echo htmlspecialchars($user_email); ?>)</p>
            </div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?' . http_build_query($_GET); ?>">
                <input type="hidden" name="movie_id" value="<?php echo htmlspecialchars($movie_id); ?>" />
                <input type="hidden" name="showtime_id" value="<?php echo htmlspecialchars($showtime_id); ?>" />
                <input type="hidden" name="seats" value="<?php echo htmlspecialchars(implode(',', $seats)); ?>" />
                <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($total_price); ?>" />

                <div class="border-cont">
                    <input type="text" name="name" placeholder="Name" value="<?php echo getValue('name') ?: $user_name; ?>" 
                           class="<?php echo isset($errors['name']) ? 'error-field' : ''; ?>" />
                    <?php echo showError('name'); ?>
                    
                    <input type="text" name="address" placeholder="Address" value="<?php echo getValue('address'); ?>"
                           class="<?php echo isset($errors['address']) ? 'error-field' : ''; ?>" />
                    <?php echo showError('address'); ?>
                    
                    <input type="text" name="apartment" placeholder="Apartment, suite, etc. (optional)" value="<?php echo getValue('apartment'); ?>"
                           class="<?php echo isset($errors['apartment']) ? 'error-field' : ''; ?>" />
                    <?php echo showError('apartment'); ?>
                    
                    <div class="input-group">
                        <div style="flex: 1;">
                            <input type="text" name="postal_code" placeholder="Postal code" value="<?php echo getValue('postal_code'); ?>"
                                  class="<?php echo isset($errors['postal_code']) ? 'error-field' : ''; ?>" />
                            <?php echo showError('postal_code'); ?>
                        </div>
                        <div style="flex: 2;">
                            <input type="text" name="city" placeholder="City" value="<?php echo getValue('city'); ?>"
                                  class="<?php echo isset($errors['city']) ? 'error-field' : ''; ?>" />
                            <?php echo showError('city'); ?>
                        </div>
                    </div>
                    
                    <select name="region" class="<?php echo isset($errors['region']) ? 'error-field' : ''; ?>">
                        <option value="">Region</option>
                        <option value="NCR" <?php echo (getValue('region') == 'NCR') ? 'selected' : ''; ?>>NCR</option>
                        <option value="Region I" <?php echo (getValue('region') == 'Region I') ? 'selected' : ''; ?>>Region I</option>
                        <option value="Region II" <?php echo (getValue('region') == 'Region II') ? 'selected' : ''; ?>>Region II</option>
                        <option value="Region III" <?php echo (getValue('region') == 'Region III') ? 'selected' : ''; ?>>Region III</option>
                        <option value="Region IV-A" <?php echo (getValue('region') == 'Region IV-A') ? 'selected' : ''; ?>>Region IV-A</option>
                        <option value="Region IV-B" <?php echo (getValue('region') == 'Region IV-B') ? 'selected' : ''; ?>>Region IV-B</option>
                        <option value="Region V" <?php echo (getValue('region') == 'Region V') ? 'selected' : ''; ?>>Region V</option>
                    </select>
                    <?php echo showError('region'); ?>
                </div>
                <div class="billing-text">
                    <h2>Payment Method</h2>
                </div>
                <div class="border-cont">
                    <label>Credit Card Details</label>
                    <input type="text" name="card_number" placeholder="Card Number" value="<?php echo getValue('card_number'); ?>"
                           class="<?php echo isset($errors['card_number']) ? 'error-field' : ''; ?>" />
                    <?php echo showError('card_number'); ?>
                    
                    <input type="text" name="card_holder" placeholder="Card Holder Name" value="<?php echo getValue('card_holder') ?: $user_name; ?>"
                           class="<?php echo isset($errors['card_holder']) ? 'error-field' : ''; ?>" />
                    <?php echo showError('card_holder'); ?>
                    
                    <div class="input-group">
                        <div style="flex: 1;">
                            <input type="text" name="expiry_date" placeholder="Expiry Date (MM/YY)" value="<?php echo getValue('expiry_date'); ?>"
                                  class="<?php echo isset($errors['expiry_date']) ? 'error-field' : ''; ?>" />
                            <?php echo showError('expiry_date'); ?>
                        </div>
                        <div style="flex: 1;">
                            <input type="text" name="cvv" placeholder="CVV" value="<?php echo getValue('cvv'); ?>"
                                  class="<?php echo isset($errors['cvv']) ? 'error-field' : ''; ?>" />
                            <?php echo showError('cvv'); ?>
                        </div>
                    </div>
                </div>
        </div>
        <!-- RIGHT SIDE -->
        <div class="order-cont">
            <div class="billing-text">
                <h2>Order Summary</h2>
            </div>
            <div class="border-cont summary">
                <p><strong>Movie:</strong> <?php echo htmlspecialchars($movie_title); ?></p>
                <p><strong>Cinema:</strong> <?php echo htmlspecialchars($cinema_name); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($movie_date); ?></p>
                <p><strong>Time:</strong> <?php echo htmlspecialchars($movie_time_display); ?></p>
                <p><strong>Seats:</strong> <?php echo htmlspecialchars($seats_str); ?></p>
                <p><strong>Price per Ticket:</strong> ₱<?php echo number_format($movie_price, 2); ?></p>
                <hr />
                <p><strong>Total:</strong> ₱<?php echo number_format($total_price, 2); ?></p>
            </div>
            <button type="submit" class="pay-btn">Pay Now</button>
            </form>
        </div>
    </div>
</body>

</html>