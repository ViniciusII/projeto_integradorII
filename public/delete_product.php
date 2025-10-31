<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";
require_once "../src/Produto.php";

$db = (new Database())->getConnection();
$produto = new Produto($db);

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID do produto não informado.");
}

try {
    if ($produto->excluir($id, $_SESSION['user_id'])) {
        header("Location: estoque.php?mensagem=Produto excluído com sucesso!");
        exit;
    } else {
        die("Erro ao excluir o produto.");
    }
} catch (Exception $e) {
    die($e->getMessage());
}
