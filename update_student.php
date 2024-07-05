<?php
include 'connection.php';

// Check if the 'id' parameter is set in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Error: ID parameter missing in the URL.";
    exit();
}

$id = $_GET['id'];

// Use prepared statements to prevent SQL injection
$sql = "SELECT * FROM students WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
    $email = $row['email'];
    $dob = $row['dob'];
    $date_of_admission = $row['date_of_admission'];
    $address = $row['address'];
} else {
    echo "Record not found";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $date_of_admission = $_POST['date_of_admission'];
    $address = $_POST['address'];

    $update_sql = "UPDATE students SET name=?, email=?, dob=?, date_of_admission=?, address=? WHERE id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssi", $name, $email, $dob, $date_of_admission, $address, $id);

    if ($update_stmt->execute()) {
        header('Location: add_student_details.php');
        exit();
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
    <title>Update Student</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="update.css">
</head>
<body>
    <a href="add_student_details.php"> <button class="back-btn"> Back </button></a>
    <div class="container mt-5">
        <div class="title-container">
            <img src="images/update_student.jpg" alt="Student-Icon" class="title-icon">
            <h2>Update Students Details</h2>
        </div>
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($dob); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_of_admission">Date of Admission:</label>
                <input type="date" class="form-control" id="date_of_admission" name="date_of_admission" value="<?php echo htmlspecialchars($date_of_admission); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea class="form-control" id="address" name="address" required><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            <button type="submit" class="btn up-btn">Update</button>
        </form>
    </div>
</body>
</html>
