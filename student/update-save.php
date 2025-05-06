<?php
require("../include/conn.php");

$vstudentnumber = $_POST['txtstudentnumber'];
$vlastname = $_POST['txtlastname'];
$vfirstname = $_POST['txtfirstname'];
$vmiddlename = $_POST['txtmiddlename'];

$sql = "UPDATE tblstudent SET fldlastname='$vlastname', fldfirstname='$vfirstname', fldmiddlename='$vmiddlename' WHERE fldstudentnumber='$vstudentnumber'";
if ($conn->query($sql) === TRUE) {
    echo "<script>
     alert('Student Updated.');
     window.location.href = 'student.php';
     </script>";
} else {
    echo "<script>
     alert('Update failed.');
     window.history.back();
     </script>";
}
