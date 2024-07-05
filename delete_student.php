<?php
include 'connection.php';

$id = $_GET['id'];
$sql = "DELETE FROM students WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    header('Location: add_student_details.php');
} else {
    echo "Error deleting record: " . $conn->error;
}

$conn->close();
?>
