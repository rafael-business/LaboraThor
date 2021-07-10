<?php
namespace Src\TableGateways;

class PedidoExamesGateway {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $statement = "
            SELECT 
                codigo, pedido_ordem_servico, exame_codigo 
            FROM 
                pedido_exames;
        ";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($codigo)
    {
        $statement = "
            SELECT 
                codigo, pedido_ordem_servico, exame_codigo 
            FROM 
                pedido_exames
            WHERE codigo = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($codigo));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function findByOrdemServico($ordem_servico)
    {
        $statement = "
            SELECT 
                codigo, pedido_ordem_servico, exame_codigo 
            FROM 
                pedido_exames
            WHERE pedido_ordem_servico = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($ordem_servico));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function insert(Array $input)
    {
        $statement = "
            INSERT INTO pedido_exames 
                (pedido_ordem_servico, exame_codigo)
            VALUES
                (:pedido_ordem_servico, :exame_codigo);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'pedido_ordem_servico'  => $input['pedido_ordem_servico'],
                'exame_codigo'  => $input['exame_codigo'],
            ));
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($codigo, Array $input)
    {
        $statement = "
            UPDATE pedido_exames 
            SET 
                pedido_ordem_servico  = :pedido_ordem_servico, 
                exame_codigo  = :exame_codigo 
            WHERE codigo = :codigo;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'codigo' => (int) $codigo,
                'pedido_ordem_servico'  => $input['pedido_ordem_servico'],
                'exame_codigo'  => $input['exame_codigo'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function delete($codigo)
    {
        $statement = "
            DELETE FROM pedido_exames 
            WHERE codigo = :codigo;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('codigo' => $codigo));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }
}
