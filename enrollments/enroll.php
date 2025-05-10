<?php require("../include/conn.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Enroll Student</title>
  <link rel="stylesheet" href="../assets/style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <li><a href="student_subject.php">Student's Subjects</a></li>
        <li><a href="subject_student.php">Subject's Students</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <h1>Enroll Student to a Course</h1>

      <?php
      if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['enroll'])) {
        $student_id = $_POST['student_id'];
        $course_code = $_POST['course_code'];

        $check = $conn->prepare("SELECT * FROM tblenrollment WHERE fldstudentnumber = ? AND fldcoursecode = ?");
        $check->bind_param("ii", $student_id, $course_code);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
          echo "<p class='message-error'>Student is already enrolled in this course.</p>";
        } else {
          $stmt = $conn->prepare("INSERT INTO tblenrollment (fldstudentnumber, fldcoursecode) VALUES (?, ?)");
          $stmt->bind_param("ii", $student_id, $course_code);

          if ($stmt->execute()) {
            echo "<p class='message-success'>Enrollment successful.</p>";
          } else {
            echo "<p class='message-error'>Error: " . $stmt->error . "</p>";
          }
        }
      }

      // Handle AJAX-like request inside same file
      if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['get_courses'])) {
        $student_id = $_POST['student_id'];
        $sql = "
        SELECT fldindex, fldcoursecode, fldcoursetitle
        FROM tblcourse
        WHERE fldindex NOT IN (
          SELECT fldcoursecode FROM tblenrollment WHERE fldstudentnumber = ?
        )
        ORDER BY fldcoursetitle
      ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $options = "<option value=''>-- Select Course --</option>";
        while ($row = $result->fetch_assoc()) {
          $options .= "<option value='{$row['fldindex']}'>{$row['fldcoursecode']} - {$row['fldcoursetitle']}</option>";
        }
        echo $options;
        exit;
      }
      ?>

      <form method="POST" action="">
        <!-- Student Dropdown -->
        <label>Student:
          <select name="student_id" id="studentSelect" required>
            <option value="">-- Select Student --</option>
            <?php
            $students = $conn->query("SELECT fldindex, fldstudentnumber, fldfirstname, fldlastname FROM tblstudent ORDER BY fldlastname");
            while ($s = $students->fetch_assoc()) {
              echo "<option value='{$s['fldindex']}'>{$s['fldstudentnumber']} - {$s['fldlastname']}, {$s['fldfirstname']}</option>";
            }
            ?>
          </select>
        </label><br><br>

        <!-- Course Dropdown -->
        <label>Course:
          <select name="course_code" id="courseSelect" required>
            <option value="">-- Select Course --</option>
          </select>
        </label><br><br>

        <button type="submit" name="enroll">Enroll</button>
      </form>

      <h2>Enrolled Students</h2>
      <?php
      $result = $conn->query("
      SELECT 
        e.fldstudentnumber,
        e.fldcoursecode,
        s.fldstudentnumber AS student_num,
        s.fldfirstname,
        s.fldlastname,
        c.fldcoursecode AS course_code,
        c.fldcoursetitle
      FROM tblenrollment e
      JOIN tblstudent s ON e.fldstudentnumber = s.fldindex
      JOIN tblcourse c ON e.fldcoursecode = c.fldindex
      ORDER BY s.fldlastname
    ");

      if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10'>
              <tr>
                <th>Student Number</th>
                <th>Student Name</th>
                <th>Course Code</th>
                <th>Course Description</th>
                <th>Action</th>
              </tr>";
        while ($row = $result->fetch_assoc()) {
          echo "<tr>
                <td>{$row['student_num']}</td>
                <td>{$row['fldlastname']}, {$row['fldfirstname']}</td>
                <td>{$row['course_code']}</td>
                <td>{$row['fldcoursetitle']}</td>
                <td>
                  <a class='action-btn action-edit' href='update.php?vid={$row['fldstudentnumber']}&cid={$row['fldcoursecode']}'>Edit</a>
                  <a class='action-btn action-delete' href='delete.php?vid={$row['fldstudentnumber']}&cid={$row['fldcoursecode']}' onclick=\"return confirm('Are you sure?');\">Delete</a>
                </td>
              </tr>";
        }
        echo "</table>";
      } else {
        echo "<p>No enrollments yet.</p>";
      }
      ?>
    </main>
  </div>

  <script>
    $(document).ready(function() {
      $('#studentSelect').on('change', function() {
        const studentId = $(this).val();
        $('#courseSelect').html('<option>Loading...</option>');

        $.post('enroll.php', {
          student_id: studentId,
          get_courses: true
        }, function(data) {
          $('#courseSelect').html(data);
        });
      });
    });
  </script>
</body>

</html>



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

  h1 {
    margin-top: 0;
  }

  form {
    margin-bottom: 20px;
  }

  form input[type="text"],
  form select {
    padding: 6px;
    margin-right: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }

  form button,
  form a {
    background-color: #00703c;
    color: white;
    padding: 6px 12px;
    text-decoration: none;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
  }

  form button:hover,
  form a:hover {
    background-color: #00572e;
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

  .message-success {
    color: green;
    font-weight: bold;
  }

  .message-error {
    color: red;
    font-weight: bold;
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

  .action-edit {
    background-color: #00703c;
  }

  .action-edit:hover {
    background-color: #00572e;
  }

  .action-delete {
    background-color: #c62828;
  }

  .action-delete:hover {
    background-color: #a41e1e;
  }
</style>