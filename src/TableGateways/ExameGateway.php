<?php
namespace Src\TableGateways;

class ExameGateway {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $statement = "
            SELECT 
                codigo, descricao 
            FROM 
                exames;
        ";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($codigo_pk)
    {
        $statement = "
            SELECT 
                codigo, descricao 
            FROM 
                exames
            WHERE codigo_pk = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($codigo_pk));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function findByCodigo($codigo)
    {
        $statement = "
            SELECT 
                codigo_pk, descricao 
            FROM 
                exames
            WHERE codigo = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($codigo));
            $result = $statement->fetchAll();
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function insert(Array $input)
    {
        $statement = "
            INSERT INTO exames 
                (codigo, descricao)
            VALUES
                (:codigo, :descricao);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'codigo' => $input['codigo'],
                'descricao'  => $input['descricao'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($codigo_pk, Array $input)
    {
        $statement = "
            UPDATE exames 
            SET 
                codigo = :codigo,
                descricao  = :descricao 
            WHERE codigo_pk = :codigo_pk;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'codigo_pk' => (int) $codigo_pk,
                'codigo' => $input['codigo'],
                'descricao'  => $input['descricao'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function delete($codigo_pk)
    {
        $statement = "
            DELETE FROM exames 
            WHERE codigo_pk = :codigo_pk;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('codigo_pk' => $codigo_pk));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }
}
