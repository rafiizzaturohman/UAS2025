<?php
include '../../lib/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
}
?>
