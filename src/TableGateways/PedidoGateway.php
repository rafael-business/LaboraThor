<?php
namespace Src\TableGateways;

class PedidoGateway {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $statement = "
            SELECT 
                ordem_servico, codigo, paciente_codigo 
            FROM 
                pedidos;
        ";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($ordem_servico)
    {
        $statement = "
            SELECT 
                ordem_servico, codigo, paciente_codigo 
            FROM 
                pedidos
            WHERE ordem_servico = ?;
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
            INSERT INTO pedidos 
                (codigo, paciente_codigo)
            VALUES
                (:codigo, :paciente_codigo);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'codigo' => $input['codigo'],
                'paciente_codigo'  => $input['paciente_codigo'],
            ));
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($ordem_servico, Array $input)
    {
        $statement = "
            UPDATE pedidos 
            SET 
                codigo = :codigo,
                paciente_codigo  = :paciente_codigo 
            WHERE ordem_servico = :ordem_servico;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'ordem_servico' => (int) $ordem_servico,
                'codigo' => $input['codigo'],
                'paciente_codigo' => $input['paciente_codigo'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function delete($ordem_servico)
    {
        $statement = "
            DELETE FROM pedidos 
            WHERE ordem_servico = :ordem_servico;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('ordem_servico' => $ordem_servico));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }
}
