<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

$pageTitle = 'Lista de Clientes';
include '../../includes/header.php';

// Consulta clientes
$stmt = $pdo->query("SELECT * FROM clientes ORDER BY nome");
$clientes = $stmt->fetchAll();
?>

<div class="card border-0">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Clientes Cadastrados</h5>
            <a href="cadastrar.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Novo Cliente
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
                        <th>NIF</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= $cliente['id'] ?></td>
                        <td><?= $cliente['nome'] ?></td>
                        <td><?= $cliente['cpf'] ?></td>
                        <td><?= $cliente['telefone'] ?></td>
                        <td><?= $cliente['email'] ?? '--' ?></td>
                        <td><?= date('d/m/Y', strtotime($cliente['data_cadastro'])) ?></td>
                        <td>
                            <a href="editar.php?id=<?= $cliente['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="excluir.php?id=<?= $cliente['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>