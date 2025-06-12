<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

$placa = $_GET['placa'] ?? '';
$stmt = $pdo->prepare("SELECT 1 FROM veiculos WHERE placa = ?");
$stmt->execute([$placa]);

echo json_encode(['existe' => (bool)$stmt->fetch()]);