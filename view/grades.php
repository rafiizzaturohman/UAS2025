<?php
include('../auth.php');
include '../lib/config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $grade = $_POST['grade'];

    if ($action == 'create') {
        $stmt = $pdo->prepare("INSERT INTO grades (student_id, subject_id, grade) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $subject_id, $grade]);
        header('Location: grades.php');
        exit;
    } elseif ($action♣ == 'update') {
        $stmt = $pdo->prepare("UPDATE grades SET student_id = ?, subject_id = ?, grade = ? WHERE id = ?");
        $stmt->execute([$student_id, $subject_id, $grade, $id]);
        header('Location: grades.php');
        exit;
    } elseif ($action == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM grades WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: grades.php');
        exit;
    }
}

$stmt = $pdo->prepare("SELECT g.*, s.name AS student_name, sub.subject_name, t.name AS teacher_name FROM grades g JOIN students s ON g.student_id = s.id JOIN subjects sub ON g.subject_id = sub.id JOIN teachers t ON sub.teacher_id = t.id");
$stmt->execute();
$grades = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM students");
$stmt->execute();
$students = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM subjects");
$stmt->execute();
$subjects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Nilai</title>
    
    <link rel="stylesheet" href="../public/stylesheets/styles.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="navbar-brand"><i class="fa-duotone fa-solid fa-spider-web"></i> UAS Pemograman Web</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php"><i class="fa-solid fa-grid-horizontal"></i> Dashboard</a></li>
                <li><a href="../function/logout.php"><i class="fa-duotone fa-solid fa-arrow-up-left-from-circle"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li>
                    <a href="#" class="dropdown-toggle">Data</a>
                    <ul class="dropdown-menu">
                        <li><a href="grades.php">Nilai</a></li>
                        <li><a href="students.php">Mahasiswa</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="dropdown-toggle">Master</a>
                    <ul class="dropdown-menu">
                        <li><a href="classes.php">Kelas</a></li>
                        <li><a href="subjects.php">Mata Kuliah</a></li>
                        <li><a href="teachers.php">Dosen</a></li>
                    </ul>
                </li>
            </ul>
        </aside>
        <main class="content">
            <div class="export">Export :
            <button id="exportExcelBtn"><i class="fa-duotone fa-regular fa-file-excel"></i></button>
            <button id="exportPdfBtn"><i class="fa-duotone fa-solid fa-file-pdf"></i></button>
            </div>
            <h1>Nilai</h1>
            <button id="addGradeBtn" class="btn"><i class="fa-sharp-duotone fa-solid fa-book"></i> Tambah Nilai</button>
           
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Mata Kuliah</th>
                        <th>Dosen</th>
                        <th>Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($grade['teacher_name']); ?></td>
                            <td><?php echo htmlspecialchars($grade['grade']); ?></td>
                            <td>
                                <button class="btn editBtn" data-id="<?php echo $grade['id']; ?>"><i class="fa-duotone fa-solid fa-pen-to-square"></i> Edit</button>
                                <button class="btn deleteBtn" data-id="<?php echo $grade['id']; ?>"><i class="fa-duotone fa-solid fa-trash"></i> Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Modal Tambah/Edit Nilai -->
            <div id="modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 id="modalTitle">Tambah Nilai</h2>
                    <form id="gradeForm" method="post">
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="id" value="">
                        <label for="student_id">Mahasiswa:</label>
                        <select name="student_id" id="student_id" required>
                            <option value="">Pilih Mahasiswa</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="subject_id">Mata Kuliah:</label>
                        <select name="subject_id" id="subject_id" required>
                            <option value="">Pilih Mata Kuliah</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="grade">Nilai:</label>
                        <input type="number" step="0.01" name="grade" id="grade" required>
                        <button type="submit" class="btn" id="simpan"><i class="fa-sharp-duotone fa-solid fa-floppy-disk"></i> Simpan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <footer>
        <p>&copy; 2025 <i class="fa-duotone fa-solid fa-spider-web"></i> UAS Pemograman Web. All rights reserved.</p>
    </footer>
    <script>
        $(document).ready(function() {
            // Modal
            var modal = $('#modal');
            var btnAdd = $('#addGradeBtn');
            var span = $('.close');

            btnAdd.click(function() {
                $('#modalTitle').text('Tambah Nilai');
                $('#action').val('create');
                $('#id').val('');
                $('#student_id').val('');
                $('#subject_id').val('');
                $('#grade').val('');
                modal.show();
            });

            span.click(function() {
                modal.hide();
            });

            $(window).click(function(event) {
                if ($(event.target).is(modal)) {
                    modal.hide();
                }
            });

            // Edit Button
            $('.editBtn').click(function() {
                var id = $(this).data('id');
                $('#modalTitle').text('Edit Nilai'); //Mengubah judul modal menjadi Edit Nilai ketika tombol edit di klik
                $('#action').val('update'); //Mengubah action menjadi update ketika tombol edit di klik
                $('#id').val(id);

                $.ajax({
                    url: '../function/get/get_grade.php',
                    method: 'POST',
                    data: {
                        id: id
                    },
                    success: function(data) {
                        var grade = JSON.parse(data);
                        $('#student_id').val(grade.student_id);
                        $('#subject_id').val(grade.subject_id);
                        $('#grade').val(grade.grade);
                        modal.show();
                    }
                });
            });

            // Delete Button
            $('.deleteBtn').click(function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus nilai ini?')) {
                    $.ajax({
                        url: '../function/delete/delete_grade.php',
                        method: 'POST',
                        data: {
                            id: id
                        },
                        success: function(data) {
                            location.reload();
                        }
                    });
                }
            });

            // Export ke Excel
            $('#exportExcelBtn').click(function() {
                window.location.href = '../function/export/export_grades.php?format=xls';
            });

            // Export ke PDF
            $('#exportPdfBtn').click(function() {
                window.location.href = '../function/export/export_grades.php?format=pdf';
            });
        });

        $(document).ready(function() {
            $('.dropdown-toggle').click(function(e) {
                e.preventDefault();
                $(this).next('.dropdown-menu').slideToggle();
            });
        });
    </script>
</body>

</html>