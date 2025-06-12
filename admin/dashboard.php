<?php
require_once '../config/auth.php';
require_once '../config/db.php';
protegerPagina();

$pageTitle = 'Dashboard';
include '../includes/header.php';

// Estatísticas
$veiculos = $pdo->query("SELECT COUNT(*) FROM veiculos")->fetchColumn();
$clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$alugueis = $pdo->query("SELECT COUNT(*) FROM alugueis WHERE status = 'ativo'")->fetchColumn();
?>

<div class="row">
    <div class="col-md-4">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Veículos</h5>
                <h2 class="card-text"><?= $veiculos ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="veiculos/listar.php">Ver Detalhes</a>
                <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Clientes</h5>
                <h2 class="card-text"><?= $clientes ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="clientes/listar.php">Ver Detalhes</a>
                <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-warning text-dark mb-4">
            <div class="card-body">
                <h5 class="card-title">Aluguéis Ativos</h5>
                <h2 class="card-text"><?= $alugueis ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-dark stretched-link" href="alugueis/listar.php">Ver Detalhes</a>
                <div class="small text-dark"><i class="bi bi-chevron-right"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Últimos Aluguéis</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Período</th>
                        <th>Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("
                        SELECT a.*, c.nome as cliente, v.marca, v.modelo 
                        FROM alugueis a
                        JOIN clientes c ON a.cliente_id = c.id
                        JOIN veiculos v ON a.veiculo_id = v.id
                        ORDER BY a.data_registro DESC LIMIT 5
                    ");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['cliente'] ?></td>
                        <td><?= $row['marca'] ?> <?= $row['modelo'] ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($row['data_inicio'])) ?> - 
                            <?= date('d/m/Y', strtotime($row['data_fim'])) ?>
                        </td>
                        <td>R$ <?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $row['status'] == 'ativo' ? 'warning' : 
                                ($row['status'] == 'finalizado' ? 'success' : 'danger') 
                            ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>