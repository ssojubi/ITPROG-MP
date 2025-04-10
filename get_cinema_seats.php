<?php
session_start();
include("connection.php");

// Redirect if not logged in or not admin
if(!isset($_SESSION['email']) || !isset($_SESSION['type']) || $_SESSION['type'] != 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Check if cinema_id is provided
if(!isset($_POST['cinema_id']) || empty($_POST['cinema_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Cinema ID is required']);
    exit();
}

$cinema_id = $_POST['cinema_id'];

// Get all seats for this cinema
$query = "SELECT seat_id, seat_row, seat_number, cinema_id, status 
          FROM seats 
          WHERE cinema_id = ? 
          ORDER BY seat_row, seat_number";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cinema_id);
$stmt->execute();
$result = $stmt->get_result();

$seats = [];
while($row = $result->fetch_assoc()) {
    $seats[] = $row;
}

// Return the seats as JSON
header('Content-Type: application/json');
echo json_encode($seats);
exit();
?>