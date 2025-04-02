<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
namespace PrestaSDK;

use PrestaSDK\Install\HooksInstaller;
use PrestaSDK\Install\TablesInstaller;
use PrestaSDK\Install\TabsInstaller;
use PrestaSDK\Utility\AssetPublisher;
use PrestaSDK\Utility\Config;
use PrestaSDK\Utility\VersionHelper;

class PrestaSDKModule extends \Module
{
    public array $moduleTabs;
    public array $moduleConfigs;
    public string $perfixConfigs = '';

    public string $configsAdminController;
    public string $moduleGrandParentTab = '';

    public string $pathFileSqlInstall;
    public string $pathFileSqlUninstall;

    public $sectionQueryKey = 'section';
    public $sectionDefault = 'index';
    public $sectionForce;

    public Config $config;

    public function __construct()
    {
        $this->context = \Context::getContext();
        $this->name = strtolower(get_class($this));
        $this->bootstrap = true;

        $this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];

        if (method_exists($this,'initModule')) {
            $this->initModule();
        }

        $this->config = new Config($this->moduleConfigs, $this->perfixConfigs);

        parent::__construct();

        if (empty($this->pathFileSqlInstall)) {
            $this->pathFileSqlInstall = $this->getModulePath() . 'sql/install.sql';
        }

        if (empty($this->pathFileSqlUninstall)) {
            $this->pathFileSqlUninstall = $this->getModulePath() . 'sql/uninstall.sql';
        }
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
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
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
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
     * @throws \Exception
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
     * Get Prestashop Base Path
     *
     * @return string
     */
    public function getPsBasePath()
    {
        return _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR;
    }

    /**
     * Get This Module Path
     *
     * @param null $extraPath
     *
     * @return string
     */
    public function getModulePath($extraPath = null)
    {
        $path = _PS_MODULE_DIR_ . $this->name . '/';
        if (!empty($extraPath)) {
            $path .= ltrim($extraPath, '/');
        }
        return $path;
    }

    public function getPsBaseUrl(): bool|string
    {
        $auto_secure_mode = \Configuration::get('PS_SSL_ENABLED');
        return \Context::getContext()->shop->getBaseURL($auto_secure_mode);
    }

    /**
     * Get This Module Url
     *
     * @param null $extraPath
     *
     * @return string
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
     * getModuleAdminLink
     */
    public function getModuleAdminLink($controller, $params = [], $withToken = true): string
    {
        if (is_string($params)) {
            $params = [$this->sectionQueryKey => $params];
        }

        return $this->context->link->getAdminLink($controller, $withToken, [], $params);
    }

    /**
     * helper method for get use reques section=? value
     *
     * @return string
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

    public function getFromConfigs($config)
	{
		if (empty($this->config)) {
            $this->config = new Config($this->moduleConfigs, $this->perfixConfigs);
        }

        return $this->config->getConfig($config);
    }

    /**
     * fetch and return Template
     *
     * @param $tplPath
     * @param array $vars
     *
     * @return false|string
     *
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

    public function renderModuleTemplate( $tpl,array $vars = [] )
    {
        $tpl = ltrim($tpl, '\/');
        $tplPath = $this->getModulePath() . 'views/templates/' . $tpl;
        return $this->fetchTemplate($tplPath, $vars);
    }
}