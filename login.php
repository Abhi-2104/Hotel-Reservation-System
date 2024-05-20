<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $host = "localhost";
    $port = "5432";
    $dbname = "HOTEL";
    $user = "postgres";
    $password_db = "12345";

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password_db";

    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $getUserQuery = $pdo->prepare("SELECT user_id, password,role FROM Users WHERE username = ?");
        $getUserQuery->execute([$username]);
        $user = $getUserQuery->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            if ($user['role']=='customer') {
                header("Location: user_dashboard.php");
                exit();
            } else {
                header("Location: admin_panel.php");
                exit();
            }
        } else {
            echo "Invalid username or password";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles1.css">
    <title>Hotel Reservation System - Login</title>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        
        <?php if (isset($error_message)) : ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Log In</button>
        </form>

        <p>Don't have an account? <a href="signup.html">Sign Up</a></p>
    </div>
</body>
</html>