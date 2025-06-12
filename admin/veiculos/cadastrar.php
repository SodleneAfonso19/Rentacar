<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

// Inicia o buffer de saída para evitar erros de header
ob_start();

$pageTitle = 'Cadastrar Veículo';
include '../../includes/header.php';

// Verifica e cria o diretório de upload se não existir
$uploadDir = '../../assets/img/veiculos/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $placa = strtoupper($_POST['placa'] ?? ''); // Converte placa para maiúsculas
    $tipo = $_POST['tipo'] ?? '';
    $diaria = $_POST['diaria'] ?? '';
    $status = $_POST['status'] ?? 'disponivel';
    
    // Validação básica dos campos
    if (empty($marca) || empty($modelo) || empty($placa)) {
        $error = "Marca, modelo e placa são obrigatórios!";
    } else {
        // Verifica se a placa já existe
        $stmt = $pdo->prepare("SELECT id FROM veiculos WHERE placa = ?");
        $stmt->execute([$placa]);
        
        if ($stmt->fetch()) {
            $error = "Já existe um veículo cadastrado com esta placa!";
        } else {
            // Processamento do upload da foto
            $foto = '';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $foto = uniqid() . '.' . $ext;
                $destino = $uploadDir . $foto;
                
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                    $error = "Erro ao fazer upload da imagem. Código: " . $_FILES['foto']['error'];
                }
            }
            
            if (!isset($error)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO veiculos 
                        (marca, modelo, ano, placa, tipo, diaria, status, foto) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    if ($stmt->execute([$marca, $modelo, $ano, $placa, $tipo, $diaria, $status, $foto])) {
                        // Limpa o buffer antes do redirecionamento
                        ob_end_clean();
                        header('Location: listar.php?success=1');
                        exit();
                    }
                } catch (PDOException $e) {
                    // Remove a foto se foi feito upload mas falhou o insert
                    if (!empty($foto) && file_exists($destino)) {
                        unlink($destino);
                    }
                    
                    if ($e->getCode() == 23000) {
                        $error = "Erro: Esta placa já está cadastrada no sistema!";
                    } else {
                        $error = "Erro ao cadastrar veículo: " . $e->getMessage();
                    }
                }
            }
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Cadastrar Novo Veículo</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="form-veiculo">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="marca" class="form-label">Marca</label>
                        <input type="text" class="form-control" id="marca" name="marca" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modelo" class="form-label">Modelo</label>
                        <input type="text" class="form-control" id="modelo" name="modelo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ano" class="form-label">Ano</label>
                        <input type="number" class="form-control" id="ano" name="ano" min="1900" max="<?= date('Y') + 1 ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="placa" class="form-label">Placa</label>
                        <input type="text" class="form-control" id="placa" name="placa" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="hatch">Hatch</option>
                            <option value="sedan">Sedan</option>
                            <option value="suv">SUV</option>
                            <option value="pickup">Pickup</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="diaria" class="form-label">Diária (R$)</label>
                        <input type="number" class="form-control" id="diaria" name="diaria" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="disponivel">Disponível</option>
                            <option value="alugado">Alugado</option>
                            <option value="manutencao">Manutenção</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto do Veículo</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
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

<script>
// Validação da placa antes de enviar o formulário
document.getElementById('form-veiculo').addEventListener('submit', function(e) {
    const placa = document.getElementById('placa').value.trim().toUpperCase();
    document.getElementById('placa').value = placa; // Atualiza o campo com a placa em maiúsculas
    
    if (!placa) {
        e.preventDefault();
        alert('Por favor, informe a placa do veículo!');
    }
});
</script>

<?php include '../../includes/footer.php'; ?>