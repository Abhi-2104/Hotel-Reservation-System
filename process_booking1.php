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
$totalFare = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guests = isset($_POST["guests"]) ? $_POST["guests"] : "";
    $room_type = isset($_POST["room_type"]) ? $_POST["room_type"] : "";
    $checkin_date = isset($_POST["checkin_date"]) ? $_POST["checkin_date"] : "";
    $checkout_date = isset($_POST["checkout_date"]) ? $_POST["checkout_date"] : "";

    $availableRoomQuery = $pdo->prepare("SELECT R.room_id, R.rate FROM Rooms AS R WHERE R.room_type = ? AND R.room_id NOT IN (SELECT B.room_id FROM Bookings AS B WHERE B.checkin_date < ? AND B.checkout_date > ?)");
    $availableRoomQuery->execute([$room_type, $checkout_date, $checkin_date]);

    $availableRoom = $availableRoomQuery->fetch(PDO::FETCH_ASSOC);
    var_dump($availableRoom);

    if ($availableRoom) {
        $room_id = $availableRoom['room_id'];
        $basePrice = $availableRoom['rate']; 

        $checkinDate = new DateTime($checkin_date);
$checkoutDate = new DateTime($checkout_date);

$checkinTimestamp = $checkinDate->getTimestamp();
$checkoutTimestamp = $checkoutDate->getTimestamp();

$numberOfDays = ceil(($checkoutTimestamp - $checkinTimestamp) / (60 * 60 * 24));

$totalFare = $basePrice * $numberOfDays;
$gst = 0.18 * $totalFare;
$totalFareWithGST = $totalFare + $gst;


$customer_id = $_SESSION['user_id'];
$getUsernameQuery = $pdo->prepare("SELECT username FROM Users WHERE user_id = ?");
$getUsernameQuery->execute([$customer_id]);
$user = $getUsernameQuery->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $username = $user['username'];
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css">
        <title>Booking Bill</title>
    </head>
    <body>
        <div class="container">
            <h1>Booking Bill</h1>
            <p>Dear ' . $username . ',</p>
            <p>Booking Details:</p>
            <ul>
                <li>Room Type: ' . $room_type . '</li>
                <li>Check-in Date: ' . $checkin_date . '</li>
                <li>Check-out Date: ' . $checkout_date . '</li>
            </ul>
            <p>Base Fare : ₹' . $totalFare . '</p>
            <p>GST (18%): ₹' . $gst . '</p>
            <p>Total Fare : ₹' . $totalFareWithGST . '</p>
            <p>Click "Confirm" to complete your booking.</p>
            <form action="process_booking.php" method="POST">
                <input type="hidden" name="room_id" value="' . $room_id . '">
                <input type="hidden" name="checkin_date" value="' . $checkin_date . '">
                <input type="hidden" name="checkout_date" value="' . $checkout_date . '">
                <input type="hidden" name="total_fare" value="' . $totalFareWithGST . '">
                <button type="submit" name="confirm_booking">Confirm</button>
            </form>
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
} elseif (isset($_POST["confirm_booking"])) {

    $customer_id = $_SESSION['user_id'];
    $room_id = $_POST["room_id"];
    $checkin_date = $_POST["checkin_date"];
    $checkout_date = $_POST["checkout_date"];
    $total_fare = $_POST["total_fare"];

    $insertBookingQuery = $pdo->prepare("INSERT INTO Bookings (customer_id, room_id, checkin_date, checkout_date, total_fare) VALUES (?, ?, ?, ?, ?)");
    $insertBookingQuery->execute([$customer_id, $room_id, $checkin_date, $checkout_date, $total_fare]);
    header("Location: user_dashboard.php");
    exit();
}
?>
