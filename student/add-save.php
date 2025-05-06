<?php
require("../include/conn.php");

// Get values from the POST request
$vstudentnumber = $_POST['txtstudentnumber'];
$vlastname = $_POST['txtlastname'];
$vfirstname = $_POST['txtfirstname'];
$vmiddlename = $_POST['txtmiddlename'];
$vprogram = $_POST['txtprogram'];

// Prepare statement to check if student number already exists
$stmt = $conn->prepare("SELECT fldstudentnumber FROM tblstudent WHERE fldstudentnumber = ?");
$stmt->bind_param("s", $vstudentnumber);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  echo "<script>alert('Student number already exists.'); window.history.back();</script>";
} else {
  // Prepare insert statement
  $insert = $conn->prepare("INSERT INTO tblstudent (fldstudentnumber, fldlastname, fldfirstname, fldmiddlename, fldprogram) VALUES (?, ?, ?, ?, ?)");
  $insert->bind_param("sssss", $vstudentnumber, $vlastname, $vfirstname, $vmiddlename, $vprogram);

  if ($insert->execute()) {
    header("Location: student.php?status=added");
    exit;
  } else {
    echo "<script>alert('Error adding record: " . $conn->error . "'); window.location.href='student.php';</script>";
  }
  $insert->close();
}

$stmt->close();
$conn->close();
