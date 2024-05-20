<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $userBookingsQuery = $pdo->prepare("SELECT B.booking_id, R.room_number, B.checkin_date, B.checkout_date FROM Bookings AS B JOIN Rooms AS R ON B.room_id = R.room_id WHERE B.user_id = ?");
    $userBookingsQuery->execute([$_SESSION['user_id']]);
    $userBookings = $userBookingsQuery->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $bookingId = $_POST["booking_id"];
        $newCheckinDate = $_POST["new_checkin_date"];
        $newCheckoutDate = $_POST["new_checkout_date"];
        $updateBookingQuery = $pdo->prepare("UPDATE Bookings SET checkin_date = ?, checkout_date = ? WHERE booking_id = ?");
        $updateBookingQuery->execute([$newCheckinDate, $newCheckoutDate, $bookingId]);
        header("Location: user_dashboard.php");
        exit();
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles1.css">
    <title>User Dashboard</title>
</head>
<body>
    <div class="container">
        <h1>Welcome to Your Dashboard<?php if (isset($_SESSION['username'])) echo ", " . $_SESSION['username'] ?></h1>

        <p><a href="index.php">Book a New Room</a></p>

        <h2>Your Bookings</h2>
        <?php if (!empty($userBookings)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Room Number</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Update Dates</th>
                        <th>Reviews</th>
                        <th>Invoice</th>
                        <th>Cancel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userBookings as $booking): ?>
                        <tr>
                            <td><?= $booking['booking_id'] ?></td>
                            <td><?= $booking['room_number'] ?></td>
                            <td><?= $booking['checkin_date'] ?></td>
                            <td><?= $booking['checkout_date'] ?></td>
                            
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                    <label for="new_checkin_date">New Check-in Date:</label>
                                    <input type="date" name="new_checkin_date" required>
                                    <label for="new_checkout_date">New Check-out Date:</label>
                                    <input type="date" name="new_checkout_date" required>
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                            <td>
                                <a href="review.html?booking_id=<?= $booking['booking_id'] ?>">Leave Review</a>
                            </td>
                            <td>
                                <a href="invoice.php?booking_id=<?= $booking['booking_id'] ?>">View Invoice</a>
                            </td>
                            <td><a href="delete_booking.php?booking_id=<?= $booking['booking_id'] ?>">Cancel</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no bookings.</p>
        <?php endif; ?>

        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
