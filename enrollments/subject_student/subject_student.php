<?php
require("../../include/conn.php");

if (!isset($_GET['vid'])) {
  echo "No course specified.";
  exit;
}

$course_id = $_GET['vid'];

// Get course details using fldindex (correct)
$course_stmt = $conn->prepare("SELECT fldcoursecode, fldcoursetitle FROM tblcourse WHERE fldindex = ?");
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
  echo "Course not found.";
  exit;
}

$course = $course_result->fetch_assoc();

// Get students enrolled in the course using fldcoursecode in tblenrollment
$student_stmt = $conn->prepare("
  SELECT s.fldstudentnumber, s.fldlastname, s.fldfirstname, s.fldmiddlename, s.fldprogram
  FROM tblenrollment e
  JOIN tblstudent s ON e.fldstudentnumber = s.fldindex
  WHERE e.fldcoursecode = ?
");
$student_stmt->bind_param("i", $course_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Students Enrolled in <?= htmlspecialchars($course['fldcoursetitle']) ?></title>
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

    form {
      margin-bottom: 20px;
    }

    form input[type="text"] {
      padding: 6px;
      margin-right: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .form-button {
      background-color: #00703c;
      color: white;
      padding: 6px 12px;
      text-decoration: none;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      display: inline-block;
      font-size: 14px;
      margin-right: 5px;
    }

    .form-button:hover {
      background-color: #00572e;
    }

    .subject-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: bold;
      font-size: 20px;
      margin-bottom: 20px;
    }

    .subject-header p {
      margin: 0;
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
        <li><a href="../../enrollments/enrollment.php">Enroll Student</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <a href="../../course/course.php" class="back-button">← Back to Courses</a>

      <h1>Students Enrolled</h1>
      <div class="subject-header">
      <p>
        <?= htmlspecialchars($course['fldcoursetitle']) ?> (<?= htmlspecialchars($course['fldcoursecode']) ?>)
      </p>
      <a href="download.php?vid=<?= urlencode($course['fldcoursecode']) ?>" class="form-button">Download PDF</a>
  </div>

  <?php if ($student_result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Student Number</th>
          <th>Last Name</th>
          <th>First Name</th>
          <th>Middle Name</th>
          <th>Program</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $student_result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['fldstudentnumber']) ?></td>
            <td><?= htmlspecialchars($row['fldlastname']) ?></td>
            <td><?= htmlspecialchars($row['fldfirstname']) ?></td>
            <td><?= htmlspecialchars($row['fldmiddlename']) ?></td>
            <td><?= htmlspecialchars($row['fldprogram']) ?></td>
            <td>
              <a class="action-btn action-delete"
                href="delete.php?student=<?= urlencode($row['fldstudentnumber']) ?>&course=<?= urlencode($course_id) ?>"
                onclick="return confirm('Are you sure?');">
                Delete
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No students enrolled in this course.</p>
  <?php endif; ?>
  </main>
  </div>
</body>

</html>