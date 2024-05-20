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
        $getUserQuery = $pdo->prepare("SELECT u.user_id, u.password,u.role,c.customer_id FROM Users u LEFT JOIN Customers c ON u.user_id = c.user_id WHERE u.username = ?");
        $getUserQuery->execute([$username]);
        $user = $getUserQuery->fetch(PDO::FETCH_ASSOC);

        echo "SQL Query: " . $getUserQuery->queryString . "<br>";
        echo "User Information: ";
        var_dump($user);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id']; 

            if ($user['customer_id']) {
                header("Location: user_dashboard.php"); 
                exit();
            } else {
                header("Location: admin_panel.php");
                exit();
            }
       
    } 
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
}
?>
