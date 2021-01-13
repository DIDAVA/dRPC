<?php

class dRPC {
  public $response;
  public $request;

  public function __construct() {
    $this->response = (object) [
      'jsonrpc' => '2.0'
    ];
    $input = file_get_contents('php://input');
    $json = json_decode($input);
    if (empty($input) || empty($json)) $this->setError( 'Parse error', -32700 );
    if (!property_exists($json, 'jsonrpc') || $json->jsonrpc != '2.0') $this->setError( 'Invalid version', -32600 );
    if (!property_exists($json, 'id') || empty($json->id)) $this->setError( 'Invalid ID', -32600 );
    $this->response->id = $json->id;
    $this->request = $json;
  }

  public function getMethod() {
    if (!property_exists($this->request, 'method') || empty($this->request->method)) $this->methodNotFound();
    return $this->request->method;
  }

  public function getParams( $params = [] ) {
    if (empty($this->request->params)) $this->setError( 'Invalid params', -32602 );
    if (count($params) > 0) {
      foreach ($params as $param) {
        if (!property_exists($this->request->params, $param)) $this->invalidMethodParams("$param is required");
      }
    }
    return $this->request->params;
  }

  public function methodNotFound() {
    $this->setError('Method not found', -32601);
  }

  public function invalidMethodParams($message = null) {
    $this->setError($message, -32602);
  }

  public function setError($message, $code, $data = null) {
    $this->response->error = (object) [
      'code' => $code,
      'message' => $message
    ];
    if (!empty($data)) $this->response->error->data = $data;
    $this->export();
  }

  public function setResult($result) {
    $this->response->result = $result;
    $this->export();
  }

  private function export() {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode( $this->response );
    exit();
  }
 
}
