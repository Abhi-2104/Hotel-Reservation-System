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
    $include_breakfast = isset($_POST["include_breakfast"]) ? 1 : 0;

    $availableRoomQuery = $pdo->prepare("SELECT R.room_id FROM Rooms AS R WHERE R.room_type = ? AND R.room_id NOT IN (SELECT B.room_id FROM Bookings AS B WHERE B.checkin_date < ? AND B.checkout_date > ?)");
    $availableRoomQuery->execute([$room_type, $checkout_date, $checkin_date]);

    $availableRooms = $availableRoomQuery->fetchAll(PDO::FETCH_COLUMN);

    if (count($availableRooms) > 0) {
        $room_id = $availableRooms[0];
        $user_id = $_SESSION['user_id'];

        $totalFare = calculate_amount($checkin_date, $checkout_date, $room_type, $include_breakfast, $guests);

        if ($totalFare !== false) {
            $query = "INSERT INTO Bookings (user_id, room_id, checkin_date, checkout_date, total_fare, include_breakfast, num_guests) 
                VALUES (:user_id, :room_id, :checkin_date, :checkout_date, :total_fare, :include_breakfast, :num_guests)";
            $stmt = $pdo->prepare($query);

            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':room_id', $room_id);
            $stmt->bindParam(':checkin_date', $checkin_date);
            $stmt->bindParam(':checkout_date', $checkout_date);
            $stmt->bindParam(':total_fare', $totalFare); 
            $stmt->bindParam(':include_breakfast', $include_breakfast, PDO::PARAM_BOOL);
            $stmt->bindParam(':num_guests', $guests);


            if ($stmt->execute()) {
                $booking_id = $pdo->lastInsertId();
                $getUsernameQuery = $pdo->prepare("SELECT username FROM Users WHERE user_id = ?");
                $getUsernameQuery->execute([$user_id]);
                $user = $getUsernameQuery->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $username = $user['username'];
                    
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
                                <li>Total Cost: $' . number_format($totalFare, 2) . '</li>
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
                echo "Booking failed. Please try again.";
            }
        } else {
            echo "Error calculating total fare. Please try again.";
        }
    } else {
        
        echo "Sorry, no available rooms in the selected category for the specified dates.";
    }
}


function calculate_amount($checkin_date, $checkout_date, $room_type, $include_breakfast, $num_guests) {
    global $pdo; 

    $room_rates = [
        'single' => 1000,
        'double' => 1500,
        'twin' => 2000,
        'suite' => 5000,
    ];

    $breakfast_fee = 200;
    $start_date = new DateTime($checkin_date);
    $end_date = new DateTime($checkout_date);
    $duration = $start_date->diff($end_date)->days;

    if (!array_key_exists(strtolower($room_type), $room_rates)) {
        return false;
    }
    $per_night_fare_query = $pdo->prepare("SELECT rate FROM Rooms WHERE room_type = ? LIMIT 1");
    $per_night_fare_query->execute([$room_type]);
    $per_night_fare = $per_night_fare_query->fetchColumn();

    if (!$per_night_fare) {
        return false;
    }

    $total_fare = $per_night_fare * $duration;

    if ($include_breakfast) {
        $total_fare += $breakfast_fee * $num_guests * $duration;
    }

    return $total_fare;
}
?>
