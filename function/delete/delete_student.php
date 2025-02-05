<?php
include '../../lib/config.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $success = $stmt->execute([$id]);

    echo json_encode(['success' => $success]);
}
?>
