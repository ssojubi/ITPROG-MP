<?php
session_start();
include("connection.php");

// Redirect if not logged in or not admin
if(!isset($_SESSION['email']) || !isset($_SESSION['type']) || $_SESSION['type'] != 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if showtime ID is provided
if(!isset($_POST['showtime_id']) || empty($_POST['showtime_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Showtime ID is required']);
    exit();
}

$showtime_id = $_POST['showtime_id'];
$booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : 0;

// Get all booked seats for this showtime (excluding the current booking)
$query = "SELECT seat_id FROM booking_seats 
          WHERE showtime_id = ? AND booking_id != ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $showtime_id, $booking_id);
$stmt->execute();
$result = $stmt->get_result();

$booked_seats = [];
while($row = $result->fetch_assoc()) {
    $booked_seats[] = (int)$row['seat_id'];
}

// Return the array of booked seat IDs
header('Content-Type: application/json');
echo json_encode($booked_seats);
?>