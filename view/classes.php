<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

include '../lib/config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $class_name = $_POST['class_name'];

    if ($action == 'create') {
        $stmt = $pdo->prepare("INSERT INTO classes (class_name) VALUES (?)");
        $stmt->execute([$class_name]);
        header('Location: classes.php');
        exit;
    } elseif ($action == 'update') {
        $stmt = $pdo->prepare("UPDATE classes SET class_name = ? WHERE id = ?");
        $stmt->execute([$class_name, $id]);
        header('Location: classes.php');
        exit;
    } elseif ($action == 'delete') {
        // Cek apakah kelas digunakan oleh mahasiswa
        $stmt = $pdo->prepare("SELECT * FROM students WHERE class_id = ?");
        $stmt->execute([$id]);
        $students = $stmt->fetchAll();

        if (count($students) > 0) {
            $error = "Kelas tidak dapat dihapus karena masih digunakan oleh mahasiswa.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: classes.php');
            exit;
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM classes");
$stmt->execute();
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas</title>
    <link rel="stylesheet" href="../public/stylesheets/styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    
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
            <h1>Kelas</h1>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <button id="addClassBtn" class="btn"><i class="fa-duotone fa-solid fa-landmark"></i> Tambah Kelas</button>
            <table>
                <thead>
                    <tr>
                        <th>Nama Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                            <td>
                                <button class="btn editBtn" data-id="<?php echo $class['id']; ?>"><i class="fa-duotone fa-solid fa-pen-to-square"></i> Edit</button>
                                <button class="btn deleteBtn" data-id="<?php echo $class['id']; ?>"><i class="fa-duotone fa-solid fa-trash"></i> Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Modal Tambah/Edit Kelas -->
            <div id="modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 id="modalTitle">Tambah Kelas</h2>
                    <form id="classForm" method="post">
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="id" value="">
                        <label for="class_name">Nama Kelas:</label>
                        <input type="text" name="class_name" id="class_name" required>
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
            var btnAdd = $('#addClassBtn');
            var span = $('.close');

            btnAdd.click(function() {
                $('#modalTitle').text('Tambah Kelas');
                $('#action').val('create');
                $('#id').val('');
                $('#class_name').val('');
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
                $('#modalTitle').text('Edit Kelas'); //mengubah judul modal menjadi Edit Kelas ketika tombol edit di klik
                $('#action').val('update'); //mengubah action menjadi update ketika tombol edit di klik
                $('#id').val(id);

                $.ajax({
                    url: '../function/get/get_class.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(data) {
                        var classData = JSON.parse(data);
                        $('#class_name').val(classData.class_name);
                        modal.show();
                    }
                });
            });

            // Delete Button
            $('.deleteBtn').click(function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
                    $.ajax({
                        url: '../function/delete/delete_class.php',
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
