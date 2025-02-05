<?php
include('../auth.php');
include '../lib/config.php';

$action = $_POST['action'] ?? null;
$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? null;
$student_id = $_POST['student_id'] ?? null;
$class_id = $_POST['class_id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'create') {
        // Cek apakah NIM sudah ada
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $existingStudent = $stmt->fetch();

        if ($existingStudent) {
            $error = "NIM sudah ada.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO students (user_id, name, student_id, class_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $student_id, $class_id]);
            header('Location: students.php');
            exit;
        }
    } elseif ($action == 'update') {
        // Cek apakah NIM sudah ada untuk mahasiswa lain
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? AND id != ?");
        $stmt->execute([$student_id, $id]);
        $existingStudent = $stmt->fetch();

        if ($existingStudent) {
            $error = "NIM sudah ada.";
        } else {
            $stmt = $pdo->prepare("UPDATE students SET name = ?, student_id = ?, class_id = ? WHERE id = ?");
            $stmt->execute([$name, $student_id, $class_id, $id]);
            header('Location: students.php');
            exit;
        }
    } elseif ($action == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: students.php');
        exit;
    }
}

$stmt = $pdo->prepare("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id");
$stmt->execute();
$students = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM classes");
$stmt->execute();
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Mahasiswa</title>
    
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
            <h1>Mahasiswa</h1>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <button id="addStudentBtn" class="btn"><i class="fa-duotone fa-solid fa-user-plus"></i> Tambah Mahasiswa</button>
            
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['class_name'] ?? 'Tidak Ada'); ?></td>
                            <td>
                                <button class="btn editBtn" data-id="<?php echo $student['id']; ?>"><i class="fa-duotone fa-solid fa-pen-to-square"></i> Edit</button>
                                <button class="btn deleteBtn" data-id="<?php echo $student['id']; ?>"><i class="fa-duotone fa-solid fa-trash"></i> Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Modal Tambah/Edit Mahasiswa -->
            <div id="modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 id="modalTitle">Tambah Mahasiswa</h2>
                    <form id="studentForm" method="post">
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="id" value="">
                        <label for="name">Nama:</label>
                        <input type="text" name="name" id="name" required>
                        <label for="student_id">NIM:</label>
                        <input type="text" name="student_id" id="student_id" required>
                        <label for="class_id">Kelas:</label>
                        <select name="class_id" id="class_id" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
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
            var btnAdd = $('#addStudentBtn');
            var span = $('.close');

            btnAdd.click(function() {
                $('#modalTitle').text('Tambah Mahasiswa');
                $('#action').val('create');
                $('#id').val('');
                $('#name').val('');
                $('#student_id').val('');
                $('#class_id').val('');
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
                $('#modalTitle').text('Edit Mahasiswa'); //Mengubah judul modal menjadi Edit Mahasiswa ketika tombol edit di tekan
                $('#action').val('update'); // Mengubah action menjadi update
                $('#id').val(id);

                $.ajax({
                    url: '../function/get/get_student.php',
                    method: 'POST',
                    data: {
                        id: id
                    },
                    success: function(data) {
                        var student = JSON.parse(data);
                        $('#name').val(student.name);
                        $('#student_id').val(student.student_id);
                        $('#class_id').val(student.class_id);
                        modal.show();
                    }
                });
            });

            // Delete Button
            $('.deleteBtn').click(function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus mahasiswa ini?')) {
                    $.ajax({
                        url: '../function/delete/delete_student.php',
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
                window.location.href = '../function/export/export_students.php?format=xls';
            });

            // Export ke PDF
            $('#exportPdfBtn').click(function() {
                window.location.href = '../function/export/export_students.php?format=pdf';
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