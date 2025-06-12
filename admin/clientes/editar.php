<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';
protegerPagina();

// PROCESSAMENTO PRIMEIRO — antes de qualquer HTML ou include
if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

// Carrega os dados do cliente
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: listar.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? null;
    $telefone = $_POST['telefone'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $endereco = $_POST['endereco'] ?? '';

    // Validação
    if (empty($nome) || empty($telefone) || empty($cpf)) {
        $error = "Nome, telefone e CPF são obrigatórios!";
    } else {
        // Verifica se já existe outro cliente com o mesmo CPF ou email
        $verifica = $pdo->prepare("SELECT id FROM clientes WHERE (cpf = ? OR email = ?) AND id != ?");
        $verifica->execute([$cpf, $email, $id]);

        if ($verifica->rowCount() > 0) {
            $registro = $verifica->fetch();
            if ($registro['cpf'] === $cpf) {
                $error = "Este CPF já está cadastrado por outro cliente.";
            } elseif ($registro['email'] === $email) {
                $error = "Este email já está cadastrado por outro cliente.";
            }
        } else {
            // Atualiza os dados
            $stmt = $pdo->prepare("
                UPDATE clientes SET 
                nome = ?, email = ?, telefone = ?, cpf = ?, endereco = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$nome, $email, $telefone, $cpf, $endereco, $id])) {
                header('Location: listar.php?success=1');
                exit();
            } else {
                $error = "Erro ao atualizar cliente!";
            }
        }
    }
}
?>

<?php
// SÓ AQUI INCLUI O HTML APÓS O PROCESSAMENTO
$pageTitle = 'Editar Cliente';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Editar Cliente</h5>
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
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cpf" class="form-label">NIF</label>
                        <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($cliente['cpf']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="endereco" class="form-label">Endereço</label>
                        <textarea class="form-control" id="endereco" name="endereco" rows="3"><?= htmlspecialchars($cliente['endereco']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="listar.php" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#telefone').mask('(00) 00000-0000');
    $('#cpf').mask('000.000.000-00');
});
</script>

<?php include '../../includes/footer.php'; ?>
