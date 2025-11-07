<?php
class Produto {
    private $conn;

    public $id;
    public $nome;
    public $descricao;
    public $quantidade;
    public $preco;
    public $user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listarPorUsuario($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM produtos WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$user_id]);
        return $stmt;
    }

    public function pegarProdutoPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function adicionar($user_id) {
        if (empty(trim($this->nome))) {
            throw new Exception("O campo 'Nome' é obrigatório.");
        }
        if (empty($this->descricao)) {
            $this->descricao = "";
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO produtos (nome, descricao, quantidade, preco, user_id) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $this->nome,
            $this->descricao,
            $this->quantidade,
            $this->preco,
            $user_id
        ]);
    }

    public function atualizar($id) {
        if (empty(trim($this->nome))) {
            throw new Exception("O campo 'Nome' é obrigatório.");
        }
        if (empty($this->descricao)) {
            $this->descricao = "";
        }

        $stmt = $this->conn->prepare(
            "UPDATE produtos SET nome = ?, descricao = ?, quantidade = ?, preco = ? WHERE id = ?"
        );
        return $stmt->execute([
            $this->nome,
            $this->descricao,
            $this->quantidade,
            $this->preco,
            $id
        ]);
    }

    public function atualizarEstoque($id, $nova_quantidade) {
        if (!is_numeric($nova_quantidade) || $nova_quantidade < 0) {
            throw new Exception("Quantidade inválida.");
        }
        $stmt = $this->conn->prepare(
            "UPDATE produtos SET quantidade = ? WHERE id = ?"
        );
        return $stmt->execute([$nova_quantidade, $id]);
    }

    public function excluir($id, $user_id) {
        $produto = $this->pegarProdutoPorId($id);
        if (!$produto) {
            throw new Exception("Produto não encontrado.");
        }
        if ($produto['user_id'] != $user_id) {
            throw new Exception("Você não tem permissão para excluir este produto.");
        }

        $stmt = $this->conn->prepare("DELETE FROM produtos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
