<?php require("../include/conn.php"); ?>

<?php
// Check if student ID is passed
if (!isset($_GET['vid'])) {
    header("Location: student.php");
    exit;
}

$vid = $_GET['vid'];
$stmt = $conn->prepare("SELECT * FROM tblstudent WHERE fldstudentnumber = ?");
$stmt->bind_param("s", $vid);
$stmt->execute();
$result = $stmt->get_result();

// If no such student, redirect
if ($result->num_rows === 0) {
    header("Location: student.php");
    exit;
}

$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Student</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            padding: 40px;
        }

        .form-container {
            max-width: 500px;
            margin: auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
            color: #00703c;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        input[readonly] {
            background-color: #f9f9f9;
        }

        button {
            background-color: #00703c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #00572e;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #00703c;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2>Update Student</h2>
        <form method="POST" action="">
            <input type="text" name="student_number" value="<?php echo htmlspecialchars($student['fldstudentnumber']); ?>" readonly>
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($student['fldlastname']); ?>" required>
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($student['fldfirstname']); ?>" required>
            <input type="text" name="middlename" value="<?php echo htmlspecialchars($student['fldmiddlename']); ?>" required>
            <button type="submit" name="submit">Update Student</button>
        </form>
        <a href="student.php">← Back to Student Records</a>
    </div>

    <?php
    if (isset($_POST['submit'])) {
        $lastname = trim($_POST['lastname']);
        $firstname = trim($_POST['firstname']);
        $middlename = trim($_POST['middlename']);

        $stmt = $conn->prepare("UPDATE tblstudent SET fldlastname = ?, fldfirstname = ?, fldmiddlename = ? WHERE fldstudentnumber = ?");
        $stmt->bind_param("ssss", $lastname, $firstname, $middlename, $vid);
        $stmt->execute();

        echo "<script>
            alert('Student Updated.');
            window.location.href = 'student.php';
        </script>";
    }
    ?>
</body>

</html>