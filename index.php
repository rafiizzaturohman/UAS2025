<?php
session_start();
include __DIR__ . '/lib/config.php';

$error = ""; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') { //Memeriksa apakah form telah di submit dan metode nya POST
    if (isset($_POST['login'])) { // Memeriksa apakah name dari button dalam form dengan
        // method POST itu adalah login
        $username = $_POST['username']; // Menerima value dari input dan menyimpannya 
                                        // ke dalam variabel username 
                                        // berdasarkan name=username
        $password = $_POST['password']; // Menerima value dari input dan menyimpannya 
                                        // ke dalam variabel password 
                                        // berdasarkan name=password

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?"); // Mempersiapkan 
        // variable stmt yang memiliki value berupa SQL yang dikondisikan 
        // berdasarkan username
        $stmt->execute([$username]); // Mengeksekusi SQL yang telah disiapkan 
                                     // dengan parameter username
        $user = $stmt->fetch(); // Membuat variabel user yang memiliki 
        // value berupa data dari database berdasarkan username yang telah diinputkan

        if ($user && password_verify($password, $user['password'])) { // Memeriksa dan
            // memverifikasi password dan user yang login
            $_SESSION['user_id'] = $user['id']; //Mengambil id berdasarkan 
            // SESSION dari user yang login
            header('Location: ./view/dashboard.php'); // Jika password benar 
            // langsung diarahkan ke halaman dashboard
            exit;
        } else {
            $error = "Username atau password salah."; // Jika salah akan menampilkan error
        }
    }

    // Cek apakah form registrasi telah di submit
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];

        // Memvalidasi hasil inputan
        if (empty($username) || empty($password) || empty($email)) {
            $error = "Semua field harus diisi."; // Pesan error jika ada field yang kosong atau tidak diisi
        } else {
            // Cek apakah email dan username sudah ada
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $user = $stmt->fetch();

            if ($user) {
                $error = "Username atau email sudah ada."; // Pesan Error jika username atau email sudah ada
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Query untuk menambahkan user baru
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $email]); // Mengeksekusi query untuk menambahkan user baru dengan parameter username, password, dan email

                // Jika pendaftaran berhasil langsung diarahkan ke halaman login
                header('Location: index.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home</title>
    
    <link rel="stylesheet" href="./public/stylesheets/login.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <link rel="shortcut icon" href="./assets/images/logo.png" type="image/x-icon">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form method="post">
                <h1>Register</h1>
                <div class="infield">
                    <input type="text" name="username" placeholder="Username" required>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="email" name="email" placeholder="Email" required>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="password" name="password" placeholder="Password" required>
                    <label></label>
                </div>
                <button type="submit" name="register">Register</button>
            </form>
        </div>
        <div class="form-container sign-in-container">
            <form method="post">
                <h1>Login</h1>
                <div class="infield">
                    <input type="text" name="username" placeholder="Username" required>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="password" name="password" placeholder="Password" required>
                    <label></label>
                </div>
                <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

                <button type="submit" name="login">Login</button>
            </form>
        </div>
        <div class="overlay-container" id="overlayCon">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Punya Akun ?</h1>
                    <p>Ya udah jangan daftar lagi, langsung login di sini!</p>
                    <button id="signIn">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Halo sobb!</h1>
                    <p>Daftarnya sebelah sini ya! bukan di tempat lain</p>
                    <button id="signUp">Sign Up</button>
                </div>
            </div>
            <button id="overlayBtn"></button>
        </div>
    </div>

    <script>
        const container = document.getElementById('container');
        const overlayBtn = document.getElementById('overlayBtn');

        // Event listener for the overlay button
        overlayBtn.addEventListener('click', () => {
            container.classList.toggle('right-panel-active');

            // Optional: Add scaling effect to the button
            overlayBtn.classList.remove('btnScaled');
            window.requestAnimationFrame(() => {
                overlayBtn.classList.add('btnScaled');
            });
        });

        // Dedicated buttons for sign in and sign up
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');

        signUpButton.addEventListener('click', () => {
            container.classList.add('right-panel-active');
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove('right-panel-active');
        });
    </script>

    <footer>
        <p>&copy; 2025 <i class="fa-duotone fa-solid fa-spider-web"></i> UAS Pemograman Web. All rights reserved.</p>
    </footer>
</body>

</html>
