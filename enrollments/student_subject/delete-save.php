<?php
require("../../include/conn.php");

$student_number = $_GET['student'] ?? '';
$course_code = $_GET['course'] ?? '';

if (!$student_number || !$course_code) {
  echo "Invalid deletion request.";
  exit;
}

// Delete the record
$stmt = $conn->prepare("
  DELETE e FROM tblenrollment e
  JOIN tblstudent s ON e.fldstudentnumber = s.fldindex
  JOIN tblcourse c ON e.fldcoursecode = c.fldindex
  WHERE s.fldstudentnumber = ? AND c.fldcoursecode = ?
");

$stmt->bind_param("is", $student_number, $course_code);

if ($stmt->execute()) {
  header("Location: student_subject.php?vid=" . urlencode($student_number));
  exit;
} else {
  echo "Failed to delete enrollment.";
}
