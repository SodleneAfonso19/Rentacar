<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

// Verifica se é admin
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}

$pageTitle = 'Lista de Funcionários';
include '../../includes/header.php';

// Consulta funcionários
$stmt = $pdo->query("SELECT * FROM funcionarios ORDER BY nome");
$funcionarios = $stmt->fetchAll();
?>

<div class="card border-0">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Funcionários Cadastrados</h5>
            <a href="cadastrar.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Novo Funcionário
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Cargo</th>
                        <th>Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($funcionarios as $func): ?>
                    <tr>
                        <td><?= $func['id'] ?></td>
                        <td><?= $func['nome'] ?></td>
                        <td><?= $func['email'] ?></td>
                        <td><?= ucfirst($func['cargo']) ?></td>
                        <td><?= date('d/m/Y', strtotime($func['data_cadastro'])) ?></td>
                        <td>
                            <a href="editar.php?id=<?= $func['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($func['id'] != $_SESSION['user_id']): ?>
                            <a href="excluir.php?id=<?= $func['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>