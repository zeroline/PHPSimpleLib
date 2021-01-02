<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Communication;

/**
 * Enumeration for HTTP verbs used p.e for REST communication.
 * Comments indicates the verbs standard usage.
 * Provided by https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
 */
final class EnumHTTPVerbs
{
    /**
     * The POST method is used to submit an entity to the specified resource, often causing a change in state or side effects on the server
     */
    public const HTTP_VERB_POST = "POST";
/**
     * The PUT method replaces all current representations of the target resource with the request payload.
     */
    public const HTTP_VERB_PUT = "PUT";
/**
     * The GET method requests a representation of the specified resource. Requests using GET should only retrieve data.
     */
    public const HTTP_VERB_GET = "GET";
/**
     * The DELETE method deletes the specified resource.
     */
    public const HTTP_VERB_DELETE = "DELETE";
/**
     * The HEAD method asks for a response identical to that of a GET request, but without the response body.
     */
    public const HTTP_VERB_HEAD = "HEAD";
/**
     * The CONNECT method establishes a tunnel to the server identified by the target resource.
     */
    public const HTTP_VERB_CONNECT = "CONNECT";
/**
     * The OPTIONS method is used to describe the communication options for the target resource.
     */
    public const HTTP_VERB_OPTIONS = "OPTIONS";
/**
     * The TRACE method performs a message loop-back test along the path to the target resource.
     */
    public const HTTP_VERB_TRACE = "TRACE";
/**
     * The PATCH method is used to apply partial modifications to a resource.
     */
    public const HTTP_VERB_PATCH = "PATCH";
}
