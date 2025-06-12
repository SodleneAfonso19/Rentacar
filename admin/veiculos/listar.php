<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

$pageTitle = 'Lista de Veículos';
include '../../includes/header.php';

// Consulta veículos
$stmt = $pdo->query("SELECT * FROM veiculos ORDER BY marca, modelo");
$veiculos = $stmt->fetchAll();
?>

<div class="card border-0">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Veículos Cadastrados</h5>
            <a href="cadastrar.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Novo Veículo
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Marca/Modelo</th>
                        <th>Ano/Placa</th>
                        <th>Tipo</th>
                        <th>Diária</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($veiculos as $veiculo): ?>
                    <tr>
                        <td><?= $veiculo['id'] ?></td>
                        <td>
                            <?php if ($veiculo['foto']): ?>
                            <img src="../assets/img/veiculos/<?= $veiculo['foto'] ?>" width="50" class="img-thumbnail">
                            <?php else: ?>
                            <span class="text-muted">Sem foto</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $veiculo['marca'] ?> <?= $veiculo['modelo'] ?></td>
                        <td><?= $veiculo['ano'] ?><br><?= $veiculo['placa'] ?></td>
                        <td><?= ucfirst($veiculo['tipo']) ?></td>
                        <td>R$ <?= number_format($veiculo['diaria'], 2, ',', '.') ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $veiculo['status'] == 'disponivel' ? 'success' : 
                                ($veiculo['status'] == 'alugado' ? 'warning' : 'danger') 
                            ?>">
                                <?= ucfirst($veiculo['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="editar.php?id=<?= $veiculo['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="excluir.php?id=<?= $veiculo['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza?')">
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