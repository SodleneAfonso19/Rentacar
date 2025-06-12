<?php
ob_start(); // <-- Importante para evitar erros de "headers already sent"

require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

// Verifica se é admin
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}

$pageTitle = 'Cadastrar Funcionário';
include '../../includes/header.php';

// Lógica de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $cargo = $_POST['cargo'] ?? 'funcionario';

    if (empty($nome) || empty($email) || empty($senha)) {
        $error = "Nome, email e senha são obrigatórios!";
    } else {
        // Verifica se o email já existe
        $stmt = $pdo->prepare("SELECT id FROM funcionarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email já cadastrado!";
        } else {
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO funcionarios (nome, email, senha, cargo)
                VALUES (?, ?, ?, ?)
            ");

            if ($stmt->execute([$nome, $email, $hash, $cargo])) {
                header('Location: listar.php?success=1');
                exit();
            } else {
                $error = "Erro ao cadastrar funcionário!";
            }
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Cadastrar Novo Funcionário</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>

                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo</label>
                        <select class="form-select" id="cargo" name="cargo" required>
                            <option value="funcionario">Funcionário</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="listar.php" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>
        </form>
    </div>
</div>

<?php 
include '../../includes/footer.php'; 
ob_end_flush(); // <-- Libera o buffer no final do script
?>
