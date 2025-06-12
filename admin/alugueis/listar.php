<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

$pageTitle = 'Lista de Aluguéis';
include '../../includes/header.php';

// Filtros
$status = $_GET['status'] ?? 'todos';
$cliente = $_GET['cliente'] ?? '';
$veiculo = $_GET['veiculo'] ?? '';

// Consulta base
$sql = "
    SELECT a.*, c.nome as cliente, v.marca, v.modelo, v.placa, f.nome as funcionario
    FROM alugueis a
    JOIN clientes c ON a.cliente_id = c.id
    JOIN veiculos v ON a.veiculo_id = v.id
    JOIN funcionarios f ON a.funcionario_id = f.id
";

// Aplicar filtros
$where = [];
$params = [];

if ($status !== 'todos') {
    $where[] = "a.status = ?";
    $params[] = $status;
}

if (!empty($cliente)) {
    $where[] = "c.nome LIKE ?";
    $params[] = "%$cliente%";
}

if (!empty($veiculo)) {
    $where[] = "(v.marca LIKE ? OR v.modelo LIKE ? OR v.placa LIKE ?)";
    $params[] = "%$veiculo%";
    $params[] = "%$veiculo%";
    $params[] = "%$veiculo%";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY a.data_registro DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alugueis = $stmt->fetchAll();
?>

<div class="card border-0">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Aluguéis Registrados</h5>
            <a href="cadastrar.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Novo Aluguel
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <div class="mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="todos" <?= $status == 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="ativo" <?= $status == 'ativo' ? 'selected' : '' ?>>Ativos</option>
                        <option value="finalizado" <?= $status == 'finalizado' ? 'selected' : '' ?>>Finalizados</option>
                        <option value="cancelado" <?= $status == 'cancelado' ? 'selected' : '' ?>>Cancelados</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="cliente" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="cliente" name="cliente" value="<?= $cliente ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="veiculo" class="form-label">Veículo</label>
                    <input type="text" class="form-control" id="veiculo" name="veiculo" value="<?= $veiculo ?>">
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="listar.php" class="btn btn-secondary">Limpar</a>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Período</th>
                        <th>Valor</th>
                        <th>Registrado por</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alugueis as $aluguel): ?>
                    <tr>
                        <td><?= $aluguel['id'] ?></td>
                        <td><?= $aluguel['cliente'] ?></td>
                        <td><?= $aluguel['marca'] ?> <?= $aluguel['modelo'] ?> (<?= $aluguel['placa'] ?>)</td>
                        <td>
                            <?= date('d/m/Y', strtotime($aluguel['data_inicio'])) ?> - 
                            <?= date('d/m/Y', strtotime($aluguel['data_fim'])) ?>
                        </td>
                        <td>R$ <?= number_format($aluguel['valor_total'], 2, ',', '.') ?></td>
                        <td><?= $aluguel['funcionario'] ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $aluguel['status'] == 'ativo' ? 'warning' : 
                                ($aluguel['status'] == 'finalizado' ? 'success' : 'danger') 
                            ?>">
                                <?= ucfirst($aluguel['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="editar.php?id=<?= $aluguel['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($aluguel['status'] == 'ativo'): ?>
                            <a href="finalizar.php?id=<?= $aluguel['id'] ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Finalizar este aluguel?')">
                                <i class="bi bi-check-circle"></i>
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