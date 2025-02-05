<?php
include '../..//lib/config.php';
require_once('../../lib/fpdf/fpdf.php'); // Sesuaikan dengan path ke direktori FPDF Anda

// Mendapatkan parameter format dari URL
$format = isset($_GET['format']) ? $_GET['format'] : 'xls';

// Menyiapkan SQL query
$stmt = $pdo->prepare("SELECT g.*, s.name AS student_name, sub.subject_name, t.name AS teacher_name FROM grades g JOIN students s ON g.student_id = s.id JOIN subjects sub ON g.subject_id = sub.id JOIN teachers t ON sub.teacher_id = t.id");
$stmt->execute();
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format == 'xls') {
    // Header untuk Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="grades.xls"');

    echo "<table border='1'>
            <tr>
                <th>Mahasiswa</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Nilai</th>
            </tr>";

    foreach ($grades as $grade) {
        echo "<tr>
                <td>" . htmlspecialchars($grade['student_name']) . "</td>
                <td>" . htmlspecialchars($grade['subject_name']) . "</td>
                <td>" . htmlspecialchars($grade['teacher_name']) . "</td>
                <td>" . htmlspecialchars($grade['grade']) . "</td>
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
    $header = array('Mahasiswa', 'Mata Kuliah', 'Dosen', 'Nilai');
    $w = array(50, 50, 50, 20);

    // Menambahkan header tabel ke PDF
    for ($i = 0; $i < count($header); $i++) {
        $pdf->Cell($w[$i], 10, $header[$i], 1, 0, 'C');
    }
    $pdf->Ln();

    // Mengubah font untuk data
    $pdf->SetFont('Arial', '', 12);

    // Menambahkan data ke PDF
    foreach ($grades as $grade) {
        $pdf->Cell($w[0], 10, htmlspecialchars($grade['student_name']), 1, 0, 'L');
        $pdf->Cell($w[1], 10, htmlspecialchars($grade['subject_name']), 1, 0, 'L');
        $pdf->Cell($w[2], 10, htmlspecialchars($grade['teacher_name']), 1, 0, 'L');
        $pdf->Cell($w[3], 10, htmlspecialchars($grade['grade']), 1, 0, 'C');
        $pdf->Ln();
    }

    // Output PDF ke browser
    $pdf->Output('grades.pdf', 'I');
} else {
    echo "Format tidak dikenali. Gunakan format=xls atau format=pdf.";
}
?>
