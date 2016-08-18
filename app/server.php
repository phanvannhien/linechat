<?php

require_once(DIR.'/../vendor/autoload.php');
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Http\Controllers\LineChatController;

$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      new LineChatController()
    )
  ),
  8181
);
echo "Websocket server is running. Press ctrl-c to exit...\r\n";
$server->run();