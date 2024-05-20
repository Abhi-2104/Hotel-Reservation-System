<?php
session_start();
$host = "localhost";
$port = "5432";
$dbname = "HOTEL";
$user = "postgres";
$password_db = "12345";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password_db";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["check_availability"])) {
        $checkinDate = $_POST["checkin_date"];
        $checkoutDate = $_POST["checkout_date"];
        $getAvailableRoomsQuery = $pdo->prepare("SELECT * FROM Rooms WHERE room_id NOT IN (
            SELECT room_id FROM Bookings
            WHERE (checkin_date < ? AND checkout_date > ?) OR (checkin_date < ? AND checkout_date > ?))");
        $getAvailableRoomsQuery->execute([$checkoutDate, $checkinDate, $checkinDate, $checkoutDate]);
        $availableRooms = $getAvailableRoomsQuery->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["show_bookings"])) {
        $getCurrentBookingsQuery = $pdo->query("SELECT * FROM Bookings");
        $currentBookings = $getCurrentBookingsQuery->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["total_revenue"])) {
        $selectedMonth = $_POST["selected_month"];
        $selectedYear = $_POST["selected_year"];
        $getTotalRevenueQuery = $pdo->prepare("SELECT COALESCE(SUM(total_fare), 0) AS total_revenue 
            FROM Bookings 
            WHERE EXTRACT(MONTH FROM checkin_date) = ? AND EXTRACT(YEAR FROM checkin_date) = ?");
        $getTotalRevenueQuery->execute([$selectedMonth, $selectedYear]);
        $totalRevenueResult = $getTotalRevenueQuery->fetch(PDO::FETCH_ASSOC);
        $totalRevenue = $totalRevenueResult['total_revenue'];
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Initialize $availableRooms, $currentBookings, and $totalRevenue as empty if not set
$availableRooms = isset($availableRooms) ? $availableRooms : [];
$currentBookings = isset($currentBookings) ? $currentBookings : [];
$totalRevenue = isset($totalRevenue) ? $totalRevenue : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="styles1.css">
</head>
<body>
    <div class="container">
        <h1>Admin Panel</h1>

        <!-- Form for checking availability -->
        <form method="POST" action="">
            <label for="checkin_date">Check-in Date:</label>
            <input type="date" name="checkin_date" required>
            <label for="checkout_date">Check-out Date:</label>
            <input type="date" name="checkout_date" required>
            <button type="submit" name="check_availability">Check Availability</button>
        </form>

        <?php if (!empty($availableRooms)): ?>
            <!-- Show available rooms -->
            <h2>Available Rooms</h2>
            <ul>
                <?php foreach ($availableRooms as $room): ?>
                    <li>Room ID: <?= $room['room_id'] ?> | Room Type: <?= $room['room_type'] ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Form for showing current bookings -->
        <form method="POST" action="">
            <br><br>
            <button type="submit" name="show_bookings">Show Current Bookings</button>
        </form>

        <?php if (!empty($currentBookings)): ?>
            
            <h2>Current Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer ID</th>
                        <th>Room ID</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currentBookings as $booking): ?>
                        <tr>
                            <td><?= $booking['booking_id'] ?></td>
                            <td><?= $booking['user_id'] ?></td>
                            <td><?= $booking['room_id'] ?></td>
                            <td><?= $booking['checkin_date'] ?></td>
                            <td><?= $booking['checkout_date'] ?></td>
                            <td>
                                <!-- View Invoice and Cancel Booking buttons -->
                                <a href="invoice.php?booking_id=<?= $booking['booking_id'] ?>" target="_blank">
                                    <button type="button">View Invoice</button>
                                </a>
                                
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Form for total revenue -->
        <form method="POST" action="">
            <br><br><br>
            <label for="selected_month">Select Month:</label>
            <select name="selected_month" required>
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <label for="selected_year">Enter Year:</label>
            <input type="number" name="selected_year" required>
            <button type="submit" name="total_revenue">Calculate Total Revenue</button>
        </form>

        <?php
            if (isset($_POST["total_revenue"])) {
                $selectedMonth = $_POST["selected_month"];
                $selectedYear = $_POST["selected_year"];
                
                // Calculate total revenue for the selected month
                $getTotalRevenueQuery = $pdo->prepare("SELECT COALESCE(SUM(total_fare), 0) AS total_revenue 
                    FROM Bookings 
                    WHERE EXTRACT(MONTH FROM checkin_date) = ? AND EXTRACT(YEAR FROM checkin_date) = ?");
                $getTotalRevenueQuery->execute([$selectedMonth, $selectedYear]);
                $totalRevenueResult = $getTotalRevenueQuery->fetch(PDO::FETCH_ASSOC);
                $totalRevenue = $totalRevenueResult['total_revenue'];

                // Show total revenue
                echo '<h2>Total Revenue</h2>';
                echo '<p>Total Revenue for ' . date("F Y", mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) . ': $' . number_format($totalRevenue, 2) . '</p>';
            }
        ?>

        <!-- JavaScript for confirmation dialog -->
        <script>
            function confirmCancellation(bookingId) {
                var confirmCancel = confirm("Are you sure you want to cancel this booking?");
                if (confirmCancel) {
                    // Redirect to cancelBooking.php with the booking ID
                    window.location.href = "delete_booking.php?booking_id=" + bookingId;
                }
            }
        </script>

        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
