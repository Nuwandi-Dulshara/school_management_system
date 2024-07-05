<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="details.css">
</head>
<body>
<a href="teacher_registration.html"> <button class="add-btn"> Add New Teacher</button></a>
    <div class="container mt-5">
    <div class="title-container">
            <img src="images/add_teacher.jpg" alt="Teacher-Icon" class="title-icon">
            <h2>Teachers Details</h2>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Index Number</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Date of Birth</th>
                    <th>Date of Admission</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'connection.php';
                $sql = "SELECT * FROM teachers";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $index = 1;
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . sprintf("T%04d", $index) . "</td>
                                <td>" . $row["name"] . "</td>
                                <td>" . $row["email"] . "</td>
                                <td>" . $row["dob"] . "</td>
                                <td>" . $row["date_of_admission"] . "</td>
                                <td>" . $row["address"] . "</td>
                                <td>
                                    <a href='update_teacher.php?id=" . $row["id"] . "' class='btn update-btn btn-sm'>Update</a>
                                    <a href='delete_teacher.php?id=" . $row["id"] . "' class='btn delete-btn btn-sm'>Delete</a>
                                </td>
                              </tr>";
                        $index++;
                    }
                } else {
                    echo "<tr><td colspan='7'>No records found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>