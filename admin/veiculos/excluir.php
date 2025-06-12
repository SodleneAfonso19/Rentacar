<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

// Verificar se o veículo está em algum aluguel ativo
$stmt = $pdo->prepare("SELECT COUNT(*) FROM alugueis WHERE veiculo_id = ? AND status = 'ativo'");
$stmt->execute([$id]);
$emUso = $stmt->fetchColumn();

if ($emUso) {
    header('Location: listar.php?error=1');
    exit();
}

// Obter informações do veículo para remover a foto
$stmt = $pdo->prepare("SELECT foto FROM veiculos WHERE id = ?");
$stmt->execute([$id]);
$veiculo = $stmt->fetch();

if ($veiculo && $veiculo['foto'] && file_exists("../../assets/img/veiculos/" . $veiculo['foto'])) {
    unlink("../../assets/img/veiculos/" . $veiculo['foto']);
}

// Excluir o veículo
$stmt = $pdo->prepare("DELETE FROM veiculos WHERE id = ?");
$stmt->execute([$id]);

header('Location: listar.php?success=1');
exit();
?>