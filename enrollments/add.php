<?php require("../include/conn.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Add Enrollment</title>
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
        <li><a href="enrollment.php">Enroll Student</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1>Add Enrollment</h1>
        <button onclick="goBack()" class="back-btn">← Back</button>
      </div>

      <?php
      $success_message = '';
      $error_message = '';

      if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['enroll'])) {
        $student_id = $_POST['student_id'];
        $course_code = $_POST['course_code'];

        $check = $conn->prepare("SELECT * FROM tblenrollment WHERE fldstudentnumber = ? AND fldcoursecode = ?");
        $check->bind_param("ii", $student_id, $course_code);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
          $error_message = "Student is already enrolled in this course.";
        } else {
          $stmt = $conn->prepare("INSERT INTO tblenrollment (fldstudentnumber, fldcoursecode) VALUES (?, ?)");
          $stmt->bind_param("ii", $student_id, $course_code);

          if ($stmt->execute()) {
            $success_message = "Enrollment saved successfully!";
          } else {
            $error_message = "Error: " . $stmt->error;
          }
        }
      }

      // Handle AJAX request for courses
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

      <!-- Success/Error Messages -->
      <?php if ($success_message): ?>
        <div class="notification success">
          <span class="notification-icon">✓</span>
          <?php echo $success_message; ?>
        </div>
      <?php endif; ?>

      <?php if ($error_message): ?>
        <div class="notification error">
          <span class="notification-icon">✕</span>
          <?php echo $error_message; ?>
        </div>
      <?php endif; ?>

      <!-- Enrollment Form -->
      <div class="form-container">
        <form method="POST" action="" id="enrollmentForm">
          <div class="form-group">
            <label>Student:</label>
            <select name="student_id" id="studentSelect" required>
              <option value="">-- Select Student --</option>
              <?php
              $students = $conn->query("SELECT fldindex, fldstudentnumber, fldfirstname, fldlastname FROM tblstudent ORDER BY fldlastname");
              while ($s = $students->fetch_assoc()) {
                echo "<option value='{$s['fldindex']}'>{$s['fldstudentnumber']} - {$s['fldlastname']}, {$s['fldfirstname']}</option>";
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Course:</label>
            <select name="course_code" id="courseSelect" required>
              <option value="">-- Select Course --</option>
            </select>
          </div>

          <div class="form-buttons">
            <button type="submit" name="enroll" class="btn-enroll">Enroll</button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
    $(document).ready(function() {
      $('#studentSelect').on('change', function() {
        const studentId = $(this).val();
        $('#courseSelect').html('<option>Loading...</option>');

        $.post('add.php', {
          student_id: studentId,
          get_courses: true
        }, function(data) {
          $('#courseSelect').html(data);
        });
      });
    });

    function goBack() {
      window.location.href = 'enrollment.php';
    }

    // Auto-hide success message after 3 seconds
    setTimeout(function() {
      const successNotif = document.querySelector('.notification.success');
      if (successNotif) {
        successNotif.style.opacity = '0';
        setTimeout(function() {
          successNotif.style.display = 'none';
        }, 500);
      }
    }, 3000);
  </script>

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

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .page-header h1 {
      margin: 0;
      color: #333;
    }

    .back-btn {
      background-color: #6c757d;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s;
    }

    .back-btn:hover {
      background-color: #5a6268;
    }

    /* Notification Styles */
    .notification {
      padding: 15px 20px;
      border-radius: 6px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      font-weight: bold;
      transition: opacity 0.5s ease;
    }

    .notification-icon {
      margin-right: 10px;
      font-size: 18px;
    }

    .notification.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .notification.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f1aeb5;
    }

    /* Form Container */
    .form-container {
      background-color: #f8f9fa;
      padding: 40px;
      border-radius: 8px;
      max-width: 600px;
      margin: 0 auto;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #333;
      font-size: 16px;
    }

    .form-group select {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
      background-color: white;
      box-sizing: border-box;
    }

    .form-group select:focus {
      outline: none;
      border-color: #00703c;
      box-shadow: 0 0 5px rgba(0, 112, 60, 0.3);
    }

    .form-buttons {
      text-align: center;
      margin-top: 30px;
    }

    .btn-enroll {
      background-color: #00703c;
      color: white;
      padding: 15px 40px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      font-size: 16px;
      transition: background-color 0.3s;
    }

    .btn-enroll:hover {
      background-color: #00572e;
    }

    /* Responsive design */
    @media (max-width: 768px) {
      .page-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
      }

      .form-container {
        padding: 20px;
        margin: 0 10px;
      }
    }
  </style>
</body>

</html>