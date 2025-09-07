<?php
class Produto {
    private $conn;
    private $table_name = "produtos";

    public $id;
    public $nome;
    public $descricao;
    public $quantidade;
    public $preco;

    public function __construct($db){
        $this->conn = $db;
    }

    public function listar(){
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function adicionar(){
        $query = "INSERT INTO " . $this->table_name . " SET nome=:nome, descricao=:descricao, quantidade=:quantidade, preco=:preco";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":quantidade", $this->quantidade);
        $stmt->bindParam(":preco", $this->preco);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function atualizarEstoque($id, $nova_quantidade){
        $query = "UPDATE " . $this->table_name . " SET quantidade = :quantidade WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":quantidade", $nova_quantidade);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function pegarProdutoPorId($id){
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
