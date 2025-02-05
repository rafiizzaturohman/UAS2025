<?php
include('../auth.php');
include '../lib/config.php';

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("User not found.");
    }

    // Menghitung jumlah mahasiswa
    $stmt_students = $pdo->prepare("SELECT COUNT(*) AS total_students FROM students");
    $stmt_students->execute();
    $total_students = $stmt_students->fetchColumn();

    // Menghitung jumlah kelas
    $stmt_classes = $pdo->prepare("SELECT COUNT(*) AS total_classes FROM classes");
    $stmt_classes->execute();
    $total_classes = $stmt_classes->fetchColumn();

    // Menghitung jumlah dosen
    $stmt_teachers = $pdo->prepare("SELECT COUNT(*) AS total_teachers FROM teachers");
    $stmt_teachers->execute();
    $total_teachers = $stmt_teachers->fetchColumn();

    // Menghitung jumlah mata kuliah
    $stmt_subjects = $pdo->prepare("SELECT COUNT(*) AS total_subjects FROM subjects");
    $stmt_subjects->execute();
    $total_subjects = $stmt_subjects->fetchColumn();

    // Menghitung jumlah mahasiswa per kelas
    $stmt_students_per_class = $pdo->prepare("SELECT c.class_name, COUNT(s.id) AS student_count FROM classes c LEFT JOIN students s ON c.id = s.class_id GROUP BY c.id, c.class_name");
    $stmt_students_per_class->execute();
    $students_per_class = $stmt_students_per_class->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Log the error and redirect to an error page
    error_log($e->getMessage());
    header('Location: ../error.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../public/stylesheets/styles.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style type="text/css">
        
    </style>
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
            <h1>Selamat Datang, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <div class="dashboard-info-container">
                <!-- Kartu Jumlah Mahasiswa -->
                <div class="info-card">
                    <h3>Jumlah Mahasiswa</h3>
                    <p><?php echo $total_students; ?></p>
                </div>

                <!-- Kartu Jumlah Kelas -->
                <div class="info-card">
                    <h3>Jumlah Kelas</h3>
                    <p><?php echo $total_classes; ?></p>
                </div>

                <!-- Kartu Jumlah Dosen -->
                <div class="info-card">
                    <h3>Jumlah Dosen</h3>
                    <p><?php echo $total_teachers; ?></p>
                </div>

                <!-- Kartu Jumlah Mata Kuliah -->
                <div class="info-card">
                    <h3>Jumlah Mata Kuliah</h3>
                    <p><?php echo $total_subjects; ?></p>
                </div>
            </div>

            <!-- Tabel Jumlah Mahasiswa per Kelas -->
            <div class="students-per-class">
                <h2>Jumlah Mahasiswa per Kelas</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Kelas</th>
                            <th>Jumlah Mahasiswa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students_per_class as $class): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                <td><?php echo htmlspecialchars($class['student_count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <footer>
        <p>&copy; 2025 <i class="fa-duotone fa-solid fa-spider-web"></i> UAS Pemograman Web. All rights reserved.</p>
    </footer>
    <script>
        $(document).ready(function() {
            $('.dropdown-toggle').click(function(e) {
                e.preventDefault();
                $(this).next('.dropdown-menu').slideToggle();
            });
        });
    </script>
</body>

</html>
