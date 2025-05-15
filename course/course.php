<?php
require("../include/conn.php");
session_start();

// Get and then clear any success message stored in session
$successMsg = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Course Records</title>
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

    .action-view {
      background-color: #1976d2;
    }

    .action-view:hover {
      background-color: #125ea3;
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
        <li><a href="course.php">Course Records</a></li>
        <li><a href="../enrollments/enroll.php">Enroll Student</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <h1>Course Records</h1>

      <?php if ($successMsg): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 12px 20px; border-radius: 5px; margin-bottom: 20px;">
          <?= htmlspecialchars($successMsg) ?>
        </div>
      <?php endif; ?>

      <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by course code, title, or units" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit" class="form-button">Search</button>
        <a href="course.php" class="form-button">Show All Records</a>
        <a href="add.php" class="form-button">Add New Course</a>
      </form>

      <table>
        <tr>
          <th>Index</th>
          <th>Course Code</th>
          <th>Course Title</th>
          <th>Units</th>
          <th>Actions</th>
        </tr>

        <?php
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        if (isset($_GET['search']) && $search !== '' && $search !== '0') {
          $sql = "SELECT * FROM tblcourse 
                  WHERE fldcoursecode LIKE ? 
                     OR fldcoursetitle LIKE ? 
                     OR CAST(fldunits AS CHAR) LIKE ? 
                  ORDER BY fldindex ASC";
          $stmt = $conn->prepare($sql);
          $searchTerm = "%$search%";
          $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
          $stmt->execute();
          $result = $stmt->get_result();
        } else if (!isset($_GET['search'])) {
          $sql = "SELECT * FROM tblcourse ORDER BY fldindex ASC";
          $result = $conn->query($sql);
        } else {
          $result = false;
        }

        $i = 1;
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
        ?>
            <tr>
              <td><?= $i ?></td>
              <td><?= htmlspecialchars($row['fldcoursecode']) ?></td>
              <td><?= htmlspecialchars($row['fldcoursetitle']) ?></td>
              <td><?= htmlspecialchars($row['fldunits']) ?></td>
              <td>
                <a class="action-btn action-edit" href="update.php?vid=<?= urlencode($row['fldcoursecode']) ?>">Edits</a>
                <a class="action-btn action-delete" href="delete.php?vid=<?= urlencode($row['fldcoursecode']) ?>">Delete</a>
                <a class="action-btn action-view" href="../enrollments/subject_student/subject_student.php?vid=<?= urlencode($row['fldindex']) ?>">View Students</a>
              </td>
            </tr>
        <?php
            $i++;
          }
        } else {
          echo "<tr><td colspan='5'>No records found.</td></tr>";
        }
        ?>
      </table>
    </main>
  </div>
</body>

</html>