<?php require("../include/conn.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Subject's Students</title>
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

    th,
    td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }

    th {
      background-color: #f1f1f1;
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
      <h1>Students Enrolled in a Subject</h1>

      <form method="GET" action="">
        <label for="subject">Select Subject:</label>
        <select name="course_id" id="subject" onchange="this.form.submit()" required>
          <option value="">-- Choose a Subject --</option>
          <?php
          $courses = $conn->query("SELECT fldindex, fldcoursecode, fldcoursetitle FROM tblcourse ORDER BY fldcoursetitle");
          while ($c = $courses->fetch_assoc()) {
            $selected = (isset($_GET['course_id']) && $_GET['course_id'] == $c['fldindex']) ? "selected" : "";
            echo "<option value='{$c['fldindex']}' $selected>{$c['fldcoursecode']} - {$c['fldcoursetitle']}</option>";
          }
          ?>
        </select>
      </form>

      <?php
      if (isset($_GET['course_id']) && $_GET['course_id'] !== '') {
        $course_id = $_GET['course_id'];

        $courseInfo = $conn->query("SELECT fldcoursecode, fldcoursetitle FROM tblcourse WHERE fldindex = $course_id")->fetch_assoc();
        echo "<h2>{$courseInfo['fldcoursecode']} - {$courseInfo['fldcoursetitle']}</h2>";

        $result = $conn->query("
          SELECT s.fldstudentnumber, s.fldlastname, s.fldfirstname
          FROM tblenrollment e
          JOIN tblstudent s ON e.fldstudentnumber = s.fldindex
          WHERE e.fldcoursecode = $course_id
        ");

        if ($result->num_rows > 0) {
          echo "<table>
                  <tr>
                    <th>Student Number</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                  </tr>";
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['fldstudentnumber']}</td>
                    <td>{$row['fldlastname']}</td>
                    <td>{$row['fldfirstname']}</td>
                  </tr>";
          }
          echo "</table>";
        } else {
          echo "<p>No students are enrolled in this subject.</p>";
        }
      }
      ?>
    </main>
  </div>
</body>

</html>