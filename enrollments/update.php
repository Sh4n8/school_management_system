<?php require("../include/conn.php");

$old_student = $_GET['student'];
$old_course = $_GET['course'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $new_student = $_POST['student_number'];
  $new_course = $_POST['course_code'];

  $sql = "UPDATE tblenrollment SET fldstudentnumber=?, fldcoursecode=? WHERE fldstudentnumber=? AND fldcoursecode=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssss", $new_student, $new_course, $old_student, $old_course);
  $stmt->execute();

  header("Location: enroll.php");
  exit;
}
?>

<form method="POST">
  <label>Student Number: <input type="text" name="student_number" value="<?= $old_student ?>"></label><br>
  <label>Course Code: <input type="text" name="course_code" value="<?= $old_course ?>"></label><br>
  <button type="submit">Update</button>
</form>