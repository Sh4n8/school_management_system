<?php require("../include/conn.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Add New Course</title>
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
      margin-bottom: 10px;
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

  <?php
  $error = "";
  $code = $title = $units = "";

  if (isset($_POST['submit'])) {
    $code = trim($_POST['course_code']);
    $title = trim($_POST['course_title']);
    $units = $_POST['units'];

    if (!is_numeric($units) || $units <= 0) {
      // Optionally handle invalid units
    } else {
      // Check if course code already exists
      $check = $conn->prepare("SELECT fldcoursecode FROM tblcourse WHERE fldcoursecode = ?");
      $check->bind_param("s", $code);
      $check->execute();
      $check->store_result();

      if ($check->num_rows > 0) {
        $error = "Course code already exists.";
      } else {
        // Insert new course
        $stmt = $conn->prepare("INSERT INTO tblcourse (fldcoursecode, fldcoursetitle, fldunits) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $code, $title, $units);
        $stmt->execute();

        header("Location: course.php");
        exit;
      }
    }
  }
  ?>

  <div class="form-container">
    <h2>Add New Course</h2>
    <form method="POST" action="">
      <input type="text" name="course_code" placeholder="Course Code" value="<?= htmlspecialchars($code) ?>" required>
      <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
      <?php endif; ?>
      <input type="text" name="course_title" placeholder="Course Title" value="<?= htmlspecialchars($title) ?>" required>
      <input type="number" name="units" placeholder="Units" value="<?= htmlspecialchars($units) ?>" min="1" required>
      <button type="submit" name="submit">Save</button>
    </form>
    <a href="course.php">← Back to Course Records</a>
  </div>

</body>

</html>