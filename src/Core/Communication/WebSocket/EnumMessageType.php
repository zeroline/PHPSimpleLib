<?php

namespace PHPSimpleLib\Core\Communication\WebSocket;

final class EnumMessageType
{
    public const CONTINUOUS = "continuous";
    public const TEXT = "text";
    public const BINARY = "binary";
    public const CLOSE = "close";
    public const PING = "ping";
    public const PONG = "pong";
}
