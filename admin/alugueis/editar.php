<?php
ob_start(); // ← inicia o buffer para evitar o erro de headers
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

$pageTitle = 'Editar Aluguel';
include '../../includes/header.php';

// Validar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = (int) $_GET['id'];

// Buscar os dados do aluguel
$stmt = $pdo->prepare("
    SELECT a.*, c.nome as cliente, c.cpf, v.marca, v.modelo, v.placa, v.diaria
    FROM alugueis a
    JOIN clientes c ON a.cliente_id = c.id
    JOIN veiculos v ON a.veiculo_id = v.id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$aluguel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluguel) {
    header('Location: listar.php');
    exit();
}

// Obter veículos disponíveis + o atual
$veiculos = $pdo->query("
    (SELECT * FROM veiculos WHERE status = 'disponivel')
    UNION
    (SELECT * FROM veiculos WHERE id = {$aluguel['veiculo_id']})
    ORDER BY marca, modelo
")->fetchAll(PDO::FETCH_ASSOC);

// Obter todos os clientes
$clientes = $pdo->query("SELECT * FROM clientes ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $veiculo_id = $_POST['veiculo_id'] ?? '';
    $cliente_id = $_POST['cliente_id'] ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    if (strtotime($data_fim) <= strtotime($data_inicio)) {
        $error = "A data de fim deve ser posterior à data de início!";
    } else {
        // Buscar a diária do veículo
        $stmt = $pdo->prepare("SELECT diaria FROM veiculos WHERE id = ?");
        $stmt->execute([$veiculo_id]);
        $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($veiculo) {
            $dias = ceil((strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24));
            $valor_total = $dias * $veiculo['diaria'];

            // Atualizar aluguel
            $stmt = $pdo->prepare("
                UPDATE alugueis SET 
                    veiculo_id = ?, cliente_id = ?, data_inicio = ?, data_fim = ?, valor_total = ?, observacoes = ?
                WHERE id = ?
            ");

            $sucesso = $stmt->execute([
                $veiculo_id,
                $cliente_id,
                $data_inicio,
                $data_fim,
                $valor_total,
                $observacoes,
                $id
            ]);

            if ($sucesso) {
                // Se alterou o veículo, atualizar status
                if ($veiculo_id != $aluguel['veiculo_id']) {
                    $pdo->prepare("UPDATE veiculos SET status = 'disponivel' WHERE id = ?")->execute([$aluguel['veiculo_id']]);
                    $pdo->prepare("UPDATE veiculos SET status = 'alugado' WHERE id = ?")->execute([$veiculo_id]);
                }

                header('Location: listar.php?success=1');
                exit();
            } else {
                $error = "Erro ao atualizar o aluguel!";
            }
        } else {
            $error = "Veículo não encontrado!";
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Editar Aluguel #<?= htmlspecialchars($aluguel['id']) ?></h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select" id="cliente_id" name="cliente_id" required>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" <?= $cliente['id'] == $aluguel['cliente_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['nome']) ?> (<?= htmlspecialchars($cliente['cpf']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="veiculo_id" class="form-label">Veículo</label>
                        <select class="form-select" id="veiculo_id" name="veiculo_id" required>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <option value="<?= $veiculo['id'] ?>" data-diaria="<?= $veiculo['diaria'] ?>" <?= $veiculo['id'] == $aluguel['veiculo_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($veiculo['marca']) ?> <?= htmlspecialchars($veiculo['modelo']) ?> - <?= htmlspecialchars($veiculo['placa']) ?> (KZ <?= number_format($veiculo['diaria'], 2, ',', '.') ?>/dia)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="data_inicio" class="form-label">Data de Início</label>
                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($aluguel['data_inicio']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="data_fim" class="form-label">Data de Fim</label>
                        <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= htmlspecialchars($aluguel['data_fim']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="valor_total" class="form-label">Valor Total</label>
                        <input type="text" class="form-control" id="valor_total" value="KZ <?= number_format($aluguel['valor_total'], 2, ',', '.') ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?= htmlspecialchars($aluguel['observacoes'] ?? '') ?></textarea>
            </div>

            <div class="d-flex justify-content-end">
                <a href="listar.php" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    function calcularValor() {
        const inicio = new Date($('#data_inicio').val());
        const fim = new Date($('#data_fim').val());
        const diaria = parseFloat($('#veiculo_id option:selected').data('diaria')) || 0;

        if (!isNaN(inicio.getTime()) && !isNaN(fim.getTime()) && diaria > 0) {
            const dias = Math.ceil((fim - inicio) / (1000 * 60 * 60 * 24));
            if (dias > 0) {
                const total = dias * diaria;
                $('#valor_total').val('KZ ' + total.toLocaleString('pt-AO', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            } else {
                $('#valor_total').val('');
            }
        } else {
            $('#valor_total').val('');
        }
    }

    $('#data_inicio, #data_fim, #veiculo_id').on('change', calcularValor);
});
</script>

<?php include '../../includes/footer.php'; ?>