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

use PrestaSDK\V071\PrestaSDKModule;
use PrestaYar\Localizer\Native\NativeCorePrestashop;
use PrestaYar\Localizer\Traits\UseDate;

class LocalizerModule extends PrestaSDKModule
{
    use UseDate;
    
    public function hookDisplayHeader() 
    {
        if (!$this->getFromConfigs('Native_Active')) {
            return;
        }

        if ($this->context->language->is_rtl) {
            if ($this->context->language->iso_code == 'fa') {
                $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/persian-datepicker.min.css');
                $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/persian-date.min.js', 'all');
                $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/persian-datepicker.min.js', 'all');
                $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/convert-date.js', 'all');
                $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/brithdayJalali.js', 'all');
            }
        }

        if ($this->getFromConfigs('TinyMCE')) {
            $this->context->controller->addCSS($this->getPathUri() . 'views/libs/prism/prism.css');
            $this->context->controller->addJS($this->getPathUri() . 'views/libs/prism/prism.js', 'all');
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (!$this->getFromConfigs('Native_Active')) {
            return;
        }
        
        if ($this->context->language->iso_code == 'fa') {
            $jalaliDate = \Configuration::get('Localizer_JalaliDate');
            \Media::addJsDef(array('Localizer_JalaliDate' => $jalaliDate));

            if (!empty($jalaliDate)) {
                if ($this->isNewTheme()) {
                    $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/persian-datepicker.min.css');

                    $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/persian-date.min.js', 'all');
                    $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/persian-datepicker.min.js', 'all');
                    $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/convert-date.js', 'all');


                } else {
                    $path_timepicker = _PS_JS_DIR_ . 'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js';
                    $check_timepicker = array_search($path_timepicker, $this->context->controller->js_files);

                    if ($check_timepicker) {
                        $this->context->controller->removeJS($path_timepicker, false);
                    }

                    $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/jquery.ui.datepicker-fa.js', 'all');
                    $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/persian-date.min.js', 'all');
                    $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/native/convert-date.js', 'all');
                    $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/main-old-pages.js', 'all');

                    if ($check_timepicker) {
                        $this->context->controller->addJS($path_timepicker, 'all');
                    }
                }
            }

            if ($this->isNewTheme()) {
                $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/main.js', 'all');
            }

        }

        if ($this->context->language->is_rtl) {
            clearstatcache();

            if (!empty($this->getFromConfigs('BackofficeFont'))) {
                $this->context->controller->addCSS($this->getPathUri() . '/views/css/admin/localizer-font.css');
            }
            $this->context->controller->addCSS($this->getPathUri() . '/views/css/admin/localizer-fix-rtl.css');
        }

    }

    public function hookActionAdminControllerSetMedia()
    {
        if (
            !$this->getFromConfigs('Native_Active')
            || !$this->getFromConfigs('TinyMCE')
            || $this->context->language->iso_code !== 'fa'
            || \Tools::getValue('controller') === 'AdminTranslations'
        ) {
            return;
        }

        $coreTinyMceFiles = array_map(
            static function (string $path): string {
                return strtok(\Media::getJSPath($path), '?');
            },
            [
                _PS_JS_DIR_ . 'tiny_mce/tiny_mce.js',
                _PS_JS_DIR_ . 'admin/tinymce.inc.js',
            ]
        );
        $this->context->controller->js_files = array_values(array_filter(
            $this->context->controller->js_files,
            static function (string $path) use ($coreTinyMceFiles): bool {
                return !in_array(strtok($path, '?'), $coreTinyMceFiles, true);
            }
        ));

        $fileManagerUrl = $this->context->shop->getBaseURL(true) . basename(_PS_ADMIN_DIR_) . '/filemanager/dialog.php';
        $fileManagerUrl .= '?popup=1&field_id=my_field_id';
        $fileManagerUrl .= '&token=' . \Tools::getAdminTokenLite('AdminLegacyLayout');

        \Media::addJsDef([
            'Localizer_TinyMCE' => true,
            'localizer_editor_skin_tinymce' => $this->getPathUri() . 'views/css/admin/localizer-editor-skin.css',
            'localizer_moduleUrl' => $this->getModuleUrl(),
            'localizer_editor_iso' => $this->context->language->iso_code,
            'localizer_directionality' => !empty($this->context->language->is_rtl) ? 'rtl' : 'ltr',
            'localizer_base_url' => $this->getPsBaseUrl(),
            'extra_plugins' => 'codemirror abbr edit_attributes',
            'extra_plugins_toolbar' => '',
            'default_font' => 'arial,helvetica,sans-serif',
            'default_font_size' => '15px',
            'word_limit' => $this->l('The maximum characters have been reached!'),
            'psy_localizer_filemanager_url' => $fileManagerUrl,
        ]);

        $this->context->controller->addCSS($this->getPathUri() . 'views/libs/prism/prism.css');
        $this->context->controller->addJS($this->getPathUri() . 'views/libs/prism/prism.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/libs/tinymce/tinymce.min.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/localizer-editor-tinySetup.js');
    }

    public function hookActionAdminLoginControllerSetMedia()
    {
        if (!$this->getFromConfigs('Native_Active')) {
            return;
        }

        if ($this->context->language->is_rtl) {
            if (!empty($this->getFromConfigs('BackofficeFont'))) {
                $this->context->controller->addCss($this->getPathUri() . '/views/css/admin/localizer-font.css');
            }
            $this->context->controller->addCss($this->getPathUri() . '/views/css/admin/localizer-fix-rtl.css');
        }
    }

    public function hookActionObjectUpdateBefore($params)
    {
        (new NativeCorePrestashop())->convertDate($params['object']);
    }
    public function hookActionObjectAddBefore($params)
    {
        (new NativeCorePrestashop())->convertDate($params['object']);
    }

    public function getDataModifiedRecords($data) {
        if (!$this->getFromConfigs('Native_Active')) {
            return $data;
        }

        if (empty($this->getFromConfigs('JalaliDate'))) {
            return $data;
        }

        return (new NativeCorePrestashop())->getDataModifiedRecords($data);
    }

    public function hookActionOrderGridDataModifier($params) {
        $params['data'] = $this->getDataModifiedRecords($params['data']);
    }

    public function hookActionCustomerGridDataModifier($params)
    {
        $params['data'] = $this->getDataModifiedRecords($params['data']);
    }

    public function hookActionAddressGridDataModifier($params)
    {
        $params['data'] = $this->getDataModifiedRecords($params['data']);
    }    
    
    public function hookActionCartRuleGridDataModifier($params)
    {
        $params['data'] = $this->getDataModifiedRecords($params['data']);
    }

    public function isNewTheme(): bool
    {
        return \Tools::getIsset('_token');
    }

    public function isConvertDate(): bool
    {
        if (!\Configuration::get('Localizer_NativeActive')) {
            return false;
        }

        if (\Configuration::get('Localizer_JalaliDate')) {
            return true;
        }

        return false;
    }
}
