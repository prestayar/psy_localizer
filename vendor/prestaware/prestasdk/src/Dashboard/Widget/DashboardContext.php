<?php

namespace PrestaSDK\V071\Dashboard\Widget;

use Module;

class DashboardContext
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var array
     */
    private $configuration;

    public function __construct(Module $module, array $configuration = [])
    {
        $this->module = $module;
        $this->configuration = $configuration;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getConfigValue(string $path, $default = null)
    {
        if ($path === '') {
            return $default;
        }

        $segments = explode('.', $path);
        $value = $this->configuration;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function overrideConfig(string $path, string $default, array $parameters = []): string
    {
        $value = $this->getConfigValue($path);

        if ($value === null) {
            return $default;
        }

        if (!empty($parameters)) {
            return vsprintf((string) $value, $parameters);
        }

        return (string) $value;
    }

    public function l(string $text, string $specific = 'dashboardpresenter', array $parameters = []): string
    {
        $translated = $this->module->l($text, $specific);

        if (!empty($parameters)) {
            return vsprintf($translated, $parameters);
        }

        return $translated;
    }
}
