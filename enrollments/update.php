<?php
require("../include/conn.php");

if (!isset($_GET['vid'])) {
  echo "Invalid request.";
  exit;
}

$student_id = $_GET['vid'];

// Get student details
$stmt = $conn->prepare("SELECT fldstudentnumber, fldfirstname, fldlastname FROM tblstudent WHERE fldindex = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
if ($student_result->num_rows === 0) {
  echo "Student not found.";
  exit;
}
$student = $student_result->fetch_assoc();

// Get courses student is NOT yet enrolled in
$course_query = $conn->prepare("
  SELECT fldindex, fldcoursecode, fldcoursetitle 
  FROM tblcourse 
  WHERE fldindex NOT IN (
    SELECT fldcoursecode 
    FROM tblenrollment 
    WHERE fldstudentnumber = ?
  )
");
$course_query->bind_param("i", $student_id);
$course_query->execute();
$courses_result = $course_query->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_course'])) {
  $new_course = $_POST['new_course'];

  // Enroll student in selected course
  $insert = $conn->prepare("INSERT INTO tblenrollment (fldstudentnumber, fldcoursecode) VALUES (?, ?)");
  $insert->bind_param("ii", $student_id, $new_course);
  if ($insert->execute()) {
    header("Location: enroll.php?msg=updated");
    exit;
  } else {
    $error = "Failed to enroll in new course: " . $insert->error;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Enrollment</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f0f4f8;
      padding: 60px;
      text-align: center;
    }

    .edit-box {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      display: inline-block;
      padding: 40px 50px;
      text-align: left;
      max-width: 600px;
    }

    h2 {
      color: #00703c;
    }

    .details {
      margin: 20px 0;
      font-size: 16px;
    }

    .details p {
      margin: 6px 0;
    }

    .form-group {
      margin: 20px 0;
    }

    select {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      width: 100%;
    }

    .btn {
      padding: 10px 20px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      font-weight: bold;
      margin-right: 10px;
      text-decoration: none;
      display: inline-block;
    }

    .btn-submit {
      background-color: #00703c;
      color: white;
    }

    .btn-submit:hover {
      background-color: #00572e;
    }

    .btn-back {
      background-color: #c62828;
      color: white;
      margin-bottom: 20px;
      display: inline-block;
    }

    .btn-back:hover {
      background-color: #a41e1e;
    }

    .error {
      color: red;
      font-size: 14px;
      margin-bottom: 10px;
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

  <div class="edit-box">
    <h2>Update Enrollment</h2>

    <div class="details">
      <p><strong>Student Number:</strong> <?= htmlspecialchars($student['fldstudentnumber']) ?></p>
      <p><strong>Name:</strong> <?= htmlspecialchars($student['fldlastname'] . ', ' . $student['fldfirstname']) ?></p>
    </div>

    <?php if (isset($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="new_course"><strong>Select New Course:</strong></label>
        <select name="new_course" required>
          <option value="">-- Select Course --</option>
          <?php while ($course = $courses_result->fetch_assoc()): ?>
            <option value="<?= $course['fldindex'] ?>">
              <?= htmlspecialchars($course['fldcoursecode'] . ' - ' . $course['fldcoursetitle']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-submit">Update Enrollment</button>
    </form>
    <a href="enroll.php"> ← Back to Enrollment</a>
  </div>
</body>

</html>