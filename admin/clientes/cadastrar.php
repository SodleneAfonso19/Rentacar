<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

$pageTitle = 'Cadastrar Cliente';

// PROCESSAMENTO DE FORMULÁRIO DEVE VIR ANTES DE QUALQUER HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? null;
    $telefone = $_POST['telefone'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $endereco = $_POST['endereco'] ?? '';

    // Verificar duplicados
    $check = $pdo->prepare("SELECT * FROM clientes WHERE cpf = ? OR email = ?");
    $check->execute([$cpf, $email]);

    if ($check->rowCount() > 0) {
        $registroExistente = $check->fetch();
        if ($registroExistente['cpf'] == $cpf) {
            $error = "Este CPF já está cadastrado!";
        } elseif ($registroExistente['email'] == $email) {
            $error = "Este e-mail já está cadastrado!";
        }
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO clientes 
            (nome, email, telefone, cpf, endereco) 
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([$nome, $email, $telefone, $cpf, $endereco])) {
            header('Location: listar.php?success=1');
            exit();
        } else {
            $error = "Erro ao cadastrar cliente!";
        }
    }
}

// AGORA SIM, depois do processamento, carrega o cabeçalho
include '../../includes/header.php';
?>


<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Cadastrar Novo Cliente</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
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
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cpf" class="form-label">NFI</label>
                        <input type="text" class="form-control" id="cpf" name="cpf" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="endereco" class="form-label">Endereço</label>
                        <textarea class="form-control" id="endereco" name="endereco" rows="3"></textarea>
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
// Máscaras para os campos
$(document).ready(function(){
    $('#telefone').mask('(00) 00000-0000');
    $('#cpf').mask('000.000.000-00');
});
</script>

<?php include '../../includes/footer.php'; ?>