<?php
require("../include/conn.php");

// Get the values from the POST request
$vcoursecode = $_POST['txtcoursecode'];
$vcoursetitle = $_POST['txtcoursetitle'];
$vunits = $_POST['txtunits'];

// Check if course code already exists
$check_sql = "SELECT * FROM tblcourse WHERE fldcoursecode = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $vcoursecode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  echo "<script>alert('Course code already exists. Please use a different one.');</script>";
  echo '<meta http-equiv="refresh" content="0;url=course.php">';
  exit();
}

// Get the next index value for fldindex
$sql = "SELECT MAX(fldindex) AS max_index FROM tblcourse";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$next_index = $row['max_index'] + 1;

// Insert the course record
$insert_sql = "INSERT INTO tblcourse (fldindex, fldcoursecode, fldcoursetitle, fldunits)
               VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("isss", $next_index, $vcoursecode, $vcoursetitle, $vunits);

if ($stmt->execute()) {
  echo "<script>alert('Course record added successfully.');</script>";
} else {
  echo "<script>alert('Error adding record: " . $conn->error . "');</script>";
}

// Redirect back to the course list
echo '<meta http-equiv="refresh" content="0;url=course.php">';
