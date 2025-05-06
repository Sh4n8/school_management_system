<?php
require("../include/conn.php");

$vnum = $_GET['vid'] ?? '';

if (!$vnum) {
  echo "No student selected.";
  exit;
}

// Fetch student details
$sql = "SELECT * FROM tblstudent WHERE fldstudentnumber = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $vnum);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
  echo "Student not found.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Confirm Student Deletion</title>
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
    <p>Are you sure you want to delete this student?</p>

    <div class="details">
      <p><strong>Student Number:</strong> <?= htmlspecialchars($student['fldstudentnumber']) ?></p>
      <p><strong>Last Name:</strong> <?= htmlspecialchars($student['fldlastname']) ?></p>
      <p><strong>First Name:</strong> <?= htmlspecialchars($student['fldfirstname']) ?></p>
      <p><strong>Program:</strong> <?= htmlspecialchars($student['fldprogram']) ?></p>
    </div>

    <a class="btn btn-delete" href="delete-save.php?vid=<?= urlencode($student['fldstudentnumber']) ?>">Yes, Delete</a>
    <a class="btn btn-cancel" href="student.php">Cancel</a>
  </div>
</body>

</html>