<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
namespace PrestaSDK\V071\Utility;

class CacheManager
{
    private $cacheDir;

    /**
     * @param string $moduleName The name of the module to create a cache directory for.
     */
    public function __construct($moduleName)
    {
        // Define cache directory path following PrestaShop standard
        $this->cacheDir = _PS_CACHE_DIR_ . 'prestsdk/' . $moduleName . '/';
        
        // Create directory if it does not exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0775, true);
        }
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string $key The unique cache key.
     * @param int $ttl Time To Live in seconds.
     * @param \Closure $callback The function to execute if the item is not in the cache.
     * @return mixed
     */
    public function remember($key, $ttl, \Closure $callback)
    {
        $filePath = $this->getFilePath($key);
        // Check if cache file exists and is not expired
        if (file_exists($filePath) && (time() - filemtime($filePath)) < $ttl) {
            $cachedData = file_get_contents($filePath);
            return unserialize($cachedData);
        }

        // If cache is missing, execute the callback
        $freshData = call_user_func($callback);

        // Store the result in the cache file
        if ($freshData !== null) {
            file_put_contents($filePath, serialize($freshData));
        }

        return $freshData;
    }
    
    /**
     * Delete an item from the cache file.
     *
     * @param string $key
     */
    public function forget($key)
    {
        $filePath = $this->getFilePath($key);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Generates a file path for a given cache key.
     *
     * @param string $key
     * @return string
     */
    private function getFilePath($key)
    {
        // Use a hash for file name to avoid invalid characters
        return $this->cacheDir . sha1($key) . '.cache';
    }
}