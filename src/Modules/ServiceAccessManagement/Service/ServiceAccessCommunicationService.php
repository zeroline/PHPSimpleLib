<?php

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Service;

use PHPSimpleLib\Modules\ServiceAccessManagement\Model\ServiceAccessModel;
use PHPSimpleLib\Modules\ServiceAccessManagement\Service\ServiceAccessService;
use PHPSimpleLib\Core\Security\JWT\JWT;
use PHPSimpleLib\Core\Security\JWT\JWTManager;
use PHPSimpleLib\Core\Security\HMAC;
use PHPSimpleLib\Core\Communication\EnumHTTPVerbs;

final class ServiceAccessCommunicationService
{
    public const JWT_DATA_FIELD_NAME = "payloadAdditionalData";
    public const HEADER_APP_KEY = 'X-APP-KEY';
    public const HEADER_HASH = 'X-HASH';
    public const HEADER_IV = 'X-IV';

    /**
     * Generate a JWT string with the given data.
     * Used for request and response
     *
     * @param string $appKey
     * @param string $appSecret
     * @param mixed $data
     * @return void
     */
    public static function createMessageDataJWTString(string $appKey, string $appSecret, $data = null)
    {
        $jwt = JWTManager::createJWTWithPayload(array());
        $jwt->setIssuer($appKey);
        $jwt->setIssuedAt(time());
        $jwt->{self::JWT_DATA_FIELD_NAME} = $data;
        return JWTManager::stringFromJWT($jwt, $appSecret);
    }

    /**
     * Extract the JWT from the given request
     *
     * @param string $appKey
     * @param string $appSecret
     * @param string $requestString
     * @return JWT|null
     */
    public static function extractJWTFromMessageString(string $appKey, string $appSecret, string $requestString): ?JWT
    {
        $jwt = JWTManager::jwtFromString($requestString, $appSecret);
        if ($jwt) {
            if ($jwt->getIssuer() != $appKey) {
                throw new \Exception('Issuer missmatch!');
            }
            return $jwt;
        }
        return null;
    }

    /**
     * Checks if the send hash is valid
     *
     * @param string $requestHash
     * @param ServiceAccessModel $serviceAccess
     * @return boolean
     */
    public static function isRequestValid(string $requestHash, string $requestIV, ServiceAccessModel $serviceAccess): bool
    {
        return (base64_decode($requestHash) == HMAC::sign($serviceAccess->getAppKey() . $requestIV, $serviceAccess->getAppSecret()));
    }

    public static function sendAppHttpRequest(string $appKey, string $appSecret, string $requestType, string $uri, $data = null, $headerData = array())
    {
        $ch = curl_init();

        $iv = base64_encode(openssl_random_pseudo_bytes(32));

        $header = array(
            self::HEADER_APP_KEY . ': ' . $appKey,
            self::HEADER_HASH . ': ' . base64_encode(HMAC::sign($appKey . $iv, $appSecret)),
            self::HEADER_IV . ': ' . $iv
        );

        foreach ($headerData as $key => $value) {
            $header[] = $key . ': ' . $value;
        }

        if (
            in_array($requestType, array(
            EnumHTTPVerbs::HTTP_VERB_POST,
            EnumHTTPVerbs::HTTP_VERB_PUT,
            EnumHTTPVerbs::HTTP_VERB_DELETE,
            EnumHTTPVerbs::HTTP_VERB_PATCH))
        ) {
            $dataJWTString = self::createMessageDataJWTString($appKey, $appSecret, $data);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJWTString);
            $header[] = 'Content-Type: text/plain; charset="utf-8"';
            $header[] = 'Content-Transfer-Encoding: base64';
            $header[] = 'Content-Length: ' . strlen($dataJWTString);
        } elseif ($requestType === EnumHTTPVerbs::HTTP_VERB_GET) {
            if ($data) {
                $uri .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
            }
        } else {
            throw new \Exception('Invalid request type "' . $requestType . '".');
        }

        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $curlResult = curl_exec($ch);

        if (($errorMessage = curl_error($ch))) {
            throw new \Exception(__FUNCTION__ . ' curl error "' . $errorMessage . '"');
        }

        switch ($httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE)) {
            case 403: # Access denied
                throw new \Exception('Request responded with 403 "access denied"');
            case 404: # Not found
                throw new \Exception('Request responded with 404 "not found"');
            case 500: # Error
                throw new \Exception('Request responded with 500 "error"');
            default:
            case 200: # OK
            case 201: # Created
            case 422: # Invalid data
                // There should be a data response in the body - handle it
                break;
        }

        $returnValue = null;

        switch ($responseContentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE)) {
            case 'text/plain':
            case 'text/plain; charset=utf-8':
                // Expecting a jwt string
                $jwt = self::extractJWTFromMessageString($appKey, $appSecret, $curlResult);
                $returnValue = $jwt->{self::JWT_DATA_FIELD_NAME};
                break;
            case 'application/json':
            case 'application/json; charset=utf-8':
                // Expecting standard json in plain text
                $returnValue = json_decode($curlResult);
                break;
            default:
                throw new \Exception('Unexpected content type "' . $responseContentType . '" in response');
        }

        curl_close($ch);

        return $returnValue;
    }
}
