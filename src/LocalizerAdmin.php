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

use PrestaSDK\Controller\AdminController;
use PrestaYar\Localizer\Traits\UseInfoModule;

class LocalizerAdmin extends AdminController
{
    use UseInfoModule;

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->context->controller->addCSS($this->module->getPathUri() . '/views/css/admin/localizer-init.css');
    }

    public function initAdminPanel()
    {
        $apiResult = $this->getModuleInfo();
        if (isset($apiResult['result'])) {
            self::$webServiceResponse = $apiResult;
        }

        $this->setWebserviceInfo();

        $this->pushPanelVar('module_about_url', $this->module->getModuleAdminLink($this->module->configsAdminController, 'about'));

        return parent::initAdminPanel();
    }

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
            $menuItems['position_end'] = [
                'help' => [
                    'title' => $this->module->l('Help', 'localizeradmin'),
                    'link' => self::$webServiceResponse['product_info']['help_link'],
                    'icon' => 'icon-question-circle',
                ],
            ];
        }

        return $menuItems;
    }

    public function getSwitchValues()
    {
        return [
            [
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->module->l('Enabled', 'localizeradmin'),
            ], [
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->module->l('Disabled', 'localizeradmin')
            ],
        ];
    }
}
