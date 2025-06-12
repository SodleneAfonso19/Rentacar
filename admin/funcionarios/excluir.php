<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

// Verifica se é admin
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

// Impede que o usuário atual se exclua
if ($id == $_SESSION['user_id']) {
    header('Location: listar.php?error=self');
    exit();
}

// Verifica se o funcionário tem aluguéis registrados
$stmt = $pdo->prepare("SELECT COUNT(*) FROM alugueis WHERE funcionario_id = ?");
$stmt->execute([$id]);

if ($stmt->fetchColumn() > 0) {
    header('Location: listar.php?error=aluguel');
    exit();
}

// Exclui o funcionário
$stmt = $pdo->prepare("DELETE FROM funcionarios WHERE id = ?");
$stmt->execute([$id]);

header('Location: listar.php?success=1');
exit();
?>