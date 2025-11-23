<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $submitter = $_POST['submitter'];

    $stmt = $conn->prepare("INSERT INTO ideas (title, description, category, submitter) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $description, $category, $submitter);
    
    if ($stmt->execute()) {
        echo "<script>alert('Idea submitted!'); window.location.href='submit_idea.html';</script>";
    } else {
        echo "<script>alert('Error!'); window.location.href='submit_idea.html';</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: submit_idea.html");
}
?>
