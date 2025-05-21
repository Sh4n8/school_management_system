<?php
require_once('../include/conn.php');
require_once('../libs/tcpdf/TCPDF-main/TCPDF-main/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Student Management System');
$pdf->SetTitle('Student Records');
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 12);

// Start building the HTML content
$html = '
<h2 style="text-align:center;">Student Records</h2>
<table border="1" cellspacing="0" cellpadding="5">
    <tr style="background-color:#f1f1f1; font-weight:bold;">
        <th width="5%">#</th>
        <th width="20%">Student Number</th>
        <th width="20%">Last Name</th>
        <th width="20%">First Name</th>
        <th width="15%">Middle Name</th>
        <th width="20%">Program</th>
    </tr>';

// Query the student records
$sql = "SELECT * FROM tblstudent ORDER BY fldindex ASC";
$result = $conn->query($sql);

$i = 1;
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
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
  $html .= '<tr><td colspan="6">No student records found.</td></tr>';
}

$html .= '</table>';

// Output the PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Student_Records.pdf', 'D');
