<?php
require_once('../include/conn.php');
require_once('../libs/tcpdf/TCPDF-main/TCPDF-main/tcpdf.php');

// Create PDF
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Course Records App');
$pdf->SetTitle('Course Records');
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 12);

// Header + Table
$html = '
<h2 style="text-align:center;">Course Records</h2>
<table border="1" cellspacing="0" cellpadding="5">
    <tr style="background-color:#f1f1f1; font-weight:bold;">
        <th width="10%">#</th>
        <th width="25%">Course Code</th>
        <th width="45%">Course Title</th>
        <th width="20%">Units</th>
    </tr>';

$sql = "SELECT * FROM tblcourse ORDER BY fldindex ASC";
$result = $conn->query($sql);

$i = 1;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>
                    <td>' . $i . '</td>
                    <td>' . htmlspecialchars($row['fldcoursecode']) . '</td>
                    <td>' . htmlspecialchars($row['fldcoursetitle']) . '</td>
                    <td>' . htmlspecialchars($row['fldunits']) . '</td>
                  </tr>';
        $i++;
    }
} else {
    $html .= '<tr><td colspan="4">No course records found.</td></tr>';
}

$html .= '</table>';

// Output PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Course_Records.pdf', 'D');
