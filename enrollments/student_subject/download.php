<?php
require_once('../../include/conn.php');
require_once('../../libs/tcpdf/TCPDF-main/TCPDF-main/tcpdf.php');

if (!isset($_GET['vid'])) {
  die("No student specified.");
}

$student_number = $_GET['vid'];

// Get student details
$student_stmt = $conn->prepare("SELECT fldfirstname, fldlastname FROM tblstudent WHERE fldstudentnumber = ?");
$student_stmt->bind_param("i", $student_number);
$student_stmt->execute();
$student_result = $student_stmt->get_result();

if ($student_result->num_rows === 0) {
  die("Student not found.");
}

$student = $student_result->fetch_assoc();
$fullname = $student['fldlastname'] . ', ' . $student['fldfirstname'];

// Fetch enrolled subjects
$course_stmt = $conn->prepare("
    SELECT c.fldcoursecode, c.fldcoursetitle, c.fldunits
    FROM tblenrollment e
    JOIN tblcourse c ON e.fldcoursecode = c.fldindex
    JOIN tblstudent s ON e.fldstudentnumber = s.fldindex
    WHERE s.fldstudentnumber = ?
");
$course_stmt->bind_param("i", $student_number);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

// Create PDF
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Student_Course_Records_App');
$pdf->SetTitle('Subjects Enrolled - ' . $fullname);
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 12);

// Header
$html = '<h2 style="text-align:center;">Subjects Enrolled</h2>';
$html .= '<p><strong>Student:</strong> ' . htmlspecialchars($fullname) . ' (' . htmlspecialchars($student_number) . ')</p>';

// Table
$html .= '
<table border="1" cellspacing="0" cellpadding="5">
    <tr style="background-color:#f1f1f1; font-weight:bold;">
        <th width="10%">#</th>
        <th width="25%">Course Code</th>
        <th width="45%">Course Title</th>
        <th width="20%">Units</th>
    </tr>';

$i = 1;
if ($course_result && $course_result->num_rows > 0) {
  while ($row = $course_result->fetch_assoc()) {
    $html .= '<tr>
                    <td>' . $i . '</td>
                    <td>' . htmlspecialchars($row['fldcoursecode']) . '</td>
                    <td>' . htmlspecialchars($row['fldcoursetitle']) . '</td>
                    <td>' . htmlspecialchars($row['fldunits']) . '</td>
                  </tr>';
    $i++;
  }
} else {
  $html .= '<tr><td colspan="4">No enrolled subjects found.</td></tr>';
}

$html .= '</table>';

// Output PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Subjects_Enrolled_' . $student_number . '.pdf', 'D');
