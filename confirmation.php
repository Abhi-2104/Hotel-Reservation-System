<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["booking_id"])) {
    $bookingId = $_GET["booking_id"];


    $host = "localhost";
    $port = "5432";
    $dbname = "HOTEL";
    $user = "postgres";
    $password = "12345";

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
}

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : null;

if (!$booking_id) {
 
    header("Location: booking_page.php");
    exit();
}


$bookingDetailsQuery = $pdo->prepare("SELECT * FROM Bookings WHERE booking_id = ?");
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
    <title>Booking Confirmation</title>
</head>
<body>
    <div class="container">
        <h1>Booking Confirmed!</h1>
        <p>Your booking has been confirmed. Thank you!</p>
        <p><a href="invoice.php?booking_id=' . $booking_id . '">View Invoice</a></p>
        <p><a href="index.php">Go back to Home</a></p>
        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>';
?>
