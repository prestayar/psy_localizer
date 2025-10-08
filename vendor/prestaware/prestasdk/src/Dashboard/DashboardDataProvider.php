<?php

namespace PrestaSDK\V071\Dashboard;

use Module;
use PrestaSDK\V071\Dashboard\Widget\DashboardWidgetInterface;
use PrestaSDK\V071\Dashboard\Widget\WidgetRegistry;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

class DashboardDataProvider
{
    /** @var Module */
    private $module;

    /** @var array<int, array{key: ?string, factory: mixed, options: array<string, mixed>}> */
    private $widgets = [];

    /** @var array<string, mixed> */
    private $widgetConfiguration = [];

    /** @var array<string, mixed> */
    private $defaultWidgets = [];

    /** @var WidgetRegistry|null */
    private $widgetRegistry;

    public function __construct(
        Module $module,
        array $widgets = [],
        array $widgetConfiguration = [],
        array $defaultWidgets = [],
        ?WidgetRegistry $widgetRegistry = null
    ) {
        $this->module = $module;
        $this->widgetConfiguration = $this->prepareWidgetConfiguration($widgetConfiguration);
        $this->defaultWidgets = $defaultWidgets;

        $this->widgets = $this->prepareWidgets($widgets);
        $this->widgetRegistry = $widgetRegistry;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $resolved = [];

        foreach ($this->widgets as $definition) {
            $factory = $definition['factory'];
            $key = $definition['key'];
            $options = $definition['options'];
            $widget = $this->resolveWidget($factory, $resolved, $key, $options);

            if (!$widget instanceof DashboardWidgetInterface) {
                continue;
            }

            $name = $widget->getName();
            if ($name === '') {
                continue;
            }

            $registryKey = $key !== null ? $key : $name;

            if ($this->widgetRegistry !== null && $registryKey !== null && $registryKey !== '') {
                $this->widgetRegistry->register($registryKey, $widget);
            }

            $resolved[$name] = $widget->getData($resolved);
        }

        return $resolved;
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareWidgetConfiguration(array $configuration): array
    {
        $normalized = [];

        foreach ($configuration as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $normalized[$key] = is_array($value) ? $value : [];
        }

        return $normalized;
    }

    /**
     * @return array<int, array{key: ?string, factory: mixed, options: array<string, mixed>}>
     */
    private function prepareWidgets(array $widgets): array
    {
        $overrides = [];
        $extras = [];
        $order = 0;

        foreach ($widgets as $key => $definition) {
            $entry = [
                'key' => is_string($key) && $key !== '' ? $key : null,
                'definition' => $definition,
                'enabled' => $this->isEntryEnabled($definition),
                'order' => $order++,
            ];

            if ($entry['key'] !== null) {
                $overrides[$entry['key']] = $entry;
            } else {
                $extras[] = $entry;
            }
        }

        $normalized = [];

        foreach ($this->defaultWidgets as $key => $definition) {
            if (isset($overrides[$key])) {
                $entry = $overrides[$key];
                unset($overrides[$key]);

                if (!$entry['enabled']) {
                    continue;
                }

                $normalizedDefinition = $this->normalizeDefinition($entry['key'], $entry['definition']);
            } else {
                $normalizedDefinition = $this->normalizeDefinition($key, $definition);
            }

            if ($normalizedDefinition !== null) {
                $normalized[] = $normalizedDefinition;
            }
        }

        $remaining = array_merge(array_values($overrides), $extras);
        usort($remaining, function (array $left, array $right): int {
            return $left['order'] <=> $right['order'];
        });

        foreach ($remaining as $entry) {
            if (!$entry['enabled']) {
                continue;
            }

            $normalizedDefinition = $this->normalizeDefinition($entry['key'], $entry['definition']);
            if ($normalizedDefinition !== null) {
                $normalized[] = $normalizedDefinition;
            }
        }

        return $normalized;
    }

    /**
     * @param mixed $definition
     */
    private function isEntryEnabled($definition): bool
    {
        if (is_array($definition) && array_key_exists('enabled', $definition)) {
            return (bool) $definition['enabled'];
        }

        return true;
    }

    /**
     * @param mixed $definition
     *
     * @return array{key: ?string, factory: mixed, options: array<string, mixed>}|null
     */
    private function normalizeDefinition(?string $key, $definition): ?array
    {
        $options = [];

        if (is_array($definition)) {
            if (array_key_exists('enabled', $definition) && !$definition['enabled']) {
                return null;
            }

            if (isset($definition['options']) && is_array($definition['options'])) {
                $options = $definition['options'];
            }

            if (isset($definition['resolver']) && is_callable($definition['resolver'])) {
                $options['resolver'] = $definition['resolver'];
            }

            if (isset($definition['class'])) {
                $factory = function (Module $module, array $resolved, array $config) use ($definition) {
                    $class = $definition['class'];
                    if (!is_string($class) || $class === '') {
                        return null;
                    }

                    return new $class($module, $config);
                };
            } elseif (isset($definition['factory']) && is_callable($definition['factory'])) {
                $factory = $definition['factory'];
            } elseif (isset($definition['instance']) && $definition['instance'] instanceof DashboardWidgetInterface) {
                $instance = $definition['instance'];
                $factory = function () use ($instance) {
                    return $instance;
                };
            } elseif (isset($definition['callable']) && is_callable($definition['callable'])) {
                $factory = $definition['callable'];
            } else {
                $factory = null;
            }
        } else {
            $factory = null;
        }

        if ($factory === null) {
            if ($definition instanceof DashboardWidgetInterface) {
                $instance = $definition;
                $factory = function () use ($instance) {
                    return $instance;
                };
            } elseif (is_callable($definition)) {
                $factory = $definition;
            } elseif (is_string($definition) && $definition !== '') {
                $factory = function (Module $module, array $resolved, array $config) use ($definition) {
                    return new $definition($module, $config);
                };
            }
        }

        if ($factory === null) {
            return null;
        }

        if ($key !== null && isset($this->widgetConfiguration[$key]) && is_array($this->widgetConfiguration[$key])) {
            $options = array_replace($options, $this->widgetConfiguration[$key]);
        }

        return [
            'key' => $key,
            'factory' => $factory,
            'options' => $options,
        ];
    }

    /**
     * @param mixed $factory
     * @param array<string, mixed> $resolved
     * @param array<string, mixed> $options
     */
    private function resolveWidget($factory, array $resolved, ?string $key, array $options): ?DashboardWidgetInterface
    {
        if ($factory instanceof DashboardWidgetInterface) {
            return $factory;
        }

        if (!is_callable($factory)) {
            return null;
        }

        try {
            $result = $this->invokeFactory($factory, $resolved, $options);
        } catch (Throwable $exception) {
            return null;
        }

        if ($result instanceof DashboardWidgetInterface) {
            return $result;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $resolved
     * @param array<string, mixed> $options
     *
     * @return mixed
     */
    private function invokeFactory(callable $factory, array $resolved, array $options)
    {
        $arguments = [$this->module, $resolved, $options];
        $reflection = $this->createReflection($factory);

        if ($reflection) {
            $parameterCount = $reflection->getNumberOfParameters();
            if (method_exists($reflection, 'isVariadic') && $reflection->isVariadic()) {
                $parameterCount = count($arguments);
            }

            $arguments = array_slice($arguments, 0, min($parameterCount, count($arguments)));
        }

        return $factory(...$arguments);
    }

    private function createReflection(callable $factory)
    {
        if ($factory instanceof \Closure || is_string($factory)) {
            return new ReflectionFunction($factory);
        }

        if (is_array($factory) && count($factory) === 2) {
            if (is_object($factory[0])) {
                return new ReflectionMethod($factory[0], $factory[1]);
            }

            if (is_string($factory[0])) {
                return new ReflectionMethod($factory[0], $factory[1]);
            }
        }

        if (is_object($factory) && method_exists($factory, '__invoke')) {
            return new ReflectionMethod($factory, '__invoke');
        }

        return null;
    }
}
