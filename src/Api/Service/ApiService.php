<?php
/**
 * PrestaShop Payment Manager Pro - API Service
 *
 * Service class for handling API communication using PrestaSDK CurlClient
 *
 * @author PrestaWare.com
 * @copyright (c) 2025 - PrestaWare Team
 * @website https://prestaware.com
 */
namespace PrestaYar\Localizer\Api\Service;

use PrestaSDK\V070\Utility\CurlClient;
use PrestaYar\Localizer\Api\Config\ApiConfig;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiService
{
    /** @var CurlClient */
    private $curlClient;
    
    /** @var \Module PrestaShop Module instance */
    private $module;
    
    /**
     * Constructor
     *
     * @param \Module $module PrestaShop Module instance
     */
    public function __construct(\Module $module)
    {
        $this->module = $module;

        // Initialize CurlClient with default settings
        $this->curlClient = new CurlClient(
            ApiConfig::REQUEST_TIMEOUT, // timeout
            [
                'Accept' => 'application/json',
                'User-Agent' => 'PrestaShop-Module/' . $this->module->name . '/' . $this->module->version
            ], // default headers
            true // verify SSL peer
        );
    }
    
    /**
     * Fetch product information from API
     *
     * @param string $domain Shop domain
     * @param string $productId Product identifier
     * @return array API response
     * @throws \Exception If API request fails
     */
    public function fetchProductInfo(string $domain, string $productId): array
    {
        $queryParams = $this->buildProductInfoParams($domain, $productId);
        
        // Log the API request attempt
        $this->logApiRequest('PRODUCT_INFO', ApiConfig::PRODUCT_INFO_ENDPOINT, [
            'domain' => $domain,
            'product_id' => $productId
        ]);

        // Use CurlClient's request method with query parameters
        $response = $this->curlClient->request('GET', ApiConfig::PRODUCT_INFO_ENDPOINT, [
            'query' => $queryParams
        ]);

        return $this->handleApiResponse($response, 'PRODUCT_INFO', 'product info API', [
            'domain' => $domain,
            'product_id' => $productId
        ]);
    }
    
    /**
     * Handle API response and validate it
     *
     * @param array $response Raw response from CurlClient
     * @param string $operation Operation name for logging
     * @param string $operationLabel Human-readable operation label
     * @param array $logContext Additional context for logging
     * @return array Decoded JSON response
     * @throws \Exception If response is invalid or contains errors
     */
    private function handleApiResponse(array $response, string $operation, string $operationLabel, array $logContext = []): array
    {
        // Handle cURL errors (connection issues, timeouts, etc.)
        if (!$response['success'] && isset($response['error'])) {
            $errorMessage = $this->extractErrorMessage($response);
            
            $errorInfo = [
                'error_code' => $response['error']['code'] ?? null,
                'error_message' => $errorMessage,
                'error_type' => $response['error']['type'] ?? null,
                'status_code' => $response['status_code'] ?? null
            ];
            
            $this->logApiError($operation, "Failed to connect to {$operationLabel}: {$errorMessage}", $errorInfo);
            throw new \Exception("Failed to connect to {$operationLabel}: {$errorMessage}");
        }
        
        // Handle HTTP errors (4xx, 5xx status codes)
        if (!$response['success'] && isset($response['status_code']) && $response['status_code'] >= 400) {
            $this->logApiError($operation, "HTTP error from {$operationLabel}", [
                'status_code' => $response['status_code'],
                'response_body' => $response['body'] ?? null
            ]);
            throw new \Exception("HTTP error from {$operationLabel}. Status: {$response['status_code']}");
        }
        
        // Parse and validate JSON response
        $decodedResponse = $this->parseJsonResponse($response, $operation, $operationLabel);
        
        // Check API-level success
        if (!isset($decodedResponse['success']) || !$decodedResponse['success']) {
            $errorMessage = $decodedResponse['message'] ?? 'Unknown error occurred';
            $this->logApiError($operation, "{$operationLabel} failed: {$errorMessage}", array_merge([
                'api_response' => $decodedResponse,
                'status_code' => $response['status_code'] ?? null
            ], $logContext));
            throw new \Exception("{$operationLabel} failed: {$errorMessage}");
        }
        
        // Log successful response
        $this->logApiSuccess($operation, "{$operationLabel} successful", array_merge([
            'response_data' => $decodedResponse,
            'status_code' => $response['status_code'] ?? null
        ], $logContext));
        
        return $decodedResponse;
    }
    
    /**
     * Extract error message from response
     *
     * @param array $response Response array
     * @return string Error message
     */
    private function extractErrorMessage(array $response): string
    {
        if (isset($response['body'])) {
            $decodedResponse = json_decode($response['body'], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedResponse['message'])) {
                $errorMessage = $decodedResponse['message'];
                
                // Handle detailed error information
                if (isset($decodedResponse['error']['details'])) {
                    $details = $decodedResponse['error']['details'];
                    if (is_array($details)) {
                        $detailMessages = [];
                        foreach ($details as $detail) {
                            if (isset($detail['field']) && isset($detail['message'])) {
                                $detailMessages[] = $detail['field'] . ': ' . $detail['message'];
                            } elseif (is_string($detail)) {
                                $detailMessages[] = $detail;
                            }
                        }
                        if (!empty($detailMessages)) {
                            $errorMessage .= ' - ' . implode(', ', $detailMessages);
                        }
                    } elseif (is_string($details)) {
                        $errorMessage .= ' - ' . $details;
                    }
                }
                
                return $errorMessage;
            }
        }
        
        return $response['error']['message'] ?? 'Unknown error occurred';
    }
    
    /**
     * Parse JSON response and validate it
     *
     * @param array $response Raw response
     * @param string $operation Operation name for logging
     * @param string $operationLabel Human-readable operation label
     * @return array Decoded JSON response
     * @throws \Exception If JSON is invalid
     */
    private function parseJsonResponse(array $response, string $operation, string $operationLabel): array
    {
        $decodedResponse = json_decode($response['body'] ?? '', true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logApiError($operation, "Invalid JSON response from {$operationLabel}", [
                'json_error' => json_last_error_msg(),
                'response_length' => strlen($response['body'] ?? ''),
                'status_code' => $response['status_code'] ?? null
            ]);
            throw new \Exception("Invalid JSON response from {$operationLabel}");
        }
        
        return $decodedResponse;
    }
    
    /**
     * Build query parameters for product info request
     *
     * @param string $domain Shop domain
     * @param string $productId Product identifier
     * @return array Query parameters
     */
    private function buildProductInfoParams(string $domain, string $productId): array
    {
        return [
            'product' => $productId,
            'domain' => $this->normalizeDomain($domain),
            'php_version' => PHP_VERSION,
            'ps_version' => _PS_VERSION_,
            'product_version' => $this->module->version
        ];
    }
    
    /**
     * Normalize domain for API requests
     *
     * @param string $domain Raw domain
     * @return string Normalized domain
     */
    private function normalizeDomain(string $domain): string
    {
        // Remove protocol if present
        $domain = preg_replace('#^https?://#', '', $domain);
        
        // Remove www prefix
        $domain = preg_replace('#^www\.#', '', $domain);
        
        // Remove trailing slash and path
        $domain = strtok($domain, '/');
        
        // Remove port if present
        $domain = strtok($domain, ':');
        
        return strtolower(trim($domain));
    }
    
    /**
     * Get last response information from CurlClient
     *
     * @return array Response information
     */
    public function getLastResponseInfo(): array
    {
        // This method can be used to get additional info if needed
        // CurlClient returns all info in the response array
        return [
            'note' => 'Response information is included in the request response array'
        ];
    }
    
    /**
     * Log API request attempt
     *
     * @param string $operation API operation name
     * @param string $endpoint API endpoint URL
     * @param array $context Additional context data
     */
    private function logApiRequest(string $operation, string $endpoint, array $context = []): void
    {
        $message = "API Request [{$operation}] to endpoint: {$endpoint}";
        $logContext = array_merge($context, [
            'operation' => $operation,
            'endpoint' => $endpoint,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        $this->module->log('DEBUG', $message, $logContext);
    }
    
    /**
     * Log successful API response
     *
     * @param string $operation API operation name
     * @param string $message Success message
     * @param array $context Additional context data
     */
    private function logApiSuccess(string $operation, string $message, array $context = []): void
    {
        $logContext = array_merge($context, [
            'operation' => $operation,
            'status' => 'SUCCESS',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        $this->module->log('DEBUG', $message, $logContext);
    }
    
    /**
     * Log API error
     *
     * @param string $operation API operation name
     * @param string $message Error message
     * @param array $context Additional context data
     */
    private function logApiError(string $operation, string $message, array $context = []): void
    {
        $logContext = array_merge($context, [
            'operation' => $operation,
            'status' => 'ERROR',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        $this->module->log('ERROR', $message, $logContext);
    }
}