<?php
require("../include/conn.php");

if (!isset($_GET['vid']) || !isset($_GET['cid'])) {
  echo "Invalid request.";
  exit;
}

$student_id = $_GET['vid'];
$course_id = $_GET['cid'];

// Fetch student and course info
$stmt = $conn->prepare("
  SELECT 
    s.fldstudentnumber, s.fldfirstname, s.fldlastname,
    c.fldcoursecode, c.fldcoursetitle
  FROM tblenrollment e
  JOIN tblstudent s ON e.fldstudentnumber = s.fldindex
  JOIN tblcourse c ON e.fldcoursecode = c.fldindex
  WHERE e.fldstudentnumber = ? AND e.fldcoursecode = ?
");
$stmt->bind_param("ii", $student_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "Enrollment not found.";
  exit;
}

$data = $result->fetch_assoc();

// If confirmed, delete the enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
  $delete = $conn->prepare("DELETE FROM tblenrollment WHERE fldstudentnumber = ? AND fldcoursecode = ?");
  $delete->bind_param("ii", $student_id, $course_id);

  if ($delete->execute()) {
    header("Location: enroll.php?msg=deleted");
    exit;
  } else {
    echo "Failed to delete enrollment: " . $delete->error;
  }
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
      background-color: #f0f4f8;
      padding: 60px;
      text-align: center;
    }

    .confirm-box {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      display: inline-block;
      padding: 40px 50px;
      text-align: left;
      max-width: 600px;
    }

    .confirm-box h2 {
      margin-top: 0;
      color: #c62828;
    }

    .details {
      margin: 20px 0;
      font-size: 16px;
    }

    .details p {
      margin: 6px 0;
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

    .btn-delete {
      background-color: #c62828;
      color: white;
    }

    .btn-delete:hover {
      background-color: #a41e1e;
    }

    .btn-cancel {
      background-color: #00703c;
      color: white;
    }

    .btn-cancel:hover {
      background-color: #00572e;
    }
  </style>
</head>

<body>
  <div class="confirm-box">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to delete this enrollment?</p>

    <div class="details">
      <p><strong>Student Number:</strong> <?= htmlspecialchars($data['fldstudentnumber']) ?></p>
      <p><strong>Student Name:</strong> <?= htmlspecialchars($data['fldlastname'] . ', ' . $data['fldfirstname']) ?></p>
      <p><strong>Course Code:</strong> <?= htmlspecialchars($data['fldcoursecode']) ?></p>
      <p><strong>Course Title:</strong> <?= htmlspecialchars($data['fldcoursetitle']) ?></p>
    </div>

    <form method="POST">
      <button type="submit" name="confirm_delete" class="btn btn-delete">Yes, Delete</button>
      <a href="enroll.php" class="btn btn-cancel">Cancel</a>
    </form>
  </div>
</body>

</html>