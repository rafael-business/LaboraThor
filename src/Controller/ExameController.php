<?php
namespace Src\Controller;

use Src\TableGateways\ExameGateway;

class ExameController {

    private $db;
    private $requestMethod;
    private $codigo;

    private $exameGateway;

    public function __construct($db, $requestMethod, $codigo)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->codigo = $codigo;

        $this->exameGateway = new ExameGateway($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->codigo) {
                    $response = $this->getExame($this->codigo);
                } else {
                    $response = $this->getAllExames();
                };
                break;
            case 'POST':
                $response = $this->createExameFromRequest();
                break;
            case 'PUT':
                $response = $this->updateExameFromRequest($this->codigo);
                break;
            case 'DELETE':
                $response = $this->deleteExame($this->codigo);
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

    private function getAllExames()
    {
        $result = $this->exameGateway->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getExame($codigo)
    {
        $result = $this->exameGateway->find($codigo);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createExameFromRequest()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateExame($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->exameGateway->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }

    private function updateExameFromRequest($codigo)
    {
        $result = $this->exameGateway->find($codigo);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateExame($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->exameGateway->update($codigo, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function deleteExame($codigo)
    {
        $result = $this->exameGateway->find($codigo);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $this->exameGateway->delete($codigo);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validateExame($input)
    {
        if (! isset($input['codigo'])) {

            // TODO: Validação unique
            return false;
        }
        if (! isset($input['descricao'])) {
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
