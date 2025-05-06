<?php
require("../include/conn.php");

$vcoursecode = $_POST['txtcoursecode'];
$vcoursetitle = $_POST['txtcoursetitle'];
$vunits = $_POST['txtunits'];

$sql = "UPDATE tblcourse SET fldcoursetitle='$vcoursetitle', fldunits='$vunits' WHERE fldcoursecode='$vcoursecode'";
if ($conn->query($sql) === TRUE) {
  echo "<script>
    alert('Course Updated.');
    window.location.href = 'course.php';
  </script>";
} else {
  echo "<script>
    alert('Update failed.');
    window.history.back();
  </script>";
}
