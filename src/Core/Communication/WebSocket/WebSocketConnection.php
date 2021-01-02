<?php

namespace PHPSimpleLib\Core\Communication\WebSocket;

class WebSocketConnection
{
    public $socket = null;
    public $id = null;
    public $headers = array();
    public $handshake = false;
    public $handlingPartialPacket = false;
    public $partialBuffer = "";
    public $sendingContinuous = false;
    public $partialMessage = "";

    public $hasSentClose = false;
    public function __construct($id, $socket)
    {
        $this->id = $id;
        $this->socket = $socket;
    }
}
