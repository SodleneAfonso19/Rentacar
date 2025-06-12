<div class="sidebar">
    <div class="sidebar-header text-center py-4">
        <h4>Renta a Car</h4>
    </div>
    
    <ul class="list-unstyled components">
        <li class="<?= basename($_SERVER['PHP_SELF']) == '../dashboard.php' ? 'active' : '' ?>">
            <a href="../dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        
        <li class="<?= strpos($_SERVER['PHP_SELF'], 'veiculos/') !== false ? 'active' : '' ?>">
            <a href="#veiculosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="bi bi-car-front me-2"></i> Veículos
            </a>
            <ul class="collapse list-unstyled" id="veiculosSubmenu">
                <li>
                    <a href="veiculos/listar.php">Listar</a>
                </li>
                <li>
                    <a href="veiculos/cadastrar.php">Cadastrar</a>
                </li>
            </ul>
        </li>
        
        <li class="<?= strpos($_SERVER['PHP_SELF'], 'clientes/') !== false ? 'active' : '' ?>">
            <a href="#clientesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="bi bi-people me-2"></i> Clientes
            </a>
            <ul class="collapse list-unstyled" id="clientesSubmenu">
                <li>
                    <a href="../clientes/listar.php">Listar</a>
                </li>
                <li>
                    <a href="clientes/cadastrar.php">Cadastrar</a>
                </li>
            </ul>
        </li>
        
        <li class="<?= strpos($_SERVER['PHP_SELF'], 'alugueis/') !== false ? 'active' : '' ?>">
            <a href="#alugueisSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="bi bi-file-earmark-text me-2"></i> Aluguéis
            </a>
            <ul class="collapse list-unstyled" id="alugueisSubmenu">
                <li>
                    <a href="../alugueis/listar.php">Listar</a>
                </li>
                <li>
                    <a href="../alugueis/cadastrar.php">Cadastrar</a>
                </li>
            </ul>
        </li>
        
        <?php if (isAdmin()): ?>
        <li class="<?= strpos($_SERVER['PHP_SELF'], 'funcionarios/') !== false ? 'active' : '' ?>">
            <a href="#funcionariosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="bi bi-person-badge me-2"></i> Funcionários
            </a>
            <ul class="collapse list-unstyled" id="funcionariosSubmenu">
                <li>
                    <a href="../funcionarios/listar.php">Listar</a>
                </li>
                <li>
                    <a href="../funcionarios/cadastrar.php">Cadastrar</a>
                </li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>
</div>