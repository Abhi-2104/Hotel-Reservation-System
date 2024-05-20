<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    $host = "localhost";
    $port = "5432";
    $dbname = "HOTEL";
    $user = "postgres";
    $password = "12345";

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

    try {
        $pdo = new PDO($dsn);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    $getBookingDetailsQuery = $pdo->prepare("SELECT * FROM Bookings WHERE booking_id = ?");
    $getBookingDetailsQuery->execute([$booking_id]);
    $bookingDetails = $getBookingDetailsQuery->fetch(PDO::FETCH_ASSOC);

    if ($bookingDetails) {
        $room_type = $bookingDetails['room_type'];
        $checkin_date = $bookingDetails['checkin_date'];
        $checkout_date = $bookingDetails['checkout_date'];
        $total_fare = $bookingDetails['total_fare'];

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
                    <h1>Booking Confirmation</h1>
                    <p>Your booking has been confirmed. Thank you, ' . $_SESSION['username'] . '!</p>
                    <p>Booking Details:</p>
                    <ul>
                        <li>Booking ID: ' . $booking_id . '</li>
                        <li>Room Type: ' . $room_type . '</li>
                        <li>Check-in Date: ' . $checkin_date . '</li>
                        <li>Check-out Date: ' . $checkout_date . '</li>
                        <li>Total Fare: ₹' . $total_fare . '</li>
                    </ul>
                    <p><a href="index.php">Go back to Home</a></p>
                    <p><a href="logout.php">Logout</a></p>
                </div>
            </body>
            </html>';
    } else {
        echo "Error retrieving booking details.";
    }
} else {
    echo "Error: Booking ID is not set.";
}
?>