<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

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

$username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guests = $_POST["guests"];
    $room_type = $_POST["room_type"];
    $checkin_date = $_POST["checkin_date"];
    $checkout_date = $_POST["checkout_date"];

    $availableRoomQuery = $pdo->prepare("SELECT R.room_id FROM Rooms AS R WHERE R.room_type = ? AND R.room_id NOT IN (SELECT B.room_id FROM Bookings AS B WHERE B.checkin_date < ? AND B.checkout_date > ?)");
    $availableRoomQuery->execute([$room_type, $checkout_date, $checkin_date]);

    $availableRooms = $availableRoomQuery->fetchAll(PDO::FETCH_COLUMN);

    if (count($availableRooms) > 0) {
        $room_id = $availableRooms[0];

        $customer_id = $_SESSION['user_id'];

        $insertBookingQuery = $pdo->prepare("INSERT INTO Bookings (customer_id, room_id, checkin_date, checkout_date) VALUES (?, ?, ?, ?)");
        $insertBookingQuery->execute([$customer_id, $room_id, $checkin_date, $checkout_date]);

        $getUsernameQuery = $pdo->prepare("SELECT username FROM Users WHERE user_id = ?");
        $getUsernameQuery->execute([$customer_id]);
        $user = $getUsernameQuery->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $username = $user['username'];
            $booking_id = $pdo->lastInsertId(); 

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
                        <p>Your booking has been confirmed. Thank you, ' . $username . '!</p>
                        <p>Booking Details:</p>
                        <ul>
                            <li>Booking ID: ' . $booking_id . '</li>
                            <li>Room Type: ' . $room_type . '</li>
                            <li>Check-in Date: ' . $checkin_date . '</li>
                            <li>Check-out Date: ' . $checkout_date . '</li>
                        </ul>
                        <p><a href="index.php">Go back to Home</a></p>
                        <p><a href="logout.php">Logout</a></p>
                    </div>
                </body>
                </html>';
        } else {
            echo "Error retrieving username.";
        }
    } else {
        echo "Sorry, no available rooms in the selected category for the specified dates.";
    }
}
?>
