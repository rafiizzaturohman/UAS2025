<?php
include '../../lib/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($subject);
}
?>
