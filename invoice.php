<?php
session_start();
$host = "localhost";
$port = "5432";
$dbname = "HOTEL";
$user = "postgres";
$password = "12345";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : null;

    if (!$booking_id) {
        header("Location: booking_page.php");
        exit();
    }
    $bookingDetailsQuery = $pdo->prepare("SELECT * FROM Bookings JOIN rooms on Bookings.room_id = rooms.room_id WHERE booking_id = ?");
    $bookingDetailsQuery->execute([$booking_id]);
    $booking = $bookingDetailsQuery->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        
        header("Location: booking_page.php");
        exit();
    }


    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css">
        <style>
       
        
    </style>
        <title>Invoice</title>
    </head>
    <body>
        <div class="container">
            <h1>Invoice</h1>
            <p>Booking Details:</p>
            <ul>
                <li>Booking ID: ' . $booking['booking_id'] . '</li>
                <li>Room Type: ' . $booking['room_type'] . '</li>
                <li>Check-in Date: ' . $booking['checkin_date'] . '</li>
                <li>Check-out Date: ' . $booking['checkout_date'] . '</li>
                <li>Total Cost: $' . number_format($booking['total_fare'], 2) . '</li>
            </ul>
            <p><a href="index.php">Go back to Home</a></p>
            <p><a href="logout.php">Logout</a></p>
        </div>
    </body>
    </html>';

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
