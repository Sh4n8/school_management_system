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
        <li><a href="enrollment.php">Enroll Student</a></li>
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



      <!-- Search and Controls Section -->
      <div class="controls-section">
        <h2>Student Enrollment Overview</h2>
        <div class="search-controls">
          <input type="text" id="searchInput" placeholder="Search by student name or number..." />
          <button onclick="searchStudents()">Search</button>
          <button onclick="location.href='add.php'">Add Enrollment</button>
          <button onclick="location.href='download.php'">Download</button>
        </div>
      </div>

      <?php
      // Get total courses count for status calculation
      $total_courses_result = $conn->query("SELECT COUNT(*) as total FROM tblcourse");
      $total_courses = $total_courses_result->fetch_assoc()['total'];

      // Function to determine status
      function getEnrollmentStatus($enrolled_count, $total_courses)
      {
        if ($enrolled_count == 0) {
          return ['status' => 'Not Enrolled', 'class' => 'status-not-enrolled'];
        } elseif ($enrolled_count >= $total_courses) {
          return ['status' => 'Full Load', 'class' => 'status-full-load'];
        } else {
          return ['status' => 'Partial Load', 'class' => 'status-partial-load'];
        }
      }

      $result = $conn->query("
        SELECT 
          s.fldindex,
          s.fldstudentnumber,
          s.fldfirstname,
          s.fldlastname,
          COUNT(e.fldcoursecode) as courses_enrolled
        FROM tblstudent s
        LEFT JOIN tblenrollment e ON s.fldindex = e.fldstudentnumber
        GROUP BY s.fldindex, s.fldstudentnumber, s.fldfirstname, s.fldlastname
        ORDER BY s.fldlastname
      ");

      if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' id='enrollmentTable'>
              <tr>
                <th>ID</th>
                <th>Student Number</th>
                <th>Student Name</th>
                <th>Courses Enrolled</th>
                <th>Status</th>
                <th>Action</th>
              </tr>";

        $id_counter = 1;
        while ($row = $result->fetch_assoc()) {
          $status_info = getEnrollmentStatus($row['courses_enrolled'], $total_courses);

          echo "<tr class='student-row' data-student-name='{$row['fldlastname']}, {$row['fldfirstname']}' data-student-number='{$row['fldstudentnumber']}'>
                <td>{$id_counter}</td>
                <td>{$row['fldstudentnumber']}</td>
                <td>{$row['fldlastname']}, {$row['fldfirstname']}</td>
                <td><span class='course-count'>{$row['courses_enrolled']}</span></td>
                <td><span class='status-badge {$status_info['class']}'>{$status_info['status']}</span></td>
                <td>
                  <a class='action-btn action-view' href='view.php?id={$row['fldindex']}'>View</a>
                </td>
              </tr>";
          $id_counter++;
        }
        echo "</table>";
      } else {
        echo "<p>No students found.</p>";
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

    // Search functionality
    function searchStudents() {
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      const rows = document.querySelectorAll('.student-row');

      rows.forEach(row => {
        const studentName = row.getAttribute('data-student-name').toLowerCase();
        const studentNumber = row.getAttribute('data-student-number').toLowerCase();

        if (studentName.includes(searchTerm) || studentNumber.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    // Clear search when input is empty
    document.getElementById('searchInput').addEventListener('input', function() {
      if (this.value === '') {
        const rows = document.querySelectorAll('.student-row');
        rows.forEach(row => {
          row.style.display = '';
        });
      }
    });

    function downloadData() {
      // Create CSV content
      let csvContent = "ID,Student Number,Student Name,Courses Enrolled,Status\n";

      const rows = document.querySelectorAll('#enrollmentTable tr');
      for (let i = 1; i < rows.length; i++) { // Skip header row
        const cells = rows[i].querySelectorAll('td');
        if (cells.length > 0 && rows[i].style.display !== 'none') {
          const rowData = [
            cells[0].textContent, // ID
            cells[1].textContent, // Student Number
            cells[2].textContent, // Student Name
            cells[3].textContent, // Courses Enrolled
            cells[4].textContent.replace(/\s+/g, ' ').trim() // Status (clean up whitespace)
          ];
          csvContent += rowData.map(field => `"${field}"`).join(',') + '\n';
        }
      }

      // Create and download file
      const blob = new Blob([csvContent], {
        type: 'text/csv'
      });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.setAttribute('hidden', '');
      a.setAttribute('href', url);
      a.setAttribute('download', 'enrollment_data.csv');
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    }
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

    h1 {
      margin-top: 0;
    }

    /* Controls section */
    .controls-section {
      margin: 30px 0;
      padding: 20px;
      background-color: #f8f9fa;
      border-radius: 8px;
    }

    .search-controls {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
      margin-top: 15px;
    }

    #searchInput {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
      width: 300px;
      flex-shrink: 0;
    }

    .search-controls button {
      background-color: #00703c;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s;
      white-space: nowrap;
    }

    .search-controls button:hover {
      background-color: #00572e;
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

    /* Status badges */
    .status-badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .status-not-enrolled {
      background-color: #ffebee;
      color: #c62828;
    }

    .status-partial-load {
      background-color: #fff3e0;
      color: #ef6c00;
    }

    .status-full-load {
      background-color: #e8f5e8;
      color: #2e7d32;
    }

    .course-count {
      font-weight: bold;
      color: #00703c;
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

    .action-view {
      background-color: #00703c;
    }

    .action-view:hover {
      background-color: #00572e;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: white;
      margin: 10% auto;
      padding: 0;
      border-radius: 8px;
      width: 500px;
      max-width: 90%;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
      background-color: #00703c;
      color: white;
      padding: 20px;
      border-radius: 8px 8px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h3 {
      margin: 0;
      font-size: 18px;
    }

    .close {
      color: white;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      line-height: 1;
    }

    .close:hover {
      opacity: 0.7;
    }

    .modal form {
      padding: 30px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #333;
    }

    .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
      background-color: white;
    }

    .form-group select:focus {
      outline: none;
      border-color: #00703c;
      box-shadow: 0 0 5px rgba(0, 112, 60, 0.3);
    }

    .form-buttons {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 30px;
    }

    .btn-enroll {
      background-color: #00703c;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      font-size: 14px;
    }

    .btn-enroll:hover {
      background-color: #00572e;
    }

    .btn-cancel {
      background-color: #6c757d;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      font-size: 14px;
    }

    .btn-cancel:hover {
      background-color: #5a6268;
    }
  </style>
</body>

</html>