<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

// Obter informações do aluguel
$stmt = $pdo->prepare("SELECT veiculo_id FROM alugueis WHERE id = ? AND status = 'ativo'");
$stmt->execute([$id]);
$aluguel = $stmt->fetch();

if (!$aluguel) {
    header('Location: listar.php?error=1');
    exit();
}

// Finalizar aluguel
$pdo->beginTransaction();

try {
    // Atualizar status do aluguel
    $stmt = $pdo->prepare("UPDATE alugueis SET status = 'finalizado' WHERE id = ?");
    $stmt->execute([$id]);
    
    // Atualizar status do veículo
    $stmt = $pdo->prepare("UPDATE veiculos SET status = 'disponivel' WHERE id = ?");
    $stmt->execute([$aluguel['veiculo_id']]);
    
    $pdo->commit();
    header('Location: listar.php?success=1');
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: listar.php?error=2');
    exit();
}
?>