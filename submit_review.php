<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    $bookingId = $_POST["bookingId"];
    $rating = $_POST["rating"];
    $note = $_POST["note"];
    $query = "INSERT INTO stay_reviews (booking_id, rating, note) VALUES (:booking_id, :rating, :note)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':booking_id', $bookingId);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':note', $note);
    if ($stmt->execute()) {
        echo "Review submitted successfully!";
    } else {
        echo "Error submitting review. Please try again.";
    }
} else {
    header("Location: stay_review_form.html");
    exit();
}
?>
