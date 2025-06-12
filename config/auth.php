<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['cargo']) && $_SESSION['cargo'] === 'admin';
}

function protegerPagina() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function protegerAdmin() {
    protegerPagina();
    if (!isAdmin()) {
        header('Location: ../admin/dashboard.php');
        exit();
    }
}
?>