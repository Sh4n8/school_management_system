<?php require("../include/conn.php"); ?>

<?php
$code = $title = $units = $error = "";
$original_code = "";

// If course code not passed
if (!isset($_GET['vid'])) {
    header("Location: course.php");
    exit;
}

// Load original data
$vid = $_GET['vid'];
$stmt = $conn->prepare("SELECT * FROM tblcourse WHERE fldcoursecode = ?");
$stmt->bind_param("s", $vid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: course.php");
    exit;
}

$course = $result->fetch_assoc();
$original_code = $course['fldcoursecode'];
$code = $course['fldcoursecode'];
$title = $course['fldcoursetitle'];
$units = $course['fldunits'];

// If form is submitted
if (isset($_POST['submit'])) {
    $original_code = $_POST['original_code'];
    $code = trim($_POST['course_code']);
    $title = trim($_POST['course_title']);
    $units = $_POST['units'];

    // Validation
    if (!preg_match("/^[A-Z][a-zA-Z0-9]{3,14}$/", $code)) {
        $error = "Invalid course code format.";
    } elseif (!is_numeric($units) || $units <= 0) {
        $error = "Units must be a positive number.";
    } elseif (strlen($title) < 4) {
        $error = "Course title must be at least 4 characters.";
    } elseif ($code !== $original_code) {
        $check = $conn->prepare("SELECT fldcoursecode FROM tblcourse WHERE fldcoursecode = ?");
        $check->bind_param("s", $code);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = "Course code already exists.";
        }
    }

    // If no error, update
    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE tblcourse SET fldcoursecode = ?, fldcoursetitle = ?, fldunits = ? WHERE fldcoursecode = ?");
        $stmt->bind_param("ssis", $code, $title, $units, $original_code);
        $stmt->execute();

        echo "<script>
            alert('Course Updated.');
            window.location.href = 'course.php';
        </script>";
        exit;
    }
}
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
        <h2>Update Course</h2>
        <form method="POST" action="">
            <input type="text" name="course_code" value="<?= htmlspecialchars($code) ?>" required>
            <input type="hidden" name="original_code" value="<?= htmlspecialchars($original_code) ?>">

            <?php if (!empty($error)) : ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <input type="text" name="course_title" value="<?= htmlspecialchars($title) ?>" required>
            <input type="number" name="units" value="<?= htmlspecialchars($units) ?>" min="1" required>
            <button type="submit" name="submit">Update Course</button>
        </form>
        <a href="course.php">← Back to Course Records</a>
    </div>

</body>

</html>