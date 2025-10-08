<?php

namespace PrestaSDK\V071\Dashboard;

use Context;
use Module;
use PrestaSDK\V071\Dashboard\Widget\WidgetRegistry;

class DashboardBuilder
{
    /** @var Module */
    private $module;

    /** @var Context */
    private $context;

    /** @var DashboardDataProvider */
    private $dataProvider;

    /** @var DashboardPresenter */
    private $presenter;

    /** @var array<string, mixed> */
    private $configuration = [];

    public function __construct(
        Module $module,
        Context $context,
        ?DashboardDataProvider $dataProvider = null,
        ?DashboardPresenter $presenter = null,
        array $configuration = [],
        ?WidgetRegistry $widgetRegistry = null
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->configuration = $this->prepareConfiguration($configuration);

        $widgets = isset($this->configuration['widgets']) && is_array($this->configuration['widgets'])
            ? $this->configuration['widgets']
            : [];

        $widgetConfiguration = isset($this->configuration['widget_configuration']) && is_array($this->configuration['widget_configuration'])
            ? $this->configuration['widget_configuration']
            : [];

        $registry = $widgetRegistry ?: new WidgetRegistry();

        if ($dataProvider === null) {
            $this->dataProvider = new DashboardDataProvider(
                $module,
                $widgets,
                $widgetConfiguration,
                $this->getDefaultWidgets(),
                $registry
            );
        } else {
            $this->dataProvider = $dataProvider;
        }

        $presenterConfig = isset($this->configuration['presenter']) && is_array($this->configuration['presenter'])
            ? $this->configuration['presenter']
            : [];

        if ($presenter === null) {
            $this->presenter = new DashboardPresenter($module, $presenterConfig, $registry);
        } else {
            $this->presenter = $presenter;
        }
    }

    public function render(): string
    {
        $isRtl = false;
        if (isset($this->context->language) && is_object($this->context->language) && isset($this->context->language->is_rtl)) {
            $isRtl = (bool) $this->context->language->is_rtl;
        }

        $data = $this->dataProvider->getData();
        $viewModel = $this->presenter->present($data, $isRtl);

        $viewModel['dir'] = $isRtl ? 'rtl' : 'ltr';
        $viewModel['id'] = $this->resolveRootId();
        $viewModel['classes'] = $this->buildClasses($isRtl);
        $viewModel['attributes'] = $this->buildAttributes();
        $viewModel['container'] = $this->resolveContainerSelector($viewModel['id']);
        $viewModel['assets'] = $this->buildAssets();
        $viewModel['options'] = $this->buildOptions(isset($viewModel['options']) ? $viewModel['options'] : [], $isRtl);

        return $this->renderWithSmarty($viewModel);
    }

    private function renderWithSmarty(array $viewModel): string
    {
        $this->context->smarty->assign([
            'wsdkDashboard' => $viewModel,
        ]);

        return $this->context->smarty->fetch($this->getTemplatePath('dashboard.tpl'));
    }

    private function resolveRootId(): string
    {
        if (!empty($this->configuration['id']) && is_string($this->configuration['id'])) {
            $id = trim($this->configuration['id']);
            if ($id !== '') {
                return $id;
            }
        }

        return 'wsdk-dashboard';
    }

    private function resolveContainerSelector(string $id): string
    {
        if (!empty($this->configuration['container']) && is_string($this->configuration['container'])) {
            $selector = trim($this->configuration['container']);
            if ($selector !== '') {
                return $selector;
            }
        }

        return '#' . $id;
    }

    private function buildClasses(bool $isRtl): string
    {
        $classes = 'wsdk-dashboard';
        if (!empty($this->configuration['classes']) && is_string($this->configuration['classes'])) {
            $candidate = trim($this->configuration['classes']);
            if ($candidate !== '') {
                $classes = $candidate;
            }
        }

        $classes = preg_replace('/\s+/', ' ', trim($classes));

        if ($isRtl) {
            $rtlClass = 'wsdk-rtl';
            if (!empty($this->configuration['rtl_class']) && is_string($this->configuration['rtl_class'])) {
                $candidate = trim($this->configuration['rtl_class']);
                if ($candidate !== '') {
                    $rtlClass = $candidate;
                }
            }

            if ($rtlClass !== '') {
                $classes .= ' ' . $rtlClass;
            }
        }

        return trim($classes);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAttributes(): array
    {
        $attributes = [];
        if (!empty($this->configuration['attributes']) && is_array($this->configuration['attributes'])) {
            foreach ($this->configuration['attributes'] as $key => $value) {
                if (!is_string($key) || $key === '') {
                    continue;
                }

                if ($value === null) {
                    $attributes[$key] = null;
                    continue;
                }

                if (is_scalar($value)) {
                    $attributes[$key] = (string) $value;
                }
            }
        }

        if (!array_key_exists('data-module', $attributes)) {
            $attributes['data-module'] = $this->module->name;
        }

        if (!array_key_exists('data-wsdk-dashboard-root', $attributes)) {
            $attributes['data-wsdk-dashboard-root'] = '1';
        }

        return $attributes;
    }

    /**
     * @return array<string, string|null>
     */
    private function buildAssets(): array
    {
        $assets = [
            'css' => null,
            'js' => null,
        ];

        if (!empty($this->configuration['assets']) && is_array($this->configuration['assets'])) {
            if (isset($this->configuration['assets']['css']) && is_string($this->configuration['assets']['css'])) {
                $assets['css'] = $this->configuration['assets']['css'];
            }
            if (isset($this->configuration['assets']['js']) && is_string($this->configuration['assets']['js'])) {
                $assets['js'] = $this->configuration['assets']['js'];
            }
        }

        return $assets;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function buildOptions(array $options, bool $isRtl): array
    {
        $options['rtl'] = $isRtl;

        $extra = isset($this->configuration['options']) && is_array($this->configuration['options'])
            ? $this->configuration['options']
            : [];

        foreach ($extra as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $options[$key] = $value;
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareConfiguration(array $configuration): array
    {
        if (isset($configuration['sections']) && !isset($configuration['widgets'])) {
            $configuration['widgets'] = $configuration['sections'];
        }

        if (isset($configuration['section_configuration']) && !isset($configuration['widget_configuration'])) {
            $configuration['widget_configuration'] = $configuration['section_configuration'];
        }

        return $configuration;
    }

    /**
     * @return array<string, mixed>
     */
    private function getDefaultWidgets(): array
    {
        if (empty($this->configuration['default_widgets']) || !is_array($this->configuration['default_widgets'])) {
            return [];
        }

        return $this->configuration['default_widgets'];
    }

    private function getTemplateDirectory(): string
    {
        return dirname(__DIR__) . '/Resources/views/dashboard';
    }

    private function getTemplatePath(string $file): string
    {
        return 'file:' . $this->getTemplateDirectory() . '/' . ltrim($file, '/');
    }
}
