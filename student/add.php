<?php require("../include/conn.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Add Student</title>
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

  $studentnumber = $lastname = $firstname = $middlename = $program = "";
  $error = "";

  // Handle form submission
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentnumber = trim($_POST['txtstudentnumber']);
    $lastname = trim($_POST['txtlastname']);
    $firstname = trim($_POST['txtfirstname']);
    $middlename = trim($_POST['txtmiddlename']);
    $program = trim($_POST['txtprogram']);
    $currentYear = date("Y");
    $yearPrefix = substr($studentnumber, 0, 4);

    if (!preg_match('/^\d{10}$/', $studentnumber)) {
      $error = "Student number must be exactly 10 digits.";
    } elseif ((int)$yearPrefix < 2000 || (int)$yearPrefix > (int)$currentYear) {
      $error = "Invalid year in student number.";
    } else {
      // Check if student number already exists
      $check = $conn->prepare("SELECT fldstudentnumber FROM tblstudent WHERE fldstudentnumber = ?");
      $check->bind_param("s", $studentnumber);
      $check->execute();
      $check->store_result();

      if ($check->num_rows > 0) {
        $error = "Student number already exists.";
      } else {
        // Insert new student
        $stmt = $conn->prepare("INSERT INTO tblstudent (fldstudentnumber, fldlastname, fldfirstname, fldmiddlename, fldprogram) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $studentnumber, $lastname, $firstname, $middlename, $program);
        $stmt->execute();

        header("Location: student.php");
        exit;
      }
    }
  }
  ?>

  <div class="form-container">
    <h2>Add New Student</h2>
    <form method="post" action="">
      <label>Student Number:</label>
      <input type="text" name="txtstudentnumber" value="<?= htmlspecialchars($studentnumber) ?>" required>
      <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
      <?php endif; ?>

      <label>Last Name:</label>
      <input type="text" name="txtlastname" value="<?= htmlspecialchars($lastname) ?>" required>

      <label>First Name:</label>
      <input type="text" name="txtfirstname" value="<?= htmlspecialchars($firstname) ?>" required>

      <label>Middle Name:</label>
      <input type="text" name="txtmiddlename" value="<?= htmlspecialchars($middlename) ?>">

      <label>Program of Study:</label>
      <input type="text" name="txtprogram" value="<?= htmlspecialchars($program) ?>" required>

      <button type="submit">Save</button>
    </form>
    <a href="student.php">← Back to Student Records</a>
  </div>
</body>

</html>