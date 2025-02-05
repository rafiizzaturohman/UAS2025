<?php
include('../auth.php');
include '../lib/config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $email = $_POST['email'];

    if ($action == 'create') {
        // Cek apakah email sudah ada
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE email = ?");
        $stmt->execute([$email]); // Mengeksekusi atau menjalankan query SELECT dengan pengkondisian berdasarkan email
        $existingTeacher = $stmt->fetch(); // Mengambil data dosen berdasarkan email

        if ($existingTeacher) {
            $error = "Email sudah ada."; // Jika email sudah ada, maka akan menampilkan pesan error
        } else {
            $stmt = $pdo->prepare("INSERT INTO teachers (name, email) VALUES (?, ?)"); // Membuat query INSERT
            $stmt->execute([$name, $email]); // Mengeksekusi query INSERT dengan pengkondisian berdasarkan nama dan email
            header('Location: teachers.php'); // Jika berhasil, maka akan mengarahkan ke halaman teachers.php
            exit;
        }
    } elseif ($action == 'update') {
        // Cek apakah email sudah ada untuk dosen lain
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE email = ? AND id != ?"); // Membuat query SELECT dengan pengkondisian berdasarkan email dan id
        $stmt->execute([$email, $id]); // Mengeksekusi query SELECT dengan pengkondisian berdasarkan email dan id
        $existingTeacher = $stmt->fetch(); // Mengambil data dosen berdasarkan email dan id

        if ($existingTeacher) { // Jika email sudah ada untuk dosen lain
            $error = "Email sudah ada."; // Jika email sudah ada, maka akan menampilkan pesan error
        } else {
            $stmt = $pdo->prepare("UPDATE teachers SET name = ?, email = ? WHERE id = ?"); // Membuat query UPDATE
            $stmt->execute([$name, $email, $id]); // Mengeksekusi query UPDATE dengan untuk kolom nama, email dan berdasarkan id
            header('Location: teachers.php'); // Jika berhasil, maka akan mengarahkan ke halaman teachers.php
            exit;
        }
    } elseif ($action == 'delete') {
        // Cek apakah dosen digunakan oleh mata kuliah
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE teacher_id = ?"); // Membuat Query Select dengan pengkondisian berdasarkan id dosen
        $stmt->execute([$id]); // Mengeksekusi Query SELECT dengan pengkondisian berdasarkan id dosen
        $subjects = $stmt->fetchAll(); // Mengambil data dosen berdasarkan id

        if (count($subjects) > 0) { // Memverifikasi apakah jumlah matakuliah yang digunakan oleh dosen lebih dari 0
            $error = "Dosen tidak dapat dihapus karena masih digunakan oleh mata kuliah."; // Jika dosen digunakan oleh mata kuliah, maka akan menampilkan pesan error
        } else {
            $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?"); // Membuat query DELETE
            $stmt->execute([$id]); // Mengeksekusi query DELETE dengan pengkondisian berdasarkan id
            header('Location: teachers.php'); // Jika berhasil, maka akan mengarahkan ke halaman teachers.php
            exit;
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM teachers"); // Membuat Query SELECT
$stmt->execute(); // Mengeksekusi Query SELECT
$teachers = $stmt->fetchAll(); // Mengambil data dosen
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosen</title>
    <link rel="stylesheet" href="../public/stylesheets/styles.css">
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
            <h1>Dosen</h1>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <button id="addTeacherBtn" class="btn"><i class="fa-duotone fa-solid fa-chalkboard-user"></i> Tambah Dosen</button>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                            <td>
                                <button class="btn editBtn" data-id="<?php echo $teacher['id']; ?>"><i class="fa-duotone fa-solid fa-pen-to-square"></i> Edit</button>
                                <button class="btn deleteBtn" data-id="<?php echo $teacher['id']; ?>"><i class="fa-duotone fa-solid fa-trash"></i> Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Modal Tambah/Edit Dosen -->
            <div id="modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 id="modalTitle">Tambah Dosen</h2>
                    <form id="teacherForm" method="post">
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="id" value="">
                        <label for="name">Nama:</label>
                        <input type="text" name="name" id="name" required>
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required>
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
            var btnAdd = $('#addTeacherBtn');
            var span = $('.close');

            btnAdd.click(function() {
                $('#modalTitle').text('Tambah Dosen');
                $('#action').val('create');
                $('#id').val('');
                $('#name').val('');
                $('#email').val('');
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
                $('#modalTitle').text('Edit Dosen'); //Mengubah judul modal menjadi Edit Dosen ketika tombol edit di klik
                $('#action').val('update'); //Mengubah action menjadi update ketika tombol edit di klik
                $('#id').val(id);

                $.ajax({
                    url: '../function/get/get_teacher.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(data) {
                        var teacher = JSON.parse(data);
                        $('#name').val(teacher.name);
                        $('#email').val(teacher.email);
                        modal.show();
                    }
                });
            });

            // Delete Button
            $('.deleteBtn').click(function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus dosen ini?')) {
                    $.ajax({
                        url: '../function/delete/delete_teacher.php',
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
