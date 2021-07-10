<?php
namespace Src\TableGateways;

class PacienteGateway {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $statement = "
            SELECT 
                codigo, nome, sexo 
            FROM 
                pacientes;
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
                codigo, nome, sexo 
            FROM 
                pacientes
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

    public function insert(Array $input)
    {
        $statement = "
            INSERT INTO pacientes 
                (nome, sexo)
            VALUES
                (:nome, :sexo);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'nome' => $input['nome'],
                'sexo'  => $input['sexo'],
            ));
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($codigo, Array $input)
    {
        $statement = "
            UPDATE pacientes 
            SET 
                nome = :nome,
                sexo  = :sexo 
            WHERE codigo = :codigo;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'codigo' => (int) $codigo,
                'nome' => $input['nome'],
                'sexo'  => $input['sexo'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function delete($codigo)
    {
        $statement = "
            DELETE FROM pacientes 
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
