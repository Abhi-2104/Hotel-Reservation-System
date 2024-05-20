<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles1.css">
    <title>Hotel Reservation System</title>
</head>
<body>
    <div class="container">
        <h1>Welcome to Radisson Blu, Chennai</h1>
        <p>
            Thank you for choosing our Radisson Blu for your stay. We strive to provide the best experience for our guests.
            Please take a moment to fill in the details to make your reservation.
        </p>

        <h2>About Radisson Blu</h2>
        <p>
            Our hotel is located in a prime location, offering breathtaking views and top-notch amenities.
            Whether you are traveling for business or leisure, we aim to make your stay comfortable and memorable.
        </p>

        <form action="process_booking.php" method="POST">
            <label for="guests">Number of Guests:</label>
            <input type="number" name="guests" required>
            
            <label for="room_type">Room Type:</label>
            <select name="room_type" required>
                <option value="Single">Single</option>
                <option value="Double">Double</option>
                <option value="Twin">Twin</option> 
                <option value="Suite">Suite</option>
            </select>
            
            <label for="checkin_date">Check-in Date:</label>
            <input type="date" name="checkin_date" required>
            
            <label for="checkout_date">Check-out Date:</label>
            <input type="date" name="checkout_date" required>

            <label for="include_breakfast">Include Breakfast:</label>
    <input type="checkbox" name="include_breakfast" value="1">
            
            <button type="submit">Book Now</button>
        </form>

        <p><a href="user_dashboard.php">User Dashboard</a></p>


        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <p><a href="admin_panel.php">Admin Panel</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
