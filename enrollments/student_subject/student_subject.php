<?php
require("../../include/conn.php");


if (!isset($_GET['vid'])) {
  echo "No student specified.";
  exit;
}

$student_number = $_GET['vid'];

// Get student details
$student_stmt = $conn->prepare("SELECT fldstudentnumber, fldfirstname, fldlastname FROM tblstudent WHERE fldstudentnumber = ?");
$student_stmt->bind_param("i", $student_number);
$student_stmt->execute();
$student_result = $student_stmt->get_result();

if ($student_result->num_rows === 0) {
  echo "Student not found.";
  exit;
}

$student = $student_result->fetch_assoc();

// Get enrolled courses with units
$course_stmt = $conn->prepare("
  SELECT c.fldcoursecode, c.fldcoursetitle, c.fldunits
  FROM tblenrollment e
  JOIN tblcourse c ON e.fldcoursecode = c.fldindex
  JOIN tblstudent s ON e.fldstudentnumber = s.fldindex
  WHERE s.fldstudentnumber = ?
");
$course_stmt->bind_param("i", $student_number);
$course_stmt->execute();
$course_result = $course_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Subjects Enrolled - <?= htmlspecialchars($student['fldstudentnumber']) ?></title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f0f4f8;
    }

    .container {
      display: flex;
      height: 100vh;
    }

    .sidebar {
      background-color: #00703c;
      color: white;
      width: 220px;
      padding: 20px;
      height: 100vh;
    }

    .sidebar h2 {
      font-size: 22px;
      margin-bottom: 20px;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar li {
      margin-bottom: 10px;
    }

    .sidebar a {
      display: block;
      padding: 10px 15px;
      background-color: #006837;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
      transition: background-color 0.3s, transform 0.2s;
    }

    .sidebar a:hover {
      background-color: #00572e;
      transform: scale(1.03);
    }

    .main-content {
      flex: 1;
      padding: 30px;
      background-color: white;
      overflow-y: auto;
    }

    .back-button {
      display: inline-block;
      margin-bottom: 20px;
      background-color: #00703c;
      color: white;
      padding: 8px 16px;
      text-decoration: none;
      border-radius: 4px;
      font-weight: bold;
    }

    .back-button:hover {
      background-color: #00572e;
    }

    h1 {
      margin-top: 0;
      font-size: 24px;
    }

    .student-header {
      font-weight: bold;
      font-size: 20px;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }

    th {
      background-color: #f1f1f1;
    }

    .action-btn {
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
      margin-right: 5px;
      display: inline-block;
      font-size: 14px;
      color: white;
      font-weight: bold;
      transition: background-color 0.2s ease-in-out;
    }

    .action-delete {
      background-color: #c62828;
    }

    .action-delete:hover {
      background-color: #a41e1e;
    }
  </style>
</head>

<body>
  <div class="container">
    <aside class="sidebar">
      <h2>Dashboard</h2>
      <ul>
        <li><a href="../../index.php">Home</a></li>
        <li><a href="../../student/student.php">Student Records</a></li>
        <li><a href="../../course/course.php">Course Records</a></li>
        <li><a href="../../enroll.php">Enroll Student</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <a href="../../student/student.php" class="back-button">← Back to Students</a>
      <h1>Subjects Enrolled</h1>
      <div class="student-header">
        <?= htmlspecialchars($student['fldstudentnumber']) ?> - <?= htmlspecialchars($student['fldlastname'] . ', ' . $student['fldfirstname']) ?>
      </div>

      <?php if ($course_result->num_rows > 0): ?>
        <table>
          <tr>
            <th>Course Code</th>
            <th>Course Title</th>
            <th>Units</th>
            <th>Action</th>
          </tr>
          <?php while ($row = $course_result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['fldcoursecode']) ?></td>
              <td><?= htmlspecialchars($row['fldcoursetitle']) ?></td>
              <td><?= htmlspecialchars($row['fldunits']) ?></td>
              <td>
                <a
                  class="action-btn action-delete"
                  href="delete.php?student=<?= urlencode($student['fldstudentnumber']) ?>&course=<?= urlencode($row['fldcoursecode']) ?>"
                  onclick="return confirm('Are you sure?');">
                  Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>


      <?php else: ?>
        <p>No enrolled courses found for this student.</p>
      <?php endif; ?>
    </main>
  </div>
</body>

</html>