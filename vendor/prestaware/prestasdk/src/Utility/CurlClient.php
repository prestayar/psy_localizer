<?php
/**
 * CurlClient class provides a standardized wrapper around cURL requests.
 *
 * @author PrestaWare
 */
namespace PrestaSDK\V071\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CurlClient
{
    /**
     * @var int
     */
    protected $timeout;

    /**
     * @var array
     */
    protected $defaultHeaders;

    /**
     * @var bool
     */
    protected $verifyPeer;

    /**
     * @param int $timeout
     * @param array $defaultHeaders
     * @param bool $verifyPeer
     */
    public function __construct($timeout = 10, array $defaultHeaders = [], $verifyPeer = true)
    {
        $this->timeout = (int) $timeout;
        $this->defaultHeaders = $defaultHeaders;
        $this->verifyPeer = (bool) $verifyPeer;
    }

    /**
     * Execute an HTTP request and return a standardized response payload.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     *
     * @return array
     */
    public function request($method, $url, array $options = [])
    {
        $result = [
            'success' => false,
            'status_code' => 0,
            'body' => null,
            'headers' => [],
            'error' => null,
            'info' => [],
        ];

        if (!function_exists('curl_init')) {
            $result['error'] = [
                'code' => 0,
                'message' => 'cURL extension is not available.',
                'type' => 'extension_missing',
            ];

            return $result;
        }

        $method = strtoupper($method);
        $handle = curl_init();

        if (!$handle) {
            $result['error'] = [
                'code' => 0,
                'message' => 'Unable to initialize cURL session.',
                'type' => 'initialization',
            ];

            return $result;
        }

        $timeout = isset($options['timeout']) ? (int) $options['timeout'] : $this->timeout;
        $connectTimeout = isset($options['connect_timeout']) ? (int) $options['connect_timeout'] : $timeout;

        $headers = array_merge(
            $this->prepareHeaders($this->defaultHeaders),
            $this->prepareHeaders(isset($options['headers']) ? $options['headers'] : [])
        );

        $requestOptions = [
            CURLOPT_URL => $this->applyQuery($url, isset($options['query']) ? $options['query'] : []),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => isset($options['follow_location']) ? (bool) $options['follow_location'] : true,
            CURLOPT_MAXREDIRS => isset($options['max_redirs']) ? (int) $options['max_redirs'] : 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_SSL_VERIFYPEER => isset($options['verify_peer']) ? (bool) $options['verify_peer'] : $this->verifyPeer,
            CURLOPT_SSL_VERIFYHOST => isset($options['verify_host']) ? (int) $options['verify_host'] : ($this->verifyPeer ? 2 : 0),
            CURLOPT_USERAGENT => isset($options['user_agent']) ? $options['user_agent'] : 'PrestaSDK-CurlClient/0.6',
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        $requestOptions[CURLOPT_HTTPHEADER] = $headers;

        if (isset($options['body'])) {
            $requestOptions[CURLOPT_POSTFIELDS] = $options['body'];
        } elseif (isset($options['json'])) {
            $requestOptions[CURLOPT_POSTFIELDS] = json_encode($options['json']);
            $requestOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        } elseif (isset($options['form_params'])) {
            $requestOptions[CURLOPT_POSTFIELDS] = http_build_query($options['form_params']);
            $requestOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
        }

        if (empty($requestOptions[CURLOPT_HTTPHEADER])) {
            unset($requestOptions[CURLOPT_HTTPHEADER]);
        }

        curl_setopt_array($handle, $requestOptions);

        $rawResponse = curl_exec($handle);
        $info = curl_getinfo($handle);
        $result['info'] = $info;

        if ($rawResponse === false) {
            $errorNumber = curl_errno($handle);
            $errorMessage = curl_error($handle);
            curl_close($handle);

            $result['status_code'] = isset($info['http_code']) ? (int) $info['http_code'] : 0;
            $result['error'] = [
                'code' => $errorNumber,
                'message' => $errorMessage,
                'type' => $errorNumber === CURLE_OPERATION_TIMEOUTED ? 'timeout' : 'curl',
            ];

            if ($errorNumber === CURLE_OPERATION_TIMEOUTED) {
                $result['error']['message'] = isset($options['timeout_message'])
                    ? $options['timeout_message']
                    : 'The request timed out before receiving a response.';
            }

            return $result;
        }

        $headerSize = isset($info['header_size']) ? (int) $info['header_size'] : 0;
        $rawHeaders = substr($rawResponse, 0, $headerSize);
        $body = substr($rawResponse, $headerSize);
        $statusCode = isset($info['http_code']) ? (int) $info['http_code'] : 0;

        curl_close($handle);

        $result['status_code'] = $statusCode;
        $result['headers'] = $this->parseHeaders($rawHeaders);
        $result['body'] = $body;

        if ($statusCode >= 200 && $statusCode < 300) {
            $result['success'] = true;
        } else {
            $result['error'] = [
                'code' => $statusCode,
                'message' => isset($options['http_error_message'])
                    ? $options['http_error_message']
                    : 'Unexpected HTTP status code returned by the remote server.',
                'type' => 'http',
            ];
        }

        return $result;
    }

    /**
     * Merge query parameters with the given URL.
     *
     * @param string $url
     * @param array $query
     *
     * @return string
     */
    protected function applyQuery($url, array $query)
    {
        if (empty($query)) {
            return $url;
        }

        $separator = (strpos($url, '?') === false) ? '?' : '&';

        return $url . $separator . http_build_query($query);
    }

    /**
     * Convert header input to an array of header strings.
     *
     * @param array $headers
     *
     * @return array
     */
    protected function prepareHeaders(array $headers)
    {
        $prepared = [];

        foreach ($headers as $name => $value) {
            if (is_int($name)) {
                $prepared[] = $value;
            } else {
                $prepared[] = $name . ': ' . $value;
            }
        }

        return $prepared;
    }

    /**
     * Parse raw header string into an associative array.
     *
     * @param string $rawHeaders
     *
     * @return array
     */
    protected function parseHeaders($rawHeaders)
    {
        $headers = [];
        $blocks = preg_split('/\r?\n\r?\n/', trim($rawHeaders));
        $lastBlock = '';

        if (!empty($blocks)) {
            $lastBlock = trim((string) array_pop($blocks));
        }

        if ($lastBlock === '') {
            return $headers;
        }

        $lines = preg_split('/\r?\n/', $lastBlock);

        foreach ($lines as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }

            list($name, $value) = explode(':', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!isset($headers[$name])) {
                $headers[$name] = $value;
            } elseif (is_array($headers[$name])) {
                $headers[$name][] = $value;
            } else {
                $headers[$name] = [$headers[$name], $value];
            }
        }

        return $headers;
    }
}
