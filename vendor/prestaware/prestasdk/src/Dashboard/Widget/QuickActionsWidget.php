<?php

namespace PrestaSDK\V071\Dashboard\Widget;

class QuickActionsWidget extends AbstractDashboardWidget
{
    public function getName(): string
    {
        return 'quickActions';
    }

    public function getData(array $resolved = []): array
    {
        return $this->resolveActions($resolved);
    }

    public function present(array $actions, DashboardContext $context, array $allData = []): array
    {
        if (empty($actions)) {
            $actions = $this->getDefaultActions($context);
        }

        $catalog = $this->getQuickActionsCatalog($context);

        $items = [];
        foreach ($actions as $action) {
            if (is_string($action)) {
                $action = [
                    'id' => $action,
                ];
            }

            if (!is_array($action)) {
                continue;
            }

            $id = $this->extractActionId($action);

            if ($id === null || $id === '') {
                continue;
            }

            $entry = isset($catalog[$id]) && is_array($catalog[$id]) ? $catalog[$id] : [];
            $label = $this->resolveTextValue($action, $entry, 'label', $context);
            $description = $this->resolveTextValue($action, $entry, 'description', $context);
            $icon = $this->resolveIconValue($action, $entry);

            $href = $this->resolveSimpleValue($action, $entry, 'href');
            $target = $this->resolveSimpleValue($action, $entry, 'target');
            $rel = $this->resolveSimpleValue($action, $entry, 'rel');

            $item = [
                'action' => $id,
                'label' => $label,
                'description' => $description,
                'icon' => $icon,
            ];

            if ($href !== null && $href !== '') {
                $item['href'] = $href;
            }
            if ($target !== null && $target !== '') {
                $item['target'] = $target;
            }
            if ($rel !== null && $rel !== '') {
                $item['rel'] = $rel;
            }

            $items[] = $item;
        }

        return [
            'title' => $context->overrideConfig('quickActions.title', $context->l('Quick Actions', 'quickactionswidget')),
            'description' => $context->overrideConfig('quickActions.description', $context->l('Quickly perform common maintenance tasks.', 'quickactionswidget')),
            'state' => empty($items) ? 'empty' : 'ready',
            'items' => $items,
            'help' => $context->overrideConfig('quickActions.help', $context->l('Use these links to manage rules and configuration quickly.', 'quickactionswidget')),
            'emptyMessage' => $context->overrideConfig('quickActions.emptyMessage', $context->l('No quick actions are available yet.', 'quickactionswidget')),
            'errorMessage' => $context->overrideConfig('quickActions.errorMessage', $context->l('Quick actions could not be loaded.', 'quickactionswidget')),
        ];
    }

    private function resolveActions(array $resolved): array
    {
        $result = $this->executeResolver($resolved);

        if (!is_array($result)) {
            $configured = $this->getOption('actions', []);
            $result = is_array($configured) ? $configured : [];
        }

        return $this->normalizeActions($result);
    }

    private function getQuickActionsCatalog(DashboardContext $context): array
    {
        $default = [
            'openSettings' => [
                'label' => 'Module settings',
                'description' => 'Review and update the module configuration.',
                'icon' => 'settings',
                'href' => $this->buildModuleConfigurationLink($context),
            ],
        ];

        $overrides = $context->getConfigValue('quickActions.catalog', []);
        if (is_array($overrides)) {
            foreach ($overrides as $key => $value) {
                if (!is_string($key) || !is_array($value)) {
                    continue;
                }

                $default[$key] = array_replace($default[$key] ?? [], $value);
            }
        }

        return $default;
    }

    /**
     * @param array<int, mixed> $actions
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeActions(array $actions): array
    {
        $normalized = [];

        foreach ($actions as $action) {
            if (is_string($action)) {
                $normalized[] = [
                    'id' => $action,
                ];

                continue;
            }

            if (!is_array($action)) {
                continue;
            }

            $id = $this->extractActionId($action);

            if ($id === null || $id === '') {
                continue;
            }

            $payload = ['id' => $id];

            foreach (['href', 'target', 'rel', 'label', 'description', 'icon'] as $key) {
                if (isset($action[$key])) {
                    $payload[$key] = (string) $action[$key];
                }
            }

            $normalized[] = $payload;
        }

        return $normalized;
    }

    private function extractActionId(array $action): ?string
    {
        if (isset($action['id'])) {
            return (string) $action['id'];
        }

        if (isset($action['action'])) {
            return (string) $action['action'];
        }

        return null;
    }

    private function resolveTextValue(array $action, array $entry, string $key, DashboardContext $context): string
    {
        if (isset($action[$key]) && is_string($action[$key]) && $action[$key] !== '') {
            return $action[$key];
        }

        if (isset($entry[$key]) && is_string($entry[$key]) && $entry[$key] !== '') {
            return $context->l($entry[$key], 'quickactionswidget');
        }

        return '';
    }

    private function resolveIconValue(array $action, array $entry): string
    {
        if (isset($action['icon']) && is_string($action['icon']) && $action['icon'] !== '') {
            return $action['icon'];
        }

        if (isset($entry['icon']) && is_string($entry['icon']) && $entry['icon'] !== '') {
            return $entry['icon'];
        }

        return 'help';
    }

    private function resolveSimpleValue(array $action, array $entry, string $key): ?string
    {
        if (isset($action[$key]) && is_string($action[$key]) && $action[$key] !== '') {
            return $action[$key];
        }

        if (isset($entry[$key]) && is_string($entry[$key]) && $entry[$key] !== '') {
            return $entry[$key];
        }

        return null;
    }

    private function getDefaultActions(DashboardContext $context): array
    {
        $defaults = $this->getOption('defaults', []);
        $configured = [];

        if (is_array($defaults)) {
            if (isset($defaults['actions']) && is_array($defaults['actions'])) {
                $configured = $defaults['actions'];
            } elseif (array_keys($defaults) === range(0, count($defaults) - 1)) {
                $configured = $defaults;
            }
        }

        if (!empty($configured)) {
            return $this->normalizeActions($configured);
        }

        return $this->normalizeActions([
            [
                'id' => 'openSettings',
                'href' => $this->buildModuleConfigurationLink($context) ?? '#',
            ],
        ]);
    }

    private function buildModuleConfigurationLink(DashboardContext $context): ?string
    {
        $module = $context->getModule();

        if (!method_exists($module, 'getModuleAdminLink')) {
            return null;
        }

        $link = $module->getModuleAdminLink('AdminModules', [
            'configure' => $module->name,
            'module_name' => $module->name,
        ]);

        if (!is_string($link) || trim($link) === '') {
            return null;
        }

        return $link;
    }

    public function getTemplates(): array
    {
        return $this->buildTemplateMap('quick-actions.tpl');
    }
}
