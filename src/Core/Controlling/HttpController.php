<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Controlling\Controller;
use PHPSimpleLib\Helper\Autoloader;
use PHPSimpleLib\Helper\Renderer;
use PHPSimpleLib\Core\Data\DataContainer;

class HttpController extends Controller
{
    const CONTENT_TYPE_HTML = 'text/html';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_TEXT_PLAIN_JWT = 'text/plain';

    const JSON_META_RESPONSE_KEYWORD = '_meta';

    const VIEW_FOLDER_NAME = 'View';
    const VIEW_ASSET_FOLDER_NAME = "Assets";
    const VIEW_ASSET_VAR_NAME = 'assetFolder';
    const VIEW_FOLDER_VAR_NAME = 'viewFolder';
    const VIEW_FILE_EXTENSION = '.php';

    /**
     * Set the HTTP response code on response
     *
     * @var boolean
     */
    protected $useHTTPResponseCode = true;

    /**
     * See function name
     *
     * @return void
     */
    public function overrideSendingAValidHTTPResponseCodeBecauseTheFetchAPIIsBullshitAndCORSIsAPainInTheAss() : void
    {
        $this->useHTTPResponseCode = false;
    }

    /**
     * Return a get variable
     *
     * @param string $key
     * @param mixed $fallback
     * @return void
     */
    public function get(string $key, $fallback = null)
    {
        if (isset($_GET) && array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }
        return $fallback;
    }
    
    /**
     * Returns a post variable
     *
     * @param string $key
     * @param mixed $fallback
     * @return mixed
     */
    public function post(string $key, $fallback = null)
    {
        if (isset($_POST) && array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }
        return $fallback;
    }
    
    /**
     * Returns the plain request body
     *
     * @return string
     */
    public function body() : string
    {
        return file_get_contents('php://input');
    }
    
    /**
     * Returns the interpreted json request body.
     * If the json is invalid an exception will be thrown.
     *
     * @return mixed
     */
    public function jsonBody()
    {
        $result = json_decode($this->body());
        if (is_null($result)) {
            throw new \Exception('Invalid JSON body.');
        }
        return $result;
    }
    
    /**
     * Returns the request json body as a DataContainer object.
     * If the json is invalid an exception will be thrown within
     * the jsonBody function.
     *
     * @return DataContainer
     */
    public function getBodyDataContainer() : DataContainer
    {
        return new DataContainer($this->jsonBody());
    }

    /**
     * 
     * @param string $key 
     * @return mixed 
     */
    public function getHeader(string $key) {
        if (isset($_SERVER) && array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }
        return null;
    }
    
    /**
     * Return a custom header
     * Auto-replaces - with _, ups all letters and
     * prefixes with HTTP_
     *
     * @param string $key
     * @return mixed
     */
    public function customHeader(string $key)
    {
        //auto prepend HTTP
        $key = str_replace('-', '_', strtoupper('HTTP_' . $key));

        if (isset($_SERVER) && array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }
        return null;
    }

    /**
     * Redirect the http request to another location
     *
     * @param string $location
     * @param integer $statusCode
     * @return void
     */
    public function redirect(string $location, int $statusCode = 303)
    {
        header('Location: ' . $location, true, $statusCode);
        die();
    }

    /**
     * Overrids the content header with the given constant type
     *
     * @param string $type
     * @return void
     */
    public function contentHeader(string $type = self::CONTENT_TYPE_HTML) : void
    {
        switch ($type) {
            case self::CONTENT_TYPE_JSON:
                header("Content-Type: application/json; charset=utf-8", true);
                break;
            case self::CONTENT_TYPE_TEXT_PLAIN_JWT:
                header("Content-Type: text/plain; charset=utf-8", true);
                header("Content-Transfer-Encoding: base64", true);
                break;
            case self::CONTENT_TYPE_HTML:
            default:
                header("Content-Type: text/html; charset=utf-8", true);
        }
    }

