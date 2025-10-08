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

class Config
{
    public array $defaultConfigs;
    public string $perfixConfig;

    public function __construct(array $configs = [], string $perfixConfig = '')
    {
        $this->defaultConfigs = $configs;
        $this->perfixConfig = $perfixConfig;
    }

    public function getConfig($config)
    {
        $keyConfig = $this->getKeyConfig($config);

        $value = \Configuration::get($keyConfig);

        if ($value !== false) {
            return $value;
        }

        if (isset($this->defaultConfigs[$keyConfig])) {
            return $this->defaultConfigs[$keyConfig];
        }

        return false;
    }

    public static function updateConfigs(array $configs, $updateBySubmitValue = false, $enabledHtml = false, array $multilingualFields = []): void
    {
        foreach ($configs as $keyConfig => $valueConfig) {
            if ($updateBySubmitValue) {
                if (!is_int($keyConfig)) {
                    // Check if this is a multilingual field
                    if (in_array($keyConfig, $multilingualFields)) {
                        self::updateMultilingualConfig($keyConfig, $enabledHtml);
                    } else {
                        // Check if this might be a multilingual field by checking for language-specific keys
                        $isMultilingual = self::detectMultilingualField($keyConfig);
                        if ($isMultilingual) {
                            self::updateMultilingualConfig($keyConfig, $enabledHtml);
                        } else {
                            \Configuration::updateValue($keyConfig, \Tools::getValue($keyConfig), $enabledHtml);
                        }
                    }
                } else {
                    // Check if this is a multilingual field
                    if (in_array($valueConfig, $multilingualFields)) {
                        self::updateMultilingualConfig($valueConfig, $enabledHtml);
                    } else {
                        // Check if this might be a multilingual field by checking for language-specific keys
                        $isMultilingual = self::detectMultilingualField($valueConfig);
                        if ($isMultilingual) {
                            self::updateMultilingualConfig($valueConfig, $enabledHtml);
                        } else {
                            \Configuration::updateValue($valueConfig, \Tools::getValue($valueConfig), $enabledHtml);
                        }
                    }
                }
            } else {
                // update Config By Key Value Array!
                if (!is_int($keyConfig)) {
                    \Configuration::updateValue($keyConfig, $valueConfig, $enabledHtml);
                }
            }
        }
    }

    /**
     * Update multilingual configuration field
     *
     * @param string $fieldName
     * @param bool $enabledHtml
     * @return void
     */
    public static function updateMultilingualConfig($fieldName, $enabledHtml = false): void
    {
        
        $languages = \Language::getLanguages(true);
        $values = array();
        
        foreach ($languages as $language) {
            $langKey = $fieldName . '_' . $language['id_lang'];
            
            if (\Tools::getIsset($langKey)) {
                $values[$language['id_lang']] = \Tools::getValue($langKey);
            }
        }
        
        // Only update if we have values to save
        if (count($values)) {
            \Configuration::updateValue($fieldName, $values, $enabledHtml);
        }

    }

    /**
     * Detect if a field is multilingual by checking if language-specific keys exist in POST data
     *
     * @param string $fieldName
     * @return bool
     */
    public static function detectMultilingualField($fieldName): bool
    {
        $languages = \Language::getLanguages(false);
        
        // Check if any language-specific key exists in POST data
        foreach ($languages as $language) {
            $langKey = $fieldName . '_' . $language['id_lang'];
            if (\Tools::getValue($langKey) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public static function deleteConfigs(array $configs): void
    {
        foreach ($configs as $keyConfig => $valueConfig) {
            if (!is_int($keyConfig)) {
                \Configuration::deleteByName($keyConfig);
            } else {
                \Configuration::deleteByName($valueConfig);
            }
        }
    }

    public static function setMultipleValues(array $keys, $configName = null, $nullValues = false)
    {
        $values = [];

        foreach ($keys as $key) {
            $value = \Tools::getValue($key);

            if ($value) {
                $values[$key] = $value;
            } elseif (!$value && $nullValues) {
                $values[$key] = null;
            }
        }

        if (!empty($configName)) {
            \Configuration::updateValue($configName, json_encode($values), true);
        }

        return $values;
    }

    public function validateAndSaveConfig($configName, $validationMethod, $required = false)
    {
        $value = \Tools::getValue($configName);

        if (empty($value) && !$required) {
            return \Configuration::updateValue($configName, null);
        }

        if (method_exists('Validate', $validationMethod) && \Validate::$validationMethod($value)) {
            return \Configuration::updateValue($configName, $value);
        }

        return false;
    }

    private function getKeyConfig($config): string
    {
        if (empty($this->perfixConfig)) {
            return $config;
        }

        return $this->perfixConfig . '_' . $config;
    }
}