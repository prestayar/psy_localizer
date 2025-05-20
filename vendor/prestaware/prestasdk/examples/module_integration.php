<?php
/**
 * Prestashop Module Development Kit - Integration Example
 *
 * This file demonstrates how to integrate the centralized PrestaSDK system
 * into your PrestaShop modules to ensure compatibility between different versions.
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */

/**
 * Example of a module main file structure using the centralized PrestaSDK system
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Step 2: Load the module's autoloader for other dependencies
require_once dirname(__FILE__) . '/vendor/autoload.php';

// Import required classes
use PrestaSDK\V040\PrestaSDKModule;
use PrestaSDK\V040\PrestaSDKFactory;

/**
 * Example module class extending PrestaSDKModule
 */
class ExampleModule extends PrestaSDKModule
{
    /**
     * Initialize module properties
     */
    public function initModule()
    {
        $this->name = 'example_module';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        
        $this->author = 'Your Name';
        $this->displayName = $this->l('Example Module');
        $this->description = $this->l('An example module using PrestaSDK');

        $this->ps_versions_compliancy = ['min' => '1.7.8', 'max' => _PS_VERSION_];
        
        // Define module tabs and other properties
        $this->moduleTabs = [
            // Your tabs configuration
        ];
    }
    
    /**
     * Example of using PrestaSDKFactory to access SDK functionality
     */
    public function exampleMethod()
    {
        // Get a utility class using the factory
        $config = PrestaSDKFactory::getUtility('Config');
        
        // Get an installer class with parameters
        $tabsInstaller = PrestaSDKFactory::getInstaller('TabsInstaller', [$this]);
        
        return true;
    }
}