// Toggle sidebar
document.getElementById('sidebarToggleTop').addEventListener('click', function() {
    document.body.classList.toggle('sidebar-toggled');
    document.querySelector('.sidebar').classList.toggle('toggled');
    
    if (document.querySelector('.sidebar').classList.contains('toggled')) {
        document.querySelector('.sidebar .collapse').classList.remove('show');
    }
});

// Fechar menu quando item for clicado (em telas pequenas)
const sidebarLinks = document.querySelectorAll('.sidebar a:not(.dropdown-toggle)');
sidebarLinks.forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth < 768) {
            document.body.classList.remove('sidebar-toggled');
            document.querySelector('.sidebar').classList.remove('toggled');
        }
    });
});

// Ativar tooltips
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});