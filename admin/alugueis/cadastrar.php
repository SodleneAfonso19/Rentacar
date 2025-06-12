<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

// Obter veículos e clientes
$veiculos = $pdo->query("SELECT * FROM veiculos WHERE status = 'disponivel' ORDER BY marca, modelo")->fetchAll();
$clientes = $pdo->query("SELECT * FROM clientes ORDER BY nome")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $veiculo_id = $_POST['veiculo_id'] ?? '';
    $cliente_id = $_POST['cliente_id'] ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    // Validar datas
    if (strtotime($data_fim) <= strtotime($data_inicio)) {
        $error = "A data de fim deve ser posterior à data de início!";
    } else {
        // Calcular valor total
        $veiculo = $pdo->prepare("SELECT diaria FROM veiculos WHERE id = ?");
        $veiculo->execute([$veiculo_id]);
        $veiculo = $veiculo->fetch();

        if ($veiculo) {
            $dias = (strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24);
            $valor_total = $dias * $veiculo['diaria'];

            // Inserir no banco
            $stmt = $pdo->prepare("
                INSERT INTO alugueis 
                (veiculo_id, cliente_id, funcionario_id, data_inicio, data_fim, valor_total, observacoes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt->execute([
                $veiculo_id,
                $cliente_id,
                $_SESSION['user_id'],
                $data_inicio,
                $data_fim,
                $valor_total,
                $observacoes
            ])) {
                $pdo->prepare("UPDATE veiculos SET status = 'alugado' WHERE id = ?")->execute([$veiculo_id]);

                header('Location: listar.php?success=1');
                exit();
            } else {
                $error = "Erro ao registrar aluguel!";
            }
        } else {
            $error = "Veículo não encontrado!";
        }
    }
}

// Incluir HTML somente após o processamento
$pageTitle = 'Cadastrar Aluguel';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Cadastrar Novo Aluguel</h5>
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
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?> (<?= htmlspecialchars($cliente['cpf']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="veiculo_id" class="form-label">Veículo</label>
                        <select class="form-select" id="veiculo_id" name="veiculo_id" required>
                            <option value="">Selecione um veículo</option>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <option value="<?= $veiculo['id'] ?>" data-diaria="<?= $veiculo['diaria'] ?>">
                                    <?= htmlspecialchars($veiculo['marca']) ?> <?= htmlspecialchars($veiculo['modelo']) ?> - <?= htmlspecialchars($veiculo['placa']) ?> (KZ <?= number_format($veiculo['diaria'], 2, ',', '.') ?>/dia)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="data_inicio" class="form-label">Data de Início</label>
                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                    </div>

                    <div class="mb-3">
                        <label for="data_fim" class="form-label">Data de Fim</label>
                        <input type="date" class="form-control" id="data_fim" name="data_fim" required>
                    </div>

                    <div class="mb-3">
                        <label for="valor_total" class="form-label">Valor Total</label>
                        <input type="text" class="form-control" id="valor_total" name="valor_total" readonly>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
            </div>

            <div class="d-flex justify-content-end">
                <a href="listar.php" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Aluguel</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){
    function calcularValor() {
        const inicio = new Date($('#data_inicio').val());
        const fim = new Date($('#data_fim').val());
        const veiculo = $('#veiculo_id option:selected');
        const diaria = parseFloat(veiculo.data('diaria')) || 0;

        if (!isNaN(inicio) && !isNaN(fim) && diaria > 0) {
            const dias = (fim - inicio) / (1000 * 60 * 60 * 24);
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

    // Data mínima = hoje
    const hoje = new Date().toISOString().split('T')[0];
    $('#data_inicio').attr('min', hoje);
    $('#data_fim').attr('min', hoje);
});
</script>

<?php include '../../includes/footer.php'; ?>
