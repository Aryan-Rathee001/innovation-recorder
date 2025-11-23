<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idea_id = $_POST['idea_id'];
    $rater_name = $_POST['rater_name'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO ratings (idea_id, rater_name, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $idea_id, $rater_name, $rating, $comment);
    
    if ($stmt->execute()) {
        echo "<script>alert('Rating submitted!'); window.location.href='view_ideas.php';</script>";
    } else {
        echo "<script>alert('Error!'); window.location.href='view_ideas.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: view_ideas.php");
}
?>
