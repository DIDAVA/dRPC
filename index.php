<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header('Access-Control-Allow-Origin: '.$origin);

try {
  if (empty($_SERVER['HTTP_ORIGIN']) || $_SERVER['HTTP_ORIGIN'] != $origin) throw new Exception("Request only allowed from $origin", 403);
  if (!in_array($_SERVER['REQUEST_METHOD'], ['OPTIONS', 'POST'])) throw new Exception("Invalid request method", 405);
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Methods: OPTIONS, POST');
    header('Access-Control-Allow-Headers: X-PINGARUNER, Content-Type');
    header('Access-Control-Max-Age: 86400');
    header("Content-Length: 0");
  }
  else {
    include './class/dRPC.php';
    $rpc = new dRPC();
    $rpc->setResult([
      'method' => $rpc->getMethod(),
      'params' => $rpc->getParams([ 'user', 'pass' ])
    ]);
  }
}
catch (Exception $e) {
  http_response_code( $e->getCode() );
  echo $e->getMessage();
  exit();
}