<?php
/**
 * PrestaShop Payment Manager Pro - Product Info Manager
 *
 * Service class for managing product information with caching using PrestaSDK CacheManager
 *
 * @author PrestaWare.com
 * @copyright (c) 2025 - PrestaWare Team
 * @website https://prestaware.com
 */
namespace PrestaYar\Localizer\Api\Service;

use PrestaSDK\V071\Utility\CacheManager;
use PrestaYar\Localizer\Api\Config\ApiConfig;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductInfoManager
{
    /** @var ApiService */
    private $apiService;
    
    /** @var CacheManager */
    private $cacheManager;
    
    /** @var string */
    private $moduleName;
    
    /** @var string */
    private $domain;
    
    /** @var string */
    private $productId;
    
    /**
     * Constructor
     *
     * @param \Module $module Module instance
     * @param string $domain Shop domain
     * @param string|null $productId Product identifier (optional, defaults to module name)
     */
    public function __construct(\Module $module, string $domain, ?string $productId = null)
    {
        $this->moduleName = $module->name;
        $this->domain = $domain;
        $this->productId = $productId ?? $module->name;
        
        $this->apiService = new ApiService($module);
        $this->cacheManager = new CacheManager($module->name);
    }
    
    /**
     * Get module information from cache or API
     *
     * @return array Module information with product data and errors
     */
    public function getModuleInfo(): array
    {
        return $this->cacheManager->remember(
            ApiConfig::CACHE_KEY_MODULE_INFO,
            ApiConfig::CACHE_TTL,
            function () {
                return $this->fetchModuleInfoFromApi();
            }
        );
    }

    /**
     * Fetch module information from API
     *
     * @return array API response with error handling
     */
    private function fetchModuleInfoFromApi(): array
    {
        try {
            // Fetch product info from API
            $response = $this->apiService->fetchProductInfo(
                $this->domain,
                $this->productId
            );

            return [
                'data' => $this->normalizeApiResponse($response),
                'errors' => []
            ];
            
        } catch (\Exception $e) {
            return [
                'data' => null,
                'errors' => [
                    [
                        'type' => 'api_error',
                        'message' => $e->getMessage(),
                        'code' => 'API_REQUEST_FAILED'
                    ]
                ]
            ];
        }
    }    

    /**
     * Normalize API response to consistent format
     *
     * @param array $response Raw API response
     * @return array Normalized response
     */
    private function normalizeApiResponse(array $response): array
    {
        $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : [];

        $productInfo = isset($data['product_info']) && is_array($data['product_info']) ? $data['product_info'] : [];
        $updateInfo = isset($data['update_info']) && is_array($data['update_info']) ? $data['update_info'] : [];

        $statusCode = isset($response['status_code']) ? (int) $response['status_code'] : (int) ($response['status_code'] ?? 0);
        $message = isset($response['message']) ? (string) $response['message'] : '';

        return [
            'product_info' => $productInfo,
            'update_info' => $updateInfo,
            'meta' => array_filter([
                'success' => true,
                'status_code' => $statusCode,
                'message' => $message,
                'fetched_at' => $this->moduleProductInfo['meta']['fetched_at'] ?? date(DATE_ATOM),
            ]),
        ];
    }    
    
    /**
     * Get product information with error handling
     *
     * @return array Product information or error details
     */
    public function getProductInfo(): array
    {
        $moduleInfo = $this->getModuleInfo();
        
        if (isset($moduleInfo['errors']) && !empty($moduleInfo['errors'])) {
            return [
                'success' => false,
                'errors' => $moduleInfo['errors'],
                'data' => null
            ];
        }
        
        return [
            'success' => true,
            'errors' => [],
            'data' => $moduleInfo['data'] ?? null
        ];
    }
    
    /**
     * Get only error information from cached data
     *
     * @return array Error information
     */
    public function getProductInfoErrors(): array
    {
        $moduleInfo = $this->getModuleInfo();
        return $moduleInfo['errors'] ?? [];
    }
    
    /**
     * Clear cached module information
     *
     * @return bool True if cache was cleared
     */
    public function clearCache(): bool
    {
        try {
            $this->cacheManager->forget(ApiConfig::CACHE_KEY_MODULE_INFO);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Force refresh module information from API
     *
     * @return array Fresh module information
     */
    public function refreshModuleInfo(): array
    {
        $this->clearCache();
        return $this->getModuleInfo();
    }
}
