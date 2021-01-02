<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Communication\Rest\AsyncCurl;

use PHPSimpleLib\Core\Communication\EnumHTTPVerbs;

class CurlJob
{

    /**
     *
     * @var string
     */
    private $url = null;
    
    /**
     *
     * @var array
     */
    private $parameter = array();
    
    /**
     *
     * @var string {@see EnumHTTPVerbs }
     */
    private $method = null;
    
    /**
     *
     * @var mixed
     */
    private $result = null;
    
    /**
     *
     * @var mixed
     */
    private $additionalInfo = null;
    
    /**
     *
     * @param string $url
     * @param array $parameter
     * @param string $method {@see EnumHTTPVerbs }
     */
    public function __construct(string $url, array $parameter = array(), $method = EnumHTTPVerbs::HTTP_VERB_GET, $additionalInfo = null)
    {
        $this->url = $url;
        $this->parameter = $parameter;
        $this->method = $method;
        $this->additionalInfo = $additionalInfo;
    }
    
    /**
     *
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }
    
    /**
     *
     * @return array
     */
    public function getParameter() : array
    {
        return $this->parameter;
    }
    
    /**
     *
     * @return string {@see EnumHTTPVerbs }
     */
    public function getMethod() : string
    {
        return $this->method;
    }
    
    /**
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     *
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
    
    /**
     *
     * @return mixed
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }
}
