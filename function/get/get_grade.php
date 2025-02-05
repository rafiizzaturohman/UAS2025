<?php
include '../../lib/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id']; // 
    $stmt = $pdo->prepare("SELECT * FROM grades WHERE id = ?");
    $stmt->execute([$id]);
    $grade = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($grade);
}
?>
