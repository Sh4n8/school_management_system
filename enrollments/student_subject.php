<?php
require("../include/conn.php");

if (!isset($_GET['vid']) || empty($_GET['vid'])) {
  echo "Invalid request.";
  exit;
}

$studentNumber = $_GET['vid'];

// Get student details
$stmt = $conn->prepare("SELECT fldstudentnumber, fldlastname, fldfirstname FROM tblstudent WHERE fldstudentnumber = ?");
$stmt->bind_param("s", $studentNumber);
$stmt->execute();
$studentResult = $stmt->get_result();

if ($studentResult->num_rows === 0) {
  echo "Student not found.";
  exit;
}

$student = $studentResult->fetch_assoc();
$fullname = "{$student['fldlastname']}, {$student['fldfirstname']}";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Subjects Enrolled by Student</title>
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

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table,
    th,
    td {
      border: 1px solid #ccc;
    }

    th,
    td {
      padding: 10px;
      text-align: left;
    }

    th {
      background-color: #f1f1f1;
    }

    .action-btn {
      background-color: #00693e;
      color: white;
      padding: 8px 16px;
      border-radius: 4px;
      text-decoration: none;
      display: inline-block;
      margin-bottom: 20px;
      font-weight: bold;
    }

    .action-btn:hover {
      background-color: #00572e;
    }

    .total-units {
      font-weight: bold;
      margin-top: 15px;
    }
  </style>
</head>

<body>
  <div class="container">
    <aside class="sidebar">
      <h2>Dashboard</h2>
      <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="../student/student.php">Student Records</a></li>
        <li><a href="../course/course.php">Course Records</a></li>
        <li><a href="enroll.php">Enroll Student</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <a class="action-btn" href="enroll.php">← Back to Enrollment</a>
      <h1>Subjects Enrolled by Student</h1>

      <h2><?php echo htmlspecialchars($student['fldstudentnumber']) . " - " . htmlspecialchars($fullname); ?></h2>

      <?php
      // Get enrolled subjects
      $stmt2 = $conn->prepare("
        SELECT c.fldcoursecode, c.fldcoursetitle, c.fldunits
        FROM tblenrollment e
        JOIN tblcourse c ON c.fldindex = e.fldcoursecode
        WHERE e.fldstudentnumber = ?
      ");
      $stmt2->bind_param("s", $studentNumber);
      $stmt2->execute();
      $subjectsResult = $stmt2->get_result();

      $totalUnits = 0;

      if ($subjectsResult->num_rows > 0) {
        echo "<table>
                <tr>
                  <th>Course Code</th>
                  <th>Course Title</th>
                  <th>Units</th>
                </tr>";
        while ($subject = $subjectsResult->fetch_assoc()) {
          $totalUnits += (int)$subject['fldunits'];
          echo "<tr>
                  <td>" . htmlspecialchars($subject['fldcoursecode']) . "</td>
                  <td>" . htmlspecialchars($subject['fldcoursetitle']) . "</td>
                  <td>" . htmlspecialchars($subject['fldunits']) . "</td>
                </tr>";
        }
        echo "</table>";
        echo "<p class='total-units'>Total Units: " . $totalUnits . "</p>";
      } else {
        echo "<p>No subjects enrolled.</p>";
      }
      ?>
    </main>
  </div>
</body>

</html>