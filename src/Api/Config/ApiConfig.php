<?php
/**
 * PrestaShop Payment Manager Pro - API Configuration
 *
 * Central configuration class for API endpoints and constants
 *
 * @author PrestaWare.com
 * @copyright (c) 2025 - PrestaWare Team
 * @website https://prestaware.com
 */
namespace PrestaYar\Localizer\Api\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiConfig
{
    // API Base Configuration
    const API_BASE_URL = 'https://api.prestayar.com/v5';
    
    // API Endpoints
    const PRODUCT_INFO_ENDPOINT = self::API_BASE_URL . '/product/info';
    
    // Cache Configuration
    const CACHE_TTL = 86400; // 24 hours in seconds
    const CACHE_KEY_MODULE_INFO = 'module_api_info';
    
    // Request Timeouts (in seconds)
    const REQUEST_TIMEOUT = 10;
    const CONNECT_TIMEOUT = 5;
    
    // JWT Configuration
    const JWT_ALGORITHM = 'RS256';
    const MAX_VALIDATION_ATTEMPTS = 2;
}