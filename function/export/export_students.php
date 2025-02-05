<?php
include '../..//lib/config.php';
require_once('../../lib/fpdf/fpdf.php'); // Sesuaikan dengan path ke direktori FPDF Anda

// Mendapatkan parameter format dari URL
$format = isset($_GET['format']) ? $_GET['format'] : 'xls';

// Menyiapkan SQL query
$stmt = $pdo->prepare("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format == 'xls') {
    // Header untuk Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="students.xls"');

    echo "<table border='1'>
            <tr>
                <th>Nama</th>
                <th>NIM</th>
                <th>Kelas</th>
            </tr>";

    foreach ($students as $student) {
        echo "<tr>
                <td>" . htmlspecialchars($student['name']) . "</td>
                <td>" . htmlspecialchars($student['student_id']) . "</td>
                <td>" . htmlspecialchars($student['class_name']) . "</td>
              </tr>";
    }

    echo "</table>";
} elseif ($format == 'pdf') {
    // Membuat objek PDF baru
    $pdf = new FPDF();
    $pdf->AddPage();

    // Menambahkan font default
    $pdf->SetFont('Arial', 'B', 12);

    // Menyiapkan header tabel
    $header = array('Nama', 'NIM', 'Kelas');
    $w = array(50, 30, 50);

    // Menambahkan header tabel ke PDF
    for ($i = 0; $i < count($header); $i++) {
        $pdf->Cell($w[$i], 10, $header[$i], 1, 0, 'C');
    }
    $pdf->Ln();

    // Menambahkan data ke PDF
    foreach ($students as $student) {
        $pdf->Cell($w[0], 10, htmlspecialchars($student['name']), 1, 0, 'L');
        $pdf->Cell($w[1], 10, htmlspecialchars($student['student_id']), 1, 0, 'L');
        $pdf->Cell($w[2], 10, htmlspecialchars($student['class_name']), 1, 0, 'L');
        $pdf->Ln();
    }

    // Output PDF ke browser
    $pdf->Output('students.pdf', 'I');
} else {
    echo "Format tidak dikenali. Gunakan format=xls atau format=pdf.";
}
?>
