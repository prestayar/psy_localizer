<?php
/**
 * Prestashop localizer
 * Comprehensive localization of Prestashop specifically tailored for the Persian language and the Iranian market.
 *
 * @author Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright (c) 2025 - PrestaYar Team
 * @website https://prestayar.com
 */
namespace PrestaYar\Localizer;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaSDK\V071\Controller\AdminController;
use PrestaYar\Localizer\Api\Service\ProductInfoManager;

class LocalizerAdmin extends AdminController
{
    /**
     * @var ProductInfoManager
     */
    private $productInfoManager;    

    protected $sidebarOrientation = 'horizontal';
    
    public function getmenuItems()
    {
        $menuItems = [
            'position_info' => [
                'configure' => [
                    'title' => $this->module->l('Configure', 'localizeradmin'),
                    'link' => $this->module->getModuleAdminLink($this->module->configsAdminController, 'configure'),
                    'icon' => 'icon-gear',
                ],
                'document' => [
                    'title' => $this->module->l('document', 'localizeradmin'),
                    'link' => $this->module->getModuleAdminLink($this->module->configsAdminController, 'document'),
                    'icon' => 'icon-support',
                ]
            ],
        ];

        if (!empty(self::$webServiceResponse['product_info']['help_link'])){
            $menuItems['position_info']['help'] = [
                'title' => $this->module->l('Help', 'localizeradmin'),
                'link' => self::$webServiceResponse['product_info']['help_link'],
                'icon' => 'icon-question-circle',
            ];
        }

        return $menuItems;
    }    

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->context->controller->addCSS($this->module->getPathUri() . '/views/css/admin/localizer-init.css');
    }


    /**
     * Initialize SDK Panel with module information
     */
    public function initSDKPanel()
    {
        $this->initInfoModule();

        // Set module about URL
        $controller = $this->module->configsAdminController ?? null;
        if (!empty($controller)) {
            $this->pushPanelVar('module_about_url', $this->module->getModuleAdminLink($controller, 'dashboard'));  
        }        

        parent::initSDKPanel();
    }    

    /**
     * Initialize ProductInfoManager
     */
    private function initInfoModule(): void
    {
        // Set module information for SDK panel
        $moduleInfo = $this->getProductInfoManager()->getModuleInfo();

        $this->pushPanelVar('module_info', $moduleInfo);   
        
        if (isset($moduleInfo['data']['update_info']['version'])) {
            if (version_compare($this->module->version, $moduleInfo['data']['update_info']['version'], '>=')) {
                $this->pushPanelVar('status_update', 'success');
            } else {
                $this->pushPanelVar('status_update', 'warning');
                $this->pushPanelVar('tooltip_message', $this->l('A new version of the module has been released, please update!'));
            }
        }
    }    

    /**
     * Get ProductInfoManager instance
     */
    protected function getProductInfoManager(): ProductInfoManager
    {
        if (!$this->productInfoManager) {
            $this->productInfoManager = new ProductInfoManager(
                $this->module,
                \Tools::getShopDomain(true),
            );
        }
        
        return $this->productInfoManager;
    }
}
