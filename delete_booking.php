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

    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $checkBookingQuery = $pdo->prepare("SELECT 1 FROM Bookings WHERE booking_id = ? AND user_id = ?");
$checkBookingQuery->execute([$bookingId, $_SESSION['user_id']]);

        $bookingExists = $checkBookingQuery->fetch(PDO::FETCH_COLUMN);

        if ($bookingExists) {
            $deleteBookingQuery = $pdo->prepare("DELETE FROM Bookings WHERE booking_id = ?");
            $deleteBookingQuery->execute([$bookingId]);
            echo "Booking deleted successfully!";
        } else {
            echo "Invalid booking ID or you don't have permission to delete this booking.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
