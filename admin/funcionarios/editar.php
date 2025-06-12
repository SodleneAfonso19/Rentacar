<?php
ob_start(); // <- Isso evita erro de "headers already sent"

require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

// Verifica se é admin
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}

// Verifica se o ID foi passado
if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = (int) $_GET['id'];
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE id = ?");
$stmt->execute([$id]);
$funcionario = $stmt->fetch();

if (!$funcionario) {
    header('Location: listar.php');
    exit();
}

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cargo = $_POST['cargo'] ?? 'funcionario';

    if (empty($nome) || empty($email)) {
        $error = "Nome e email são obrigatórios!";
    } else {
        $stmt = $pdo->prepare("UPDATE funcionarios SET nome = ?, email = ?, cargo = ? WHERE id = ?");
        if ($stmt->execute([$nome, $email, $cargo, $id])) {
            header('Location: listar.php?success=1');
            exit();
        } else {
            $error = "Erro ao atualizar funcionário!";
        }
    }
}

$pageTitle = 'Editar Funcionário';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Editar Funcionário</h5>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($funcionario['nome']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($funcionario['email']) ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo</label>
                        <select class="form-select" id="cargo" name="cargo" required>
                            <option value="funcionario" <?= $funcionario['cargo'] === 'funcionario' ? 'selected' : '' ?>>Funcionário</option>
                            <option value="admin" <?= $funcionario['cargo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="listar.php" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<?php 
include '../../includes/footer.php'; 
ob_end_flush(); // <- Finaliza o buffer e evita erro
?>
