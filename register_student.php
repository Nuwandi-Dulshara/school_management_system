<?php
include 'connection.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$dob = $_POST['dob'];
$date_of_admission = $_POST['date_of_admission'];
$address = $_POST['address'];

// Encrypt the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO students (name, email, password, dob, date_of_admission, address)
VALUES ('$name', '$email', '$hashed_password', '$dob', '$date_of_admission', '$address')";

if ($conn->query($sql) === TRUE) {
    header('Location: add_student_details.php');
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
