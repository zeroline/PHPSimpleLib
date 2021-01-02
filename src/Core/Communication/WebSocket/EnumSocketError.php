<?php

namespace PHPSimpleLib\Core\Communication\WebSocket;

final class EnumSocketError
{
    /**
     * Network dropped connection because of reset
     */
    public const ENETRESET = 102;

    /**
     * Software caused connection abort
     */
    public const ECONNABORTED = 103;

    /**
     * Connection reset by peer
     */
    public const ECONNRESET = 104;

    /**
     * Cannot send after transport endpoint shutdown --
     * probably more of an error on our part, if we're trying to write
     * after the socket is closed.  Probably not a critical error, though.
     */
    public const ESHUTDOWN = 108;

    /**
     * Connection timed out
     */
    public const ETIMEDOUT = 110;

    /**
     * Connection refused -- We shouldn't see this one,
     * since we're listening... Still not a critical error.
     */
    public const ECONNREFUSED = 111;

    /**
     * Host is down -- Again, we shouldn't see this, and again,
     * not critical because it's just one connection and we still want to listen to/for others.
     */
    public const EHOSTDOWN = 112;

    /**
     * No route to host
     */
    public const EHOSTUNREACH = 113;

    /**
     * Remote I/O error -- Their hard drive just blew up.
     */
    public const EREMOTEIO = 121;

    /**
     * Operation canceled
     */
    public const ECANCELED = 125;
}
