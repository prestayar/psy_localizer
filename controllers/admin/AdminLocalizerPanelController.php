<?php
/**
 * Prestashop localizer
 * Comprehensive localization of Prestashop specifically tailored for the Persian language and the Iranian market.
 *
 * @author Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright (c) 2025 - PrestaYar Team
 * @website https://prestayar.com
 */
use PrestaSDK\Utility\Config;
use PrestaSDK\Utility\HelperForm;
use PrestaYar\Localizer\LocalizerAdmin;
use PrestaYar\Localizer\Native\NativeCorePrestashop;
use PrestaYar\Localizer\Native\StyleSheetFont;

class AdminLocalizerPanelController extends LocalizerAdmin
{
	/*
	|--------------------------------------------------------------------------
	| Controller Action Tabs
	|--------------------------------------------------------------------------
	*/

    public function sectionIndex()
    {
        Tools::redirectAdmin($this->module->getModuleAdminLink($this->module->configsAdminController, 'configure'));
    }

    public function sectionConfigure() 
    {
        $output = '';

        $simpleConfigs = [
            'Localizer_Native_Active',
            'Localizer_JalaliDate',
            'Localizer_TinyMCE',
            'Localizer_BackofficeFont',
        ];

        if (Tools::isSubmit('submit' . $this->module->name)) {
            Config::updateConfigs($simpleConfigs, true, true);
            if (empty($this->errors)) {
                $output .= $this->module->displayConfirmation($this->l('All settings have been updated successfully!', 'adminlocalizerpanelcontroller'));
            } else {
                $output .= $this->module->displayWarning($this->l('Except for the warnings and errors above, all other settings have been updated!', 'adminlocalizerpanelcontroller'));
            }

            if ($this->getFromConfigs('Native_Active')) {
                NativeCorePrestashop::changeFiles();
            }

            if (Tools::getValue('Localizer_FixCurrency')) {
                $result = NativeCorePrestashop::fixCurrency();
                if ($result === true) {
                    $output .= $this->module->displayConfirmation($this->module->l('Currency corrected, clear the cache once to apply the changes.', 'adminlocalizerpanelcontroller'));
                } else if ($result === -10) {
                    $output .= $this->module->displayError($this->module->l('Persian language is not available in your store, it is not possible to modify the currency.', 'adminlocalizerpanelcontroller'));
                } else if ($result === -11) {
                    $output .= $this->module->displayError($this->module->l('Error correcting Toman currency.', 'adminlocalizerpanelcontroller'));
                } else if ($result === -12) {
                    $output .= $this->module->displayError($this->module->l('Error adding Toman currency.', 'adminlocalizerpanelcontroller'));
                }
            }

            if (Tools::getValue('Localizer_BackofficeFont')) {
                $result = (new StyleSheetFont($this->module))->generate($this->getFromConfigs('BackofficeFont'));

                if (empty($result)) {
                    \Configuration::updateValue('Localizer_BackofficeFont', false);
                    $output .= $this->module->displayError($this->module->l('Error, the selected font files are not available or it is not possible to edit the css file of the fonts.', 'adminlocalizerpanelcontroller'));
                } else {
                    $output .= $this->module->displayConfirmation($this->module->l('Admin font changed. Please clear the browser using Ctrl+f5.', 'adminlocalizerpanelcontroller'));
                }
            }

        }

        // Init Fields form array
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->module->l('Native Setting', 'adminlocalizerpanelcontroller'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'name' => 'Localizer_Native_Active',
                    'label' => $this->module->l('Native Active', 'adminlocalizerpanelcontroller'),
                ], [
                    'type' => 'switch',
                    'name' => 'Localizer_JalaliDate',
                    'label' => $this->module->l('Jalali Date', 'adminlocalizerpanelcontroller'),
                    'desc' => $this->module->l('This option changes the PrestaShop date to a glorious date.', 'adminlocalizerpanelcontroller'),
                ], [
                    'type' => 'switch',
                    'name' => 'Localizer_TinyMCE',
                    'label' => $this->module->l('Advanced text editor', 'adminlocalizerpanelcontroller'),
                    'desc' => $this->module->l('To modify the settings of the text editor and add more options to it', 'adminlocalizerpanelcontroller'),
                ], [
                    'type' => 'select',
                    'label' => $this->module->l('Backoffice font', 'adminlocalizerpanelcontroller'),
                    'name' => 'Localizer_BackofficeFont',
                    'options' => [
                        'optiongroup' => [
                            'query' => $this->getFontsOption(),
                            'label' => 'name'
                        ],
                        'options' => [
                            'query' => 'query',
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'default' => [
                            'value' => 0,
                            'label' => $this->module->l('Use defualt fonts', 'adminlocalizerpanelcontroller')
                        ],
                    ],
                    'desc' => $this->module->l('Specifies the font type of the admin area, after changing the font to apply the page, reload with ctrl + f5.', 'adminlocalizerpanelcontroller'),
                ], [
                    'type' => 'switch',
                    'name' => 'Localizer_FixCurrency',
                    'label' => $this->module->l('Modify and add currency', 'adminlocalizerpanelcontroller'),
                    'desc' => [
                        $this->module->l('The Toman currency is added to the store and if there is a name, it is modified.', 'adminlocalizerpanelcontroller'),
                        $this->module->l('The price display format for the currency of Toman and Rial is modified.', 'adminlocalizerpanelcontroller'),
                        $this->module->l('This option is always disabled, you only need to activate it once when you need to modify the currency.', 'adminlocalizerpanelcontroller'),
                    ]
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'adminlocalizerpanelcontroller'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm($this->module);

        $simpleConfigs[] = 'Localizer_FixCurrency';
        $helper->setFieldsByArray($simpleConfigs);

        if (empty(NativeCorePrestashop::checkFiles())) {
            $output .= $this->module->renderModuleTemplate('admin/message-native.tpl', [
                'filesCore' => NativeCorePrestashop::getCoreChanges(),
                'changeFilesDone' => false,
                'date_test' => date('Y-m-d H:i:s'),
            ], true);
        }

        return $output . $helper->generateForm($fields_form);
    }

    public function getFontsOption() {
        $list = [];

        $directory = $this->module->getModulePath() . 'views/fonts/';

        $main = [];
        foreach (glob($directory . '/*', GLOB_ONLYDIR) as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $main[] = [
                'id' => $filename,
                'name' => $filename
            ];
        }
        if (!empty($main)) {
            $list[] = [
                'name' => $this->module->l('Localizer fonts', 'adminlocalizerpanelcontroller'),
                'query'=> $main
            ];
        }

        return $list;
    }

    public function sectionDocument() 
    {
        return $this->module->renderModuleTemplate('admin/document.tpl', [], true);
    }

    public function sectionAbout()
    {
        return 'sectionAbout';
    }
}