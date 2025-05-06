<?php require("../include/conn.php"); ?>

<?php
// Check if course code is passed
if (!isset($_GET['vid'])) {
    header("Location: course.php");
    exit;
}

$vid = $_GET['vid'];
$stmt = $conn->prepare("SELECT * FROM tblcourse WHERE fldcoursecode = ?");
$stmt->bind_param("s", $vid);
$stmt->execute();
$result = $stmt->get_result();

// If no such course, redirect
if ($result->num_rows === 0) {
    header("Location: course.php");
    exit;
}

$course = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Course</title>
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

        input[type="text"],
        input[type="number"] {
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
        <h2>Update Course</h2>
        <form method="POST" action="">
            <input type="text" name="course_code" value="<?php echo htmlspecialchars($course['fldcoursecode']); ?>" readonly>
            <input type="text" name="course_title" value="<?php echo htmlspecialchars($course['fldcoursetitle']); ?>" required>
            <input type="number" name="units" value="<?php echo htmlspecialchars($course['fldunits']); ?>" min="1" required>
            <button type="submit" name="submit">Update Course</button>
        </form>
        <a href="course.php">← Back to Course Records</a>
    </div>

    <?php
    if (isset($_POST['submit'])) {
        $code = trim($_POST['course_code']);
        $title = trim($_POST['course_title']);
        $units = $_POST['units'];

        // Silent validation for units
        if (!is_numeric($units) || $units <= 0) {
            return; 
        }

        
        $stmt = $conn->prepare("UPDATE tblcourse SET fldcoursetitle = ?, fldunits = ? WHERE fldcoursecode = ?");
        $stmt->bind_param("sis", $title, $units, $code);
        $stmt->execute();

        
        echo "<script>
            alert('Course Updated.');
            window.location.href = 'course.php';
        </script>";
    }
    ?>
</body>

</html>