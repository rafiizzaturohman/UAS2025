<?php
include('../auth.php');
include '../lib/config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $subject_name = $_POST['subject_name'];
    $teacher_id = $_POST['teacher_id'];

    if ($action == 'create') {
        $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, teacher_id) VALUES (?, ?)");
        $stmt->execute([$subject_name, $teacher_id]);
        header('Location: subjects.php');
        exit;
    } elseif ($action == 'update') {
        $stmt = $pdo->prepare("UPDATE subjects SET subject_name = ?, teacher_id = ? WHERE id = ?");
        $stmt->execute([$subject_name, $teacher_id, $id]);
        header('Location: subjects.php');
        exit;
    } elseif ($action == 'delete') {
        // Cek apakah mata kuliah digunakan oleh nilai
        $stmt = $pdo->prepare("SELECT * FROM grades WHERE subject_id = ?");
        $stmt->execute([$id]);
        $grades = $stmt->fetchAll();

        if (count($grades) > 0) {
            $error = "Mata kuliah tidak dapat dihapus karena masih digunakan oleh nilai.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: subjects.php');
            exit;
        }
    }
}

$stmt = $pdo->prepare("SELECT s.*, t.name AS teacher_name FROM subjects s LEFT JOIN teachers t ON s.teacher_id = t.id");
$stmt->execute();
$subjects = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM teachers");
$stmt->execute();
$teachers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Mata Kuliah</title>
    
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
            <h1>Mata Kuliah</h1>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <button id="addSubjectBtn" class="btn"><i class="fa-sharp-duotone fa-solid fa-diploma"></i> Tambah Mata Kuliah</button>
            <table>
                <thead>
                    <tr>
                        <th>Nama Mata Kuliah</th>
                        <th>Dosen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($subject['teacher_name'] ?? 'Tidak Ada'); ?></td>
                            <td>
                                <button class="btn editBtn" data-id="<?php echo $subject['id']; ?>"><i class="fa-duotone fa-solid fa-pen-to-square"></i> Edit</button>
                                <button class="btn deleteBtn" data-id="<?php echo $subject['id']; ?>"><i class="fa-duotone fa-solid fa-trash"></i> Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Modal Tambah/Edit Mata Kuliah -->
            <div id="modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 id="modalTitle">Tambah Mata Kuliah</h2>
                    <form id="subjectForm" method="post">
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="id" value="">
                        <label for="subject_name">Nama Mata Kuliah:</label>
                        <input type="text" name="subject_name" id="subject_name" required>
                        <label for="teacher_id">Dosen:</label>
                        <select name="teacher_id" id="teacher_id" required>
                            <option value="">Pilih Dosen</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['name']); ?></option>
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
            var btnAdd = $('#addSubjectBtn');
            var span = $('.close');

            btnAdd.click(function() {
                $('#modalTitle').text('Tambah Mata Kuliah');
                $('#action').val('create');
                $('#id').val('');
                $('#subject_name').val('');
                $('#teacher_id').val('');
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
                $('#modalTitle').text('Edit Mata Kuliah'); //Mengubah judul modal menjadi Edit Mata Kuliah ketika tombol edit di tekan
                $('#action').val('update'); //Mengubah Action menjadi update ketika tombol edit di tekan
                $('#id').val(id);

                $.ajax({
                    url: '../function/get/get_subject.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(data) {
                        var subject = JSON.parse(data);
                        $('#subject_name').val(subject.subject_name);
                        $('#teacher_id').val(subject.teacher_id);
                        modal.show();
                    }
                });
            });

            // Delete Button
            $('.deleteBtn').click(function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')) {
                    $.ajax({
                        url: '../function/delete/delete_subject.php',
                        method: 'POST',
                        data: { id: id },
                        success: function(data) {
                            location.reload();
                        }
                    });
                }
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
