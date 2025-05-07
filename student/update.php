<?php require("../include/conn.php"); ?>

<?php
$studentnumber = $lastname = $firstname = $middlename = $program = "";
$error = "";

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

if ($result->num_rows === 0) {
    header("Location: student.php");
    exit;
}

$student = $result->fetch_assoc();
$studentnumber = $student['fldstudentnumber'];
$lastname = $student['fldlastname'];
$firstname = $student['fldfirstname'];
$middlename = $student['fldmiddlename'];
$program = $student['fldprogram'];

if (isset($_POST['submit'])) {
    $original_studentnumber = trim($_POST['original_student_number']);
    $studentnumber = trim($_POST['student_number']);
    $lastname = trim($_POST['lastname']);
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $program = trim($_POST['program']);
    $currentYear = date("Y");
    $yearPrefix = substr($studentnumber, 0, 4);

    if (!preg_match('/^\d{10}$/', $studentnumber)) {
        $error = "Student number must be exactly 10 digits.";
    } elseif ((int)$yearPrefix < 2000 || (int)$yearPrefix > (int)$currentYear) {
        $error = "Invalid year in student number.";
    } elseif (strlen($lastname) < 2 || strlen($firstname) < 2 || strlen($program) < 2) {
        $error = "All fields must be filled correctly.";
    } elseif ($studentnumber !== $original_studentnumber) {
        $check = $conn->prepare("SELECT fldstudentnumber FROM tblstudent WHERE fldstudentnumber = ?");
        $check->bind_param("s", $studentnumber);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = "Student number already exists.";
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE tblstudent SET fldstudentnumber = ?, fldlastname = ?, fldfirstname = ?, fldmiddlename = ?, fldprogram = ? WHERE fldstudentnumber = ?");
        $stmt->bind_param("ssssss", $studentnumber, $lastname, $firstname, $middlename, $program, $original_studentnumber);
        $stmt->execute();

        echo "<script>
            alert('Student Updated.');
            window.location.href = 'student.php';
        </script>";
        exit;
    }
}
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

        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
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
            <label>Student Number:</label>
            <input type="text" name="student_number" value="<?= htmlspecialchars($studentnumber) ?>" required>
            <input type="hidden" name="original_student_number" value="<?= htmlspecialchars($student['fldstudentnumber']) ?>">

            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <label>Last Name:</label>
            <input type="text" name="lastname" value="<?= htmlspecialchars($lastname) ?>" required>

            <label>First Name:</label>
            <input type="text" name="firstname" value="<?= htmlspecialchars($firstname) ?>" required>

            <label>Middle Name:</label>
            <input type="text" name="middlename" value="<?= htmlspecialchars($middlename) ?>">

            <label>Program of Study:</label>
            <input type="text" name="program" value="<?= htmlspecialchars($program) ?>" required>

            <button type="submit" name="submit">Update Student</button>
        </form>
        <a href="student.php">← Back to Student Records</a>
    </div>

</body>

</html>