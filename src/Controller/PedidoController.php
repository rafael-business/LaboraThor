<?php
namespace Src\Controller;

use Src\TableGateways\PedidoGateway;
use Src\TableGateways\PacienteGateway;
use Src\TableGateways\PedidoExamesGateway;
use Src\TableGateways\ExameGateway;

class PedidoController {

    private $db;
    private $requestMethod;
    private $ordem_servico;

    private $pedidoGateway;
    private $pacienteGateway;
    private $pedidoExamesGateway;
    private $exameGateway;

    public function __construct($db, $requestMethod, $ordem_servico)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->ordem_servico = $ordem_servico;

        $this->pedidoGateway = new PedidoGateway($db);
        $this->pacienteGateway = new PacienteGateway($db);
        $this->pedidoExamesGateway = new PedidoExamesGateway($db);
        $this->exameGateway = new ExameGateway($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->ordem_servico) {
                    $response = $this->getPedido($this->ordem_servico);
                } else {
                    $response = $this->getAllPedidos();
                };
                break;
            case 'POST':
                $response = $this->createPedidoFromRequest();
                break;
            case 'PUT':
                $response = $this->updatePedidoFromRequest($this->ordem_servico);
                break;
            case 'DELETE':
                $response = $this->deletePedido($this->ordem_servico);
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

    private function getAllPedidos()
    {
        $pedidos = $this->pedidoGateway->findAll();
        $i = 0;
        foreach ($pedidos as $pedido) {
            $paciente_codigo = $pedido['paciente_codigo'];
            $paciente = $this->pacienteGateway->find($paciente_codigo)[0];
            $pedidos[$i]['paciente_nome'] = $paciente['nome'];
            $pedidos[$i]['paciente_sexo'] = $paciente['sexo'];
            $pedido_exames = $this->pedidoExamesGateway->findByOrdemServico($pedido['ordem_servico']);
            $j = 0;
            foreach ($pedido_exames as $exame) {
                $pedidos[$i]['exames'][$j]['exame_codigo'] = $exame['exame_codigo'];
                $ex = $this->exameGateway->findByCodigo($exame['exame_codigo'])[0];
                $pedidos[$i]['exames'][$j]['exame_descricao'] = $ex['descricao'];
                $j++;
            }
            $i++;
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($pedidos);
        return $response;
    }

    private function getPedido($ordem_servico)
    {
        $pedido = $this->pedidoGateway->find($ordem_servico);
        if (! $pedido) {
            return $this->notFoundResponse();
        }
        $paciente_codigo = $pedido[0]['paciente_codigo'];
        $paciente = $this->pacienteGateway->find($paciente_codigo)[0];
        $pedido[0]['paciente_nome'] = $paciente['nome'];
        $pedido[0]['paciente_sexo'] = $paciente['sexo'];
        $pedido_exames = $this->pedidoExamesGateway->findByOrdemServico($pedido[0]['ordem_servico']);
        $i = 0;
        foreach ($pedido_exames as $exame) {
            $pedido[0]['exames'][$i]['exame_codigo'] = $exame['exame_codigo'];
            $ex = $this->exameGateway->findByCodigo($exame['exame_codigo'])[0];
            $pedido[0]['exames'][$i]['exame_descricao'] = $ex['descricao'];
            $i++;
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($pedido);
        return $response;
    }

    private function createPedidoFromRequest()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validatePedido($input)) {
            return $this->unprocessableEntityResponse();
        }
        $id = $input['identificador'];
        unset($input['identificador']);
        $ordem_servico = $this->pedidoGateway->insert($input);
        $codigo = base64_encode($id.$ordem_servico);
        $this->pedidoGateway->update($ordem_servico,array( 
            'codigo' => $codigo, 
            'paciente_codigo' => $input['paciente_codigo'] 
        ));
        $pedido_exames['pedido_ordem_servico'] = $ordem_servico;
        $exames = $input['exames'];
        unset($input['exames']);
        foreach ($exames as $exame) {
            $pedido_exames['exame_codigo'] = $exame['codigo'];
            $this->pedidoExamesGateway->insert($pedido_exames);
        }
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = $ordem_servico;
        return $response;
    }

    private function updatePedidoFromRequest($ordem_servico)
    {
        $result = $this->pedidoGateway->find($ordem_servico);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validatePedido($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->pedidoGateway->update($ordem_servico, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function deletePedido($ordem_servico)
    {
        $result = $this->pedidoGateway->find($ordem_servico);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $this->pedidoGateway->delete($ordem_servico);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validatePedido($input)
    {
        if (! isset($input['identificador'])) {
            return false;
        }
        if (! isset($input['paciente_codigo'])) {

            // TODO: Validar a chave secundária
            return false;
        }
        if (! isset($input['exames']) || empty($input['exames'])) {
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
