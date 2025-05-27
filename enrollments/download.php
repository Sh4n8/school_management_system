<?php 
require_once('../include/conn.php');
require_once('../libs/tcpdf/TCPDF-main/TCPDF-main/tcpdf.php');

// Get total courses count for status calculation
$total_courses_result = $conn->query("SELECT COUNT(*) as total FROM tblcourse");
$total_courses = $total_courses_result->fetch_assoc()['total'];

// Function to determine status
function getEnrollmentStatus($enrolled_count, $total_courses) {
    if ($enrolled_count == 0) {
        return 'Not Enrolled';
    } elseif ($enrolled_count >= $total_courses) {
        return 'Full Load';
    } else {
        return 'Partial Load';
    }
}

// Create PDF
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Student Enrollment App');
$pdf->SetTitle('Student Enrollment Records');
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 12);

// Header + Table
$html = '
<h2 style="text-align:center;">Student Enrollment Records</h2>
<table border="1" cellspacing="0" cellpadding="5">
    <tr style="background-color:#f1f1f1; font-weight:bold;">
        <th width="8%">#</th>
        <th width="20%">Student Number</th>
        <th width="35%">Student Name</th>
        <th width="17%">Courses Enrolled</th>
        <th width="20%">Status</th>
    </tr>';

$sql = "
    SELECT 
        s.fldindex,
        s.fldstudentnumber,
        s.fldfirstname,
        s.fldlastname,
        COUNT(e.fldcoursecode) as courses_enrolled
    FROM tblstudent s
    LEFT JOIN tblenrollment e ON s.fldindex = e.fldstudentnumber
    GROUP BY s.fldindex, s.fldstudentnumber, s.fldfirstname, s.fldlastname
    ORDER BY s.fldlastname
";

$result = $conn->query($sql);

$i = 1;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = getEnrollmentStatus($row['courses_enrolled'], $total_courses);
        $student_name = htmlspecialchars($row['fldlastname'] . ', ' . $row['fldfirstname']);
        
        $html .= '<tr>
                    <td>' . $i . '</td>
                    <td>' . htmlspecialchars($row['fldstudentnumber']) . '</td>
                    <td>' . $student_name . '</td>
                    <td style="text-align:center;">' . $row['courses_enrolled'] . '</td>
                    <td>' . $status . '</td>
                  </tr>';
        $i++;
    }
} else {
    $html .= '<tr><td colspan="5">No enrollment records found.</td></tr>';
}

$html .= '</table>';

// Output PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Student_Enrollment_Records.pdf', 'D');
?>