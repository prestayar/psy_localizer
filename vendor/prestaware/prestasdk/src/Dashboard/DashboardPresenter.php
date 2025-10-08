<?php

namespace PrestaSDK\V071\Dashboard;

use Module;
use PrestaSDK\V071\Dashboard\Widget\DashboardContext;
use PrestaSDK\V071\Dashboard\Widget\DashboardWidgetInterface;
use PrestaSDK\V071\Dashboard\Widget\WidgetRegistry;

class DashboardPresenter
{
    private $module;

    private $configuration = [];

    /**
     * @var DashboardContext
     */
    private $context;

    /**
     * @var WidgetRegistry
     */
    private $widgetRegistry;

    public function __construct(Module $module, array $configuration = [], ?WidgetRegistry $registry = null)
    {
        $this->module = $module;
        $this->configuration = $configuration;
        $this->context = new DashboardContext($module, $configuration);
        $this->widgetRegistry = $registry ?: new WidgetRegistry();
    }

    public function registerWidget(string $key, DashboardWidgetInterface $widget): void
    {
        $this->widgetRegistry->register($key, $widget);
    }

    public function getWidgetRegistry(): WidgetRegistry
    {
        return $this->widgetRegistry;
    }

    public function getContext(): DashboardContext
    {
        return $this->context;
    }

    public function present(array $data, bool $isRtl): array
    {
        $moduleInfo = [
            'name' => $this->module->name,
            'displayName' => $this->module->displayName,
            'version' => $this->module->version,
        ];

        $orientationKey = (string) $this->getConfigValue('options.orientationKey', 'wsdkDashboardNavOrientation');
        $tipKey = (string) $this->getConfigValue('options.tipKey', 'wsdkDashboardTipDismissed');

        $orientationConfig = $this->getOrientationConfiguration();

        $options = [
            'rtl' => $isRtl,
            'mocks' => (bool) $this->getConfigValue('options.mocks', false),
            'orientationKey' => $orientationKey,
            'tipKey' => $tipKey,
        ];

        $extraOptions = $this->getConfigValue('options.extra', []);
        if (is_array($extraOptions)) {
            $options = array_replace($options, $extraOptions);
        }

        $widgets = [];
        $templates = [];

        foreach ($this->widgetRegistry->all() as $key => $widget) {
            $name = $widget->getName();
            if ($name === '') {
                continue;
            }

            $raw = isset($data[$name]) && is_array($data[$name]) ? $data[$name] : [];
            $widgets[$name] = $widget->present($raw, $this->context, $data);

            foreach ($widget->getTemplates() as $templateKey => $path) {
                $templates[$templateKey] = $path;
            }
        }

        $viewModel = [
            'module' => $moduleInfo,
            'rtl' => $isRtl,
            'widgets' => $widgets,
            'templates' => $templates,
            'options' => $options,
        ];

        foreach ($widgets as $name => $widgetView) {
            $viewModel[$name] = $widgetView;
        }

        return $viewModel;
    }

    private function getOrientationConfiguration(): array
    {
        $default = (string) $this->getConfigValue('navigation.orientation.default', 'horizontal');
        $default = $default === 'vertical' ? 'vertical' : 'horizontal';

        return [
            'default' => $default,
        ];
    }

    private function getConfigValue(string $path, $default = null)
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

    private function overrideConfig(string $path, string $default, array $parameters = []): string
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

    private function l(string $text, array $parameters = []): string
    {
        $translated = $this->module->l($text, 'dashboardpresenter');

        if (!empty($parameters)) {
            return vsprintf($translated, $parameters);
        }

        return $translated;
    }
}
