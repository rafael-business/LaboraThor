<?php
namespace Src\Controller;

use Src\TableGateways\PacienteGateway;

class PacienteController {

    private $db;
    private $requestMethod;
    private $codigo;

    private $pacienteGateway;

    public function __construct($db, $requestMethod, $codigo)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->codigo = $codigo;

        $this->pacienteGateway = new PacienteGateway($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->codigo) {
                    $response = $this->getPaciente($this->codigo);
                } else {
                    $response = $this->getAllPacientes();
                };
                break;
            case 'POST':
                $response = $this->createPacienteFromRequest();
                break;
            case 'PUT':
                $response = $this->updatePacienteFromRequest($this->codigo);
                break;
            case 'DELETE':
                $response = $this->deletePaciente($this->codigo);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllPacientes()
    {
        $result = $this->pacienteGateway->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getPaciente($codigo)
    {
        $result = $this->pacienteGateway->find($codigo);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createPacienteFromRequest()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validatePaciente($input)) {
            return $this->unprocessableEntityResponse();
        }
        $codigo = $this->pacienteGateway->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = $codigo;
        return $response;
    }

    private function updatePacienteFromRequest($codigo)
    {
        $result = $this->pacienteGateway->find($codigo);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validatePaciente($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->pacienteGateway->update($codigo, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function deletePaciente($codigo)
    {
        $result = $this->pacienteGateway->find($codigo);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $this->pacienteGateway->delete($codigo);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validatePaciente($input)
    {
        if (! isset($input['nome'])) {
            return false;
        }
        if (! isset($input['sexo'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}