    /**
     * Generates and loads a view. The name is automaticly assumed
     * by the called method. Beware to choose unique view action names
     * within one module!
     *
     * @param array $data
     * @param integer $code
     * @return string
     */
    public function view(array $data = array(), int $code = 200) : string
    {
        if ($this->useHTTPResponseCode) {
            http_response_code($code);
        }
        
        $this->contentHeader(self::CONTENT_TYPE_HTML);

        $assetFolder = Autoloader::classToDirectory(get_called_class()) . '..' . DIRECTORY_SEPARATOR . self::VIEW_ASSET_FOLDER_NAME . DIRECTORY_SEPARATOR;
        $data[self::VIEW_ASSET_VAR_NAME] = $assetFolder;

        $viewFolder = Autoloader::classToDirectory(get_called_class()) . '..' . DIRECTORY_SEPARATOR . self::VIEW_FOLDER_NAME . DIRECTORY_SEPARATOR;
        $data[self::VIEW_FOLDER_VAR_NAME] = $viewFolder;

        $viewFile = $viewFolder . $this->getSimplifiedControllerName() . DIRECTORY_SEPARATOR . $this->methodToCall . self::VIEW_FILE_EXTENSION;
        
        if (file_exists($viewFile)) {
            return Renderer::renderFile($viewFile, $data);
        } else {
            throw new \Exception('View file "' . $viewFile . '" not found.');
        }
    }
    
    /**
     * Generic json response
     * Switches the content type to json
     * Set the given HTTP response code
     *
     * @param array $data
     * @param boolean $success
     * @param string $message
     * @param integer $code
     * @return string
     */
    public function response($data, bool $success, string $message, int $code) : string
    {
        if ($this->useHTTPResponseCode) {
            http_response_code($code);
        }
        $this->contentHeader(self::CONTENT_TYPE_JSON);

        /*
        $response = array_reverse(array_merge(array(self::JSON_META_RESPONSE_KEYWORD => array(
                'success' => (boolean)$success,
                'error' => (boolean)!$success,
                'message' => $message,
                'statusCode' => $code,
                'timestamp' => time(),
                'datetime' => date('Y-m-d H:i:s')
        )), (array)json_decode(json_encode($data))));
        */
        $response = HttpResponseBuilder::buildBasicResponseArray($data, $success, $message, $code);

        return json_encode($response);
    }

    /**
     * Standard json success response message
     * HTTP code 200
     *
     * @param array $data
     * @param string $message
     * @param integer $code
     * @return string
     */
    public function responseSuccess($data = array(), string $message = '', int $code = 200) : string
    {
        return $this->response($data, true, $message, $code);
    }

    /**
     * Standard json error response message
     * HTTP code 500
     *
     * @param array $data
     * @param string $message
     * @param integer $code
     * @return string
     */
    public function responseError($data = array(), string $message = '', int $code = 500) : string
    {
        return $this->response($data, false, $message, $code);
    }

    /**
     * Standard json error response message
     * HTTP code 403
     *
     * @param string $message
     * @param integer $code
     * @return string
     */
    public function responseAccessDenied(string $message = 'Access denied', int $code = 403) : string
    {
        return $this->response(array(), false, $message, $code);
    }

    /**
     * Standard json error response message
     * HTTP code 404
     *
     * @param string $message
     * @param integer $code
     * @return string
     */
    public function responseNotFound(string $message = 'Rest endpoint not found', int $code = 404) : string
    {
        return $this->response(array(), false, $message, $code);
    }

    /**
     * Standard json error response message
     * HTTP code 422
     *
     * @param array $validationErrors
     * @param array $data
     * @param string $message
     * @param integer $code
     * @return string
     */
    public function responseInvalidData(array $validationErrors = array(), array $data = array(), string $message = 'Invalid input', int $code = 422) : string
    {
        return $this->response(array_merge(
            array(
                'validationErrors' => $validationErrors
            ),
            $data
        ), false, $message, $code);
    }
}
