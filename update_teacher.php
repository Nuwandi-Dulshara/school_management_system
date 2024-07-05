<?php
include 'connection.php';

$id = $_GET['id'];
$sql = "SELECT * FROM teachers WHERE id=$id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
    $email = $row['email'];
    $dob = $row['dob'];
    $date_of_admission = $row['date_of_admission'];
    $address = $row['address'];
} else {
    echo "Record not found";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $date_of_admission = $_POST['date_of_admission'];
    $address = $_POST['address'];

    $sql = "UPDATE teachers SET name='$name', email='$email', dob='$dob', date_of_admission='$date_of_admission', address='$address' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header('Location: add_teacher_details.php');
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Teacher</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="update.css">
</head>
<body>
    <a href="add_teacher_details.php"> <button class="back-btn"> Back </button></a>
    <div class="container mt-5">
        <div class="title-container">
            <img src="images/update_teacher.jpg" alt="Teacher-Icon" class="title-icon">
            <h2>Update Teachers Details</h2>
        </div>
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?> " required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo $dob; ?>" required>
            </div>
            <div class="form-group">
                <label for="date_of_admission">Date of Admission:</label>
                <input type="date" class="form-control" id="date_of_admission" name="date_of_admission" value="<?php echo $date_of_admission; ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea class="form-control" id="address" name="address" required><?php echo $address; ?></textarea>
            </div>
            <button type="submit" class="btn up-btn">Update</button>
        </form>
    </div>
</body>
</html>

