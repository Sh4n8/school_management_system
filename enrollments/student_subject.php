<?php require("../include/conn.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Student's Subjects</title>
  <style>
    /* Same styling as your existing dashboard */
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

    table {
      width: 100%;
      border-collapse: collapse;
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
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
      margin-right: 5px;
      display: inline-block;
      font-size: 14px;
    }

    .action-btn:hover {
      background-color: #00572e;
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
        <li><a href="../enrollments/student_subject.php">Student's Subjects</a></li>
        <li><a href="../enrollments/subject_student.php">Subject's Students</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <h1>Subjects Enrolled by Student</h1>

      <form method="GET" action="">
        <label>Select Student:</label>
        <select name="student_id" onchange="this.form.submit()" required>
          <option value="">-- Choose a Student --</option>
          <?php
          $students = $conn->query("SELECT fldindex, fldstudentnumber, fldlastname, fldfirstname FROM tblstudent ORDER BY fldlastname");
          while ($s = $students->fetch_assoc()) {
            $selected = (isset($_GET['student_id']) && $_GET['student_id'] == $s['fldindex']) ? "selected" : "";
            echo "<option value='{$s['fldindex']}' $selected>{$s['fldstudentnumber']} - {$s['fldlastname']}, {$s['fldfirstname']}</option>";
          }
          ?>
        </select>
      </form>

      <?php
      if (isset($_GET['student_id']) && $_GET['student_id'] !== '') {
        $student_id = $_GET['student_id'];

        $studentInfo = $conn->query("SELECT fldstudentnumber, fldfirstname, fldlastname FROM tblstudent WHERE fldindex = $student_id")->fetch_assoc();
        echo "<h2>{$studentInfo['fldstudentnumber']} - {$studentInfo['fldlastname']}, {$studentInfo['fldfirstname']}</h2>";

        $result = $conn->query("
          SELECT c.fldcoursecode, c.fldcoursetitle, c.fldunits
          FROM tblenrollment e
          JOIN tblcourse c ON e.fldcoursecode = c.fldindex
          WHERE e.fldstudentnumber = $student_id
        ");

        if ($result->num_rows > 0) {
          echo "<table>
                  <tr>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Units</th>
                  </tr>";
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['fldcoursecode']}</td>
                    <td>{$row['fldcoursetitle']}</td>
                    <td>{$row['fldunits']}</td>
                  </tr>";
          }
          echo "</table>";
        } else {
          echo "<p>No courses enrolled yet.</p>";
        }
      }
      ?>
    </main>
  </div>
</body>

</html>