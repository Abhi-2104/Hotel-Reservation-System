<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $full_name = $_POST["full_name"];
    $email = $_POST["email"];
    $phone_number = $_POST["phone_number"];
    $role = $_POST["role"];

    $host = "localhost";
    $port = "5432";
    $dbname = "HOTEL";
    $user = "postgres";
    $password_db = "12345";

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password_db";

    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $checkUserQuery = $pdo->prepare("SELECT user_id FROM Users WHERE username = ?");
        $checkUserQuery->execute([$username]);

        if ($checkUserQuery->rowCount() > 0) {
            echo "Username already exists. Please choose a different username.";
        } else {
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$insertUserQuery = $pdo->prepare("INSERT INTO Users (username, password, role) VALUES (?, ?, ?)");
$insertUserQuery->execute([$username, $hashedPassword, $role]);

            $user_id = $pdo->lastInsertId();
            if ($role === "customer") {
                $insertCustomerQuery = $pdo->prepare("INSERT INTO Customers (user_id, full_name, email, phone_number) VALUES (?, ?, ?, ?)");
                $insertCustomerQuery->execute([$user_id, $full_name, $email, $phone_number]);
            }

            echo "Sign up successful!";
            sheader("Location: login.php");
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>


