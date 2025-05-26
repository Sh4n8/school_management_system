<?php
require("../../include/conn.php");

$student_number = $_GET['student'] ?? '';
$course_index = $_GET['course'] ?? '';

if (!$student_number || !$course_index) {
  echo "Invalid request.";
  exit;
}

// Get student info
$student_stmt = $conn->prepare("SELECT fldstudentnumber, fldfirstname, fldlastname FROM tblstudent WHERE fldstudentnumber = ?");
$student_stmt->bind_param("i", $student_number);
$student_stmt->execute();
$student = $student_stmt->get_result()->fetch_assoc();

// Get course code using index
$course_stmt = $conn->prepare("SELECT fldcoursecode, fldcoursetitle FROM tblcourse WHERE fldindex = ?");
$course_stmt->bind_param("i", $course_index);
$course_stmt->execute();
$course = $course_stmt->get_result()->fetch_assoc();

if (!$student || !$course) {
  echo "Record not found.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirm Enrollment Deletion</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
      padding: 50px;
      text-align: center;
    }

    .confirm-box {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      display: inline-block;
      padding: 30px 40px;
      text-align: left;
    }

    .confirm-box h2 {
      margin-top: 0;
      color: #c62828;
    }

    .details {
      margin: 20px 0;
    }

    .details p {
      margin: 6px 0;
    }

    .btn {
      padding: 10px 18px;
      border-radius: 5px;
      border: none;
      cursor: pointer;
      font-weight: bold;
      margin-right: 10px;
      text-decoration: none;
      display: inline-block;
    }

    .btn-delete {
      background-color: #c62828;
      color: white;
    }

    .btn-cancel {
      background-color: #888;
      color: white;
    }

    .btn:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>
  <div class="confirm-box">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to remove this student enrollment?</p>

    <div class="details">
      <p><strong>Student Number:</strong> <?= htmlspecialchars($student['fldstudentnumber']) ?></p>
      <p><strong>Name:</strong> <?= htmlspecialchars($student['fldlastname'] . ', ' . $student['fldfirstname']) ?></p>
      <p><strong>Course Code:</strong> <?= htmlspecialchars($course['fldcoursecode']) ?></p>
      <p><strong>Course Title:</strong> <?= htmlspecialchars($course['fldcoursetitle']) ?></p>
    </div>

    <a class="btn btn-delete"
      href="delete-save.php?student=<?= urlencode($student['fldstudentnumber']) ?>&course=<?= urlencode($course['fldcoursecode']) ?>">
      Yes, Delete
    </a>
    <a class="btn btn-cancel" href="javascript:history.back()">Cancel</a>
  </div>
</body>
</html>
