<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
namespace PrestaSDK\V040\Utility;

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

    public static function updateConfigs(array $configs, $updateBySubmitValue = false, $enabledHtml = false): void
    {
        foreach ($configs as $keyConfig => $valueConfig) {
            if ($updateBySubmitValue) {
                if (!is_int($keyConfig)) {
                    \Configuration::updateValue($keyConfig, \Tools::getValue($keyConfig), $enabledHtml);
                } else {
                    \Configuration::updateValue($valueConfig, \Tools::getValue($valueConfig) , $enabledHtml);
                }
            } else {
                // update Config By Key Value Array!
                if (!is_int($keyConfig)) {
                    \Configuration::updateValue($keyConfig, $valueConfig, $enabledHtml);
                }
            }
        }
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