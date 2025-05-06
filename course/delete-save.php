<?php
require("../include/conn.php");
session_start();

if (isset($_GET['vid'])) {
  $courseCode = $_GET['vid'];

  // Fetch course info before deleting
  $fetchSql = "SELECT * FROM tblcourse WHERE fldcoursecode = ?";
  $fetchStmt = $conn->prepare($fetchSql);
  $fetchStmt->bind_param("s", $courseCode);
  $fetchStmt->execute();
  $result = $fetchStmt->get_result();
  $course = $result->fetch_assoc();

  if (!$course) {
    echo "Course not found.";
    exit;
  }

  // Delete the course
  $deleteSql = "DELETE FROM tblcourse WHERE fldcoursecode = ?";
  $deleteStmt = $conn->prepare($deleteSql);
  $deleteStmt->bind_param("s", $courseCode);

  if ($deleteStmt->execute()) {
    // Set success message in session
    $_SESSION['success_message'] = "Course '" . $course['fldcoursetitle'] . "' has been deleted successfully.";
    header("Location: course.php");
    exit;
  } else {
    echo "Failed to delete course.";
  }
} else {
  echo "Invalid request.";
}
