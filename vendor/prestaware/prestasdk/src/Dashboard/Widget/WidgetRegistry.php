<?php

namespace PrestaSDK\V071\Dashboard\Widget;

class WidgetRegistry
{
    /**
     * @var DashboardWidgetInterface[]
     */
    private $widgets = [];

    public function register(string $key, DashboardWidgetInterface $widget): void
    {
        $this->widgets[$key] = $widget;
    }

    /**
     * @return array<string, DashboardWidgetInterface>
     */
    public function all(): array
    {
        return $this->widgets;
    }

    public function has(string $key): bool
    {
        return isset($this->widgets[$key]);
    }

    public function get(string $key): ?DashboardWidgetInterface
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->widgets[$key];
    }

    public function present(string $key, DashboardContext $context, array $data, array $allData = []): array
    {
        $widget = $this->get($key);
        if ($widget === null) {
            return [];
        }

        return $widget->present($data, $context, $allData);
    }
}
