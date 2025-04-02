<?php
/**
 * Prestashop localizer
 * Comprehensive localization of Prestashop specifically tailored for the Persian language and the Iranian market.
 *
 * @author Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright (c) 2025 - PrestaYar Team
 * @website https://prestayar.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/vendor/autoload.php';

use PrestaYar\Localizer\LocalizerModule;

class Psy_Localizer extends LocalizerModule
{
    public function initModule()
    {
        $this->name = 'psy_localizer';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        
        $this->author = 'PrestaYar.com';
        $this->displayName = $this->l('Prestashop localizer');
        $this->description = $this->l('A comprehensive localization solution for PrestaShop specifically designed for the Persian language and Iranian market.');
        
        $this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];

        // Module Tab GrandParent 
        $this->moduleGrandParentTab = 'CONFIGURE';

        // defined Controllers
        $this->configsAdminController = 'AdminLocalizerPanel';

        $this->moduleTabs = [
            'AdminLocalizerPanel' => [
                'class' => 'AdminLocalizerPanel',
                'title' => $this->l('Localizer'),
                'icon' => 'language',
            ],
        ];

        // Module Configs
        $this->perfixConfigs = 'Localizer';
        $this->moduleConfigs = [
            'Localizer_NativeActive' => 1,
            'Localizer_JalaliDate' => 0,
            'Localizer_BackofficeFont' => 'vazir',
            'Localizer_TinyMCE' => 0,
        ];
    }
}