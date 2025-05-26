<?php
require_once("../../include/conn.php");
require_once("../../libs/tcpdf/TCPDF-main/TCPDF-main/tcpdf.php");

if (!isset($_GET['vid'])) {
  die("No course specified.");
}

$course_code = $_GET['vid']; // This is actually the course code string like "Infoman"

// Get course info using course code
$course_stmt = $conn->prepare("SELECT fldindex, fldcoursecode, fldcoursetitle FROM tblcourse WHERE fldcoursecode = ?");
$course_stmt->bind_param("s", $course_code);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
  die("Course not found.");
}

$course = $course_result->fetch_assoc();
$course_title = $course['fldcoursetitle'];
$course_id = $course['fldindex']; // Get the course ID for enrollment lookup

// Get enrolled students - use course ID since tblenrollment.fldcoursecode stores the course INDEX
$student_stmt = $conn->prepare("
  SELECT s.fldstudentnumber, s.fldlastname, s.fldfirstname, s.fldmiddlename, s.fldprogram
  FROM tblenrollment e
  JOIN tblstudent s ON e.fldstudentnumber = s.fldindex
  WHERE e.fldcoursecode = ?
");
$student_stmt->bind_param("i", $course_id); // Use course ID (integer), not course code
$student_stmt->execute();
$student_result = $student_stmt->get_result();

// Generate PDF
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Student_Course_Records_App');
$pdf->SetTitle('Students Enrolled - ' . $course_code);
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 11);

// Header
$html = '<h2 style="text-align:center;">Students Enrolled in Course</h2>';
$html .= '<p><strong>Course:</strong> ' . htmlspecialchars($course_code) . ' - ' . htmlspecialchars($course_title) . '</p>';

// Table Header
$html .= '
<table border="1" cellspacing="0" cellpadding="5">
  <thead>
    <tr style="background-color:#f1f1f1; font-weight:bold;">
      <th width="16.67%">#</th>
      <th width="16.67%">Student Number</th>
      <th width="16.67%">Last Name</th>
      <th width="16.67%">First Name</th> 
      <th width="16.67%">Middle Name</th>
      <th width="16.67%">Program</th>
    </tr>
  </thead>
  <tbody>';

$i = 1;
if ($student_result && $student_result->num_rows > 0) {
  while ($row = $student_result->fetch_assoc()) {
    $html .= '<tr>
      <td>' . $i . '</td>
      <td>' . htmlspecialchars($row['fldstudentnumber']) . '</td>
      <td>' . htmlspecialchars($row['fldlastname']) . '</td>
      <td>' . htmlspecialchars($row['fldfirstname']) . '</td>
      <td>' . htmlspecialchars($row['fldmiddlename']) . '</td>
      <td>' . htmlspecialchars($row['fldprogram']) . '</td>
    </tr>';
    $i++;
  }
} else {
  $html .= '<tr><td colspan="6">No students enrolled in this course.</td></tr>';
}

$html .= '</table>';

// Output PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Students_Enrolled_' . $course_code . '.pdf', 'D');
?>