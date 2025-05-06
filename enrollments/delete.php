<?php require("../include/conn.php");

$student = $_GET['student'];
$course = $_GET['course'];

$sql = "DELETE FROM tblenrollment WHERE fldstudentnumber=? AND fldcoursecode=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $student, $course);
$stmt->execute();

header("Location: enroll.php");
exit;
