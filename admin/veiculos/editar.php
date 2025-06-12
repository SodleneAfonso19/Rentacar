<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

// Buscar veículo no banco
$stmt = $pdo->prepare("SELECT * FROM veiculos WHERE id = ?");
$stmt->execute([$id]);
$veiculo = $stmt->fetch();

if (!$veiculo) {
    header('Location: listar.php');
    exit();
}

$error = null;

// Processar formulário após envio POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $placa = $_POST['placa'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $diaria = $_POST['diaria'] ?? '';
    $status = $_POST['status'] ?? 'disponivel';

    $foto = $veiculo['foto']; // Foto atual

    // Verifica se quer remover foto atual
    if (isset($_POST['remover_foto']) && $_POST['remover_foto'] == 'on') {
        if ($foto && file_exists("../../assets/img/veiculos/$foto")) {
            unlink("../../assets/img/veiculos/$foto");
        }
        $foto = null;
    }

    // Atualizar foto se for enviada nova imagem
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Remove foto antiga se existir
        if ($foto && file_exists("../../assets/img/veiculos/$foto")) {
            unlink("../../assets/img/veiculos/$foto");
        }

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../../assets/img/veiculos/' . $foto);
    }

    // Atualizar banco de dados
    $stmt = $pdo->prepare("
        UPDATE veiculos SET 
        marca = ?, modelo = ?, ano = ?, placa = ?, tipo = ?, diaria = ?, status = ?, foto = ?
        WHERE id = ?
    ");

    if ($stmt->execute([$marca, $modelo, $ano, $placa, $tipo, $diaria, $status, $foto, $id])) {
        header('Location: listar.php?success=1');
        exit();
    } else {
        $error = "Erro ao atualizar veículo!";
    }
}

$pageTitle = 'Editar Veículo';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Editar Veículo</h5>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="marca" class="form-label">Marca</label>
                        <input type="text" class="form-control" id="marca" name="marca" value="<?= htmlspecialchars($veiculo['marca']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="modelo" class="form-label">Modelo</label>
                        <input type="text" class="form-control" id="modelo" name="modelo" value="<?= htmlspecialchars($veiculo['modelo']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="ano" class="form-label">Ano</label>
                        <input type="number" class="form-control" id="ano" name="ano" min="1900" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($veiculo['ano']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="placa" class="form-label">Placa</label>
                        <input type="text" class="form-control" id="placa" name="placa" value="<?= htmlspecialchars($veiculo['placa']) ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="hatch" <?= $veiculo['tipo'] === 'hatch' ? 'selected' : '' ?>>Hatch</option>
                            <option value="sedan" <?= $veiculo['tipo'] === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                            <option value="suv" <?= $veiculo['tipo'] === 'suv' ? 'selected' : '' ?>>SUV</option>
                            <option value="pickup" <?= $veiculo['tipo'] === 'pickup' ? 'selected' : '' ?>>Pickup</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="diaria" class="form-label">Diária (R$)</label>
                        <input type="number" class="form-control" id="diaria" name="diaria" step="0.01" min="0" value="<?= htmlspecialchars($veiculo['diaria']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="disponivel" <?= $veiculo['status'] === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                            <option value="alugado" <?= $veiculo['status'] === 'alugado' ? 'selected' : '' ?>>Alugado</option>
                            <option value="manutencao" <?= $veiculo['status'] === 'manutencao' ? 'selected' : '' ?>>Manutenção</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto do Veículo</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                        <?php if ($veiculo['foto']): ?>
                            <div class="mt-2">
                                <img src="../../assets/img/veiculos/<?= htmlspecialchars($veiculo['foto']) ?>" width="100" class="img-thumbnail" alt="Foto do veículo">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remover_foto" name="remover_foto">
                                    <label class="form-check-label" for="remover_foto">Remover foto atual</label>
                                </div>
                            </div>
                        <?php endif; ?>
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

<?php include '../../includes/footer.php'; ?>
