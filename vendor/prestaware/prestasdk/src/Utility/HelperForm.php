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

class HelperForm extends \HelperForm
{
    /**
     * Create a new HelperForm.
     *
     * @return void
     */
    public function __construct($module, $formUrl = null, $token = null, $submitName = null)
    {
        parent::__construct();

        if ($module) {
            $this->initHelperForm($module, $formUrl, $token, $submitName);
        }
    }

    /**
     * Set Initialize with Set Module(By Object Or Name) and Set Default Values !
     *
     * @param $ObjectOrName
     * @return self
     */
    public function initHelperForm($module, $formUrl, $token, $submitName)
    {
        /* set Module */
        if (is_object($module) && ($module instanceof \Module)) {
            $this->module = $module;
        } elseif (is_string($module) && \Module::isInstalled($module) && \Module::isEnabled($module)) {
            $this->module = \Module::getInstanceByName($module);
        } else {
            return $this;
        }

        $controllerType = isset(\Context::getContext()->controller->controller_type) ? \Context::getContext()->controller->controller_type : '';

        /* set Form Url */
        if ($formUrl) {
            $this->currentIndex = $formUrl;
        } elseif ($controllerType == 'admin' || $controllerType == 'moduleadmin') {
            $this->currentIndex = $this->module->getModuleAdminLink(\Tools::getValue('controller'), $this->module->getRequestSection(), false);
        } elseif ($controllerType == 'front' || $controllerType == 'modulefront') {
            $this->currentIndex = $this->module->getModuleFrontLink(\Tools::getValue('controller'), $this->module->getRequestSection());
        }

        /* set Token */
        if ($token) {
            $this->token = $token;
        } elseif ($controllerType == 'admin' || $controllerType == 'moduleadmin') {
            $this->token = \Tools::getAdminTokenLite(\Tools::getValue('controller'));
        }

        /* set Name Controller & Lang */
        $this->name_controller = $this->module->name;
        $defaultLang = (int) \Configuration::get('PS_LANG_DEFAULT');
        $this->default_form_language = $defaultLang;
        $this->allow_employee_form_lang = $defaultLang;

        /* set Submit Name */
        if ($submitName) {
            $this->submit_action = $submitName;
        } else {
            $this->submit_action = 'submit' . $this->module->name;
        }

        $this->languages = \Context::getContext()->controller->_languages;

        return $this;
    }

    /**
     * set Fields Value By Array of Fields
     *
     * @param array $fieldsArray
     * @param array $multilingualFields Array of field names that are multilingual
     * @return object
     */
    public function setFieldsByArray(array $fieldsArray, array $multilingualFields = [])
    {
        if (!empty($fieldsArray)) {
            foreach ($fieldsArray as $field) {
                if (in_array($field, $multilingualFields)) {
                    // Handle multilingual field
                    $this->setMultilingualField($field);
                } else {
                    // Handle regular field
                    $this->fields_value[$field] = \Configuration::get($field);
                }
            }
        }

        return $this;
    }

    /**
     * Set multilingual field values for all languages
     *
     * @param string $fieldName
     * @param mixed $defaultValue Default value if configuration is empty
     * @return $this
     */
    public function setMultilingualField($fieldName, $defaultValue = '')
    {
        $languages = \Language::getLanguages(false);
        $this->fields_value[$fieldName] = array();
        
        foreach ($languages as $language) {
            $value = \Configuration::get($fieldName, $language['id_lang']);
            $this->fields_value[$fieldName][$language['id_lang']] = $value ?: $defaultValue;
        }

        return $this;
    }

    /**
     * Set multiple multilingual fields at once
     *
     * @param array $multilingualFields Array of field names with optional default values
     * @return $this
     */
    public function setMultilingualFields(array $multilingualFields)
    {
        foreach ($multilingualFields as $fieldName => $defaultValue) {
            if (is_int($fieldName)) {
                // If no default value provided, use field name as key
                $this->setMultilingualField($defaultValue);
            } else {
                // Use provided default value
                $this->setMultilingualField($fieldName, $defaultValue);
            }
        }

        return $this;
    }

    /**
     * set Field Value By String Field.
     *
     * @param string $field
     * @return object
     */
    public function setFieldValue($field, $value = null, $id_lang = null, $id_shop_group = null, $id_shop = null)
    {
        if ($value) {
            $this->fields_value[$field] = $value;
        } else {
            $this->fields_value[$field] = \Configuration::get($field, $id_lang, $id_shop_group, $id_shop);
        }

        return $this;
    }

    /**
     * set Fields By get Json from Configuration
     *
     * @param string $configName
     * @return object
     */
    public function setFieldsByJsonConfig($configName)
    {
        $json = \Configuration::get($configName);
        $array = json_decode($json, true);
        $array = is_array($array) ? $array : [];
        return $this->setFieldsByKeyValsArray($array);
    }

    /**
     * set Fields Key Values Array
     *
     * @param string $keyVals
     * @return object
     */
    public function setFieldsByKeyValsArray(array $keyVals)
    {
        foreach ($keyVals as $key => $value) {
            $this->fields_value[$key] = $value;
        }

        return $this;
    }

    /**
     * set Value for Group Fields
     *
     * @param string $fields
     * @param mix $groupValue
     * @param bool $forceAll
     *
     * @return object
     */
    public function setGroupFields($fields, $groupValue = null, $forceAll = false)
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }

        foreach ($fields as $field) {
            if ($forceAll || !isset($this->fields_value[$field])) {
                $this->fields_value[$field] = $groupValue;
            }
        }

        return $this;
    }

    /**
     * set Checkbox fields as active
     * @param $baseFieldName checkbox field name
     * @param array $checkedFields array values for set check boxes as true
     * @return $this
     */
    public function setCheckBoxFields($baseFieldName, array $checkedFields)
    {
        foreach ($checkedFields as $checkField) {
            $this->fields_value[$baseFieldName . '_' . $checkField] = true;
        }

        return $this;
    }

    public function generateForm($fields_form)
    {
        foreach ($fields_form as &$form) {
            foreach ($form['form']['input'] as &$field) {
                if (isset($field['type']) && $field['type'] == 'switch') {
                    if (!isset($field['values'])) {
                        $field['values'] = [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled')],
                            [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled')],
                        ];
                    }
                }
            }
        }

        return parent::generateForm($fields_form);
    }
}