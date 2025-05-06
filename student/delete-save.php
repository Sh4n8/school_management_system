<?php
require("../include/conn.php");
session_start();

if (isset($_GET['vid'])) {
    $studentNumber = $_GET['vid'];

    // First, fetch student's name before deletion
    $stmt = $conn->prepare("SELECT fldlastname, fldfirstname FROM tblstudent WHERE fldstudentnumber = ?");
    $stmt->bind_param("s", $studentNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    // If student found, proceed to delete
    if ($student) {
        $fullName = $student['fldlastname'] . ", " . $student['fldfirstname'];

        // Now delete the student
        $stmt = $conn->prepare("DELETE FROM tblstudent WHERE fldstudentnumber = ?");
        $stmt->bind_param("s", $studentNumber);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Student '$fullName' has been deleted successfully.";
        }
    }

    // Redirect back to student list
    header("Location: student.php");
    exit();
}
