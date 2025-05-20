<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
namespace PrestaSDK\V040;

/**
 * PrestaSDKFactory - Factory class for accessing PrestaSDK functionality
 * 
 * This class provides a centralized access point to PrestaSDK functionality,
 * ensuring that the latest version of the SDK is always used regardless of
 * which module loaded it first.
 */
class PrestaSDKFactory
{
    /**
     * Get a utility class instance from the latest SDK version
     *
     * @param string $className The class name to instantiate (without namespace)
     * @param array $params Constructor parameters
     * @return mixed The instantiated class or null if not found
     */
    public static function getUtility(string $className, array $params = [])
    {
        $fullClassName = "\\PrestaSDK\\V040\\Utility\\{$className}";
        return self::createInstance($fullClassName, $params);
    }
    
    /**
     * Get an installer class instance from the latest SDK version
     *
     * @param string $className The class name to instantiate (without namespace)
     * @param array $params Constructor parameters
     * @return mixed The instantiated class or null if not found
     */
    public static function getInstaller(string $className, array $params = [])
    {
        $fullClassName = "\\PrestaSDK\\V040\\Install\\{$className}";
        return self::createInstance($fullClassName, $params);
    }
    
    /**
     * Get a model class instance from the latest SDK version
     *
     * @param string $className The class name to instantiate (without namespace)
     * @param array $params Constructor parameters
     * @return mixed The instantiated class or null if not found
     */
    public static function getModel(string $className, array $params = [])
    {
        $fullClassName = "\\PrestaSDK\\V040\\Model\\{$className}";
        return self::createInstance($fullClassName, $params);
    }
    
    /**
     * Get a controller class instance from the latest SDK version
     *
     * @param string $className The class name to instantiate (without namespace)
     * @param array $params Constructor parameters
     * @return mixed The instantiated class or null if not found
     */
    public static function getController(string $className, array $params = [])
    {
        $fullClassName = "\\PrestaSDK\\V040\\Controller\\{$className}";
        return self::createInstance($fullClassName, $params);
    }
    
    /**
     * Create an instance of the specified class with the given parameters
     *
     * @param string $fullClassName The fully qualified class name
     * @param array $params Constructor parameters
     * @return mixed The instantiated class or null if not found
     */
    private static function createInstance(string $fullClassName, array $params = [])
    {
        if (!class_exists($fullClassName)) {
            return null;
        }
        
        // Create a new instance with the provided parameters
        return new $fullClassName(...$params);
    }
}