<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

// Verificar se o cliente está em algum aluguel
$stmt = $pdo->prepare("SELECT COUNT(*) FROM alugueis WHERE cliente_id = ?");
$stmt->execute([$id]);
$emUso = $stmt->fetchColumn();

if ($emUso) {
    header('Location: listar.php?error=1');
    exit();
}

// Excluir o cliente
$stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
$stmt->execute([$id]);

header('Location: listar.php?success=1');
exit();
?>