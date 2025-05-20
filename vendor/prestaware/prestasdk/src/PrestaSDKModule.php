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

use PrestaSDK\V040\Install\HooksInstaller;
use PrestaSDK\V040\Install\TablesInstaller;
use PrestaSDK\V040\Install\TabsInstaller;
use PrestaSDK\V040\Utility\AssetPublisher;
use PrestaSDK\V040\Utility\Config;
use PrestaSDK\V040\Utility\VersionHelper;

/**
 * Base class for PrestaShop module development
 * Extends the core PrestaShop Module class with additional functionality
 */
class PrestaSDKModule extends \Module
{
    /**
     * @var array List of admin tabs to be installed with the module
     */
    public array $moduleTabs;
    
    /**
     * @var array List of configuration values for the module
     */
    public array $moduleConfigs;
    
    /**
     * @var string Prefix for configuration keys in the database
     */
    public string $perfixConfigs = '';

    /**
     * @var string Main admin controller for module configuration
     */
    public string $configsAdminController;
    
    /**
     * @var string Parent tab for module tabs in the admin panel
     */
    public string $moduleGrandParentTab = '';

    /**
     * @var string Path to SQL installation file
     */
    public string $pathFileSqlInstall;
    
    /**
     * @var string Path to SQL uninstallation file
     */
    public string $pathFileSqlUninstall;

    /**
     * @var string Query parameter name for section navigation
     */
    public $sectionQueryKey = 'section';
    
    /**
     * @var string Default section to display if none specified
     */
    public $sectionDefault = 'index';
    
    /**
     * @var string|null Force a specific section to be displayed
     */
    public $sectionForce;

    /**
     * @var Config Configuration utility instance
     */
    public Config $config;

    /**
     * Constructor initializes the module with default settings
     */
    public function __construct()
    {
        $this->context = \Context::getContext();
        $this->name = strtolower(get_class($this));
        $this->bootstrap = true;

        $this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];
		
		// Initialize moduleConfigs if not set
        if (!isset($this->moduleConfigs)) {
            $this->moduleConfigs = [];
        }
        
        // Call initModule before parent constructor but after basic initialization
        if (method_exists($this,'initModule')) {
            $this->initModule();
        }

        $this->config = new Config($this->moduleConfigs, $this->perfixConfigs);

        // Call parent constructor after all properties are initialized
        parent::__construct();

        if (empty($this->pathFileSqlInstall)) {
            $this->pathFileSqlInstall = $this->getModulePath() . 'sql/install.sql';
        }

        if (empty($this->pathFileSqlUninstall)) {
            $this->pathFileSqlUninstall = $this->getModulePath() . 'sql/uninstall.sql';
        }
    }

    /**
     * Installs the module, including tabs, hooks, tables, and configurations
     * 
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     * @return bool Success status of installation
     */
    public function install(): bool
    {
        if (!parent::install()) {
            return false;
        }
        
        if (!(new TabsInstaller($this))->installTabs()) {
            return false;
        }

        if (!(new HooksInstaller($this))->installHooks()) {
            return false;
        }

        if (!(new TablesInstaller($this))->installTables()) {
            return false;
        }

        (new Config())->updateConfigs($this->moduleConfigs);

        AssetPublisher::publishAssets($this->name);

        // Save SDK version in Configuration during installation
        $sdkVersion = VersionHelper::getSDKVersion();
        \Configuration::updateValue('PRESTASDK_VERSION_' . $this->name, $sdkVersion);

        // Publish assets
        AssetPublisher::publishAssets($this->name);

        return true;
    }

    /**
     * Uninstalls the module, removing tabs, tables, and configurations
     * 
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @return bool Success status of uninstallation
     */
    public function uninstall(): bool
    {
        if (!parent::uninstall()) {
            return false;
        }

        if (!(new TabsInstaller($this))->uninstallTabs()) {
            return false;
        }

        if (!(new TablesInstaller($this))->uninstallTables()) {
            return false;
        }

        (new Config())->deleteConfigs($this->moduleConfigs);

        return true;
    }

    /**
     * Handles module configuration page access
     * Redirects to the appropriate admin controller
     * 
     * @throws \Exception
     * @return string|void HTML content or redirect
     */
    public function getContent()
    {
        if (!empty($this->configsAdminController)) {
            if (is_array($this->configsAdminController) && $this->configsAdminController[0] && $this->configsAdminController[1]) {
                \Tools::redirectAdmin($this->getModuleAdminLink($this->configsAdminController[0], $this->configsAdminController[1]));
            }

            \Tools::redirectAdmin($this->getModuleAdminLink($this->configsAdminController));
        } else {
            return $this->displayConfirmation(sprintf($this->l('%s is active!'), $this->l($this->name)));
        }
    }

    /**
     * Gets the PrestaShop base file system path
     * 
     * @return string Base path with trailing directory separator
     */
    public function getPsBasePath()
    {
        return _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR;
    }

    /**
     * Gets the module's file system path
     * 
     * @param null|string $extraPath Additional path to append
     * @return string Complete module path
     */
    public function getModulePath($extraPath = null)
    {
        $path = _PS_MODULE_DIR_ . $this->name . '/';
        if (!empty($extraPath)) {
            $path .= ltrim($extraPath, '/');
        }
        return $path;
    }

    /**
     * Gets the PrestaShop base URL
     * 
     * @return bool|string Base URL with protocol based on SSL settings
     */
    public function getPsBaseUrl(): bool|string
    {
        $auto_secure_mode = \Configuration::get('PS_SSL_ENABLED');
        return \Context::getContext()->shop->getBaseURL($auto_secure_mode);
    }

    /**
     * Gets the module's URL
     * 
     * @param null|string $extraPath Additional path to append
     * @return string Complete module URL
     */
    public function getModuleUrl($extraPath = null): string
    {
        $url = $this->getPsBaseUrl() . 'modules/' . $this->name . '/';
        if (!empty($extraPath)) {
            $url .= ltrim($extraPath, '/');
        }
        return $url;
    }

    /**
     * Generates an admin link for the module
     * 
     * @param string $controller Admin controller name
     * @param array|string $params Additional parameters or section name
     * @param bool $withToken Whether to include security token
     * @return string Complete admin URL
     */
    public function getModuleAdminLink($controller, $params = [], $withToken = true): string
    {
        if (is_string($params)) {
            $params = [$this->sectionQueryKey => $params];
        }

        return $this->context->link->getAdminLink($controller, $withToken, [], $params);
    }

    /**
     * Gets the current section from request parameters
     * 
     * @return string Current section name
     */
    public function getRequestSection()
    {
        if ($this->sectionForce) {
            return $this->sectionForce;
        }

        $section = \Tools::getValue($this->sectionQueryKey);

        if (empty($section)) {
            $section = $this->sectionDefault;
        }

        return $section;
    }

    /**
     * Gets a configuration value
     * 
     * @param string $config Configuration key
     * @return mixed Configuration value
     */
    public function getFromConfigs($config)
	{
		if (empty($this->config)) {
            $this->config = new Config($this->moduleConfigs, $this->perfixConfigs);
        }

        return $this->config->getConfig($config);
    }

    /**
     * Fetches and renders a template file
     * 
     * @param string $tplPath Full path to template file
     * @param array $vars Variables to assign to the template
     * @return false|string Rendered template content
     * @throws \SmartyException
     */
    public function fetchTemplate($tplPath, array $vars = [])
    {
        if (!file_exists($tplPath)) {
            return "<b style='color: red;'>ERROR:</b> $tplPath <b style='color: red;'> not found</b>";
        }

        if (!empty($vars)) {
            $this->context->smarty->assign($vars);
        }

        return $this->context->smarty->fetch($tplPath);
    }

    /**
     * Renders a module template file from the standard template directory
     * 
     * @param string $tpl Template path relative to views/templates/
     * @param array $vars Variables to assign to the template
     * @return false|string Rendered template content
     */
    public function renderModuleTemplate($tpl, array $vars = [])
    {
        $tpl = ltrim($tpl, '\/');
        $tplPath = $this->getModulePath() . 'views/templates/' . $tpl;
        return $this->fetchTemplate($tplPath, $vars);
    }
}