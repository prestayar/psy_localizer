<?php

namespace PrestaSDK\V071\Dashboard\Widget;

class ChecklistWidget extends AbstractDashboardWidget
{
    public function getName(): string
    {
        return 'checklist';
    }

    public function getData(array $resolved = []): array
    {
        return $this->resolveItems($resolved);
    }

    public function present(array $checklist, DashboardContext $context, array $allData = []): array
    {
        $title = $context->overrideConfig('checklist.title', $context->l('Configuration checklist', 'checklistwidget'));
        $description = $context->overrideConfig('checklist.description', $context->l('Track the steps required for a complete setup.', 'checklistwidget'));
        $emptyMessage = $context->overrideConfig('checklist.emptyMessage', $context->l('No checklist items available.', 'checklistwidget'));
        $errorMessage = $context->overrideConfig('checklist.errorMessage', $context->l('Checklist data could not be loaded.', 'checklistwidget'));
        $summaryCompleted = $context->overrideConfig('checklist.summaryCompleted', $context->l('All checklist items completed.', 'checklistwidget'));

        if (empty($checklist)) {
            return [
                'title' => $title,
                'description' => $description,
                'state' => 'empty',
                'items' => [],
                'summary' => $context->overrideConfig('checklist.summaryEmpty', $context->l('Checklist items will appear as you configure the module.', 'checklistwidget')),
                'completed' => false,
                'emptyMessage' => $emptyMessage,
                'errorMessage' => $errorMessage,
            ];
        }

        $items = $this->normalizeItems($checklist, $context);
        $remaining = 0;

        foreach ($items as $item) {
            if (empty($item['ok'])) {
                $remaining++;
            }
        }

        $summary = $remaining === 0
            ? $summaryCompleted
            : $context->overrideConfig('checklist.summaryRemaining', $context->l('%s item(s) remaining', 'checklistwidget', [$remaining]));

        return [
            'title' => $title,
            'description' => $description,
            'state' => 'ready',
            'items' => $items,
            'summary' => $summary,
            'completed' => count($items) > 0 && $remaining === 0,
            'emptyMessage' => $emptyMessage,
            'errorMessage' => $errorMessage,
        ];
    }

    public function buildProgress(array $checklist, DashboardContext $context): ?array
    {
        $items = $this->normalizeItems($checklist, $context);
        $total = count($items);
        $completed = 0;

        foreach ($items as $item) {
            if (!empty($item['ok'])) {
                $completed++;
            }
        }

        if ($total === 0) {
            return [
                'value' => 0,
                'label' => $context->overrideConfig('checklist.progress.label', $context->l('Configuration progress', 'checklistwidget')),
                'assistive' => $context->overrideConfig('checklist.emptyMessage', $context->l('No checklist items available.', 'checklistwidget')),
            ];
        }

        $value = (int) round(($completed / $total) * 100);
        $value = max(0, min(100, $value));

        $assistive = $context->l('%1$s of %2$s checklist items complete', 'checklistwidget', [$completed, $total]);

        return [
            'value' => $value,
            'label' => $context->overrideConfig('checklist.progress.label', $context->l('Configuration progress', 'checklistwidget')),
            'assistive' => $assistive,
        ];
    }

    private function resolveItems(array $resolved): array
    {
        $result = $this->executeResolver($resolved);

        if (!is_array($result)) {
            $configured = $this->getOption('items', []);
            $result = is_array($configured) ? $configured : [];
        }

        $normalized = [];

        foreach ($result as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = null;
            if (isset($item['id'])) {
                $id = (string) $item['id'];
            } elseif (isset($item['name'])) {
                $id = (string) $item['name'];
            }

            if ($id === null || $id === '') {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'ok' => !empty($item['ok']),
                'link' => isset($item['link']) ? (string) $item['link'] : '#',
                'label' => isset($item['label']) ? (string) $item['label'] : null,
            ];
        }

        return $normalized;
    }

    private function normalizeItems(array $checklist, DashboardContext $context): array
    {
        $items = [];

        foreach ($checklist as $row) {
            $ok = !empty($row['ok']);
            $id = isset($row['id']) ? (string) $row['id'] : null;
            $label = $this->resolveChecklistLabel($id, $context);

            if ($label === '' && isset($row['label'])) {
                $label = (string) $row['label'];
            }

            $link = isset($row['link']) ? trim((string) $row['link']) : '#';
            if ($link === '') {
                $link = '#';
            }

            $items[] = [
                'label' => $label,
                'ok' => $ok,
                'link' => $link,
            ];
        }

        return $items;
    }

    private function resolveChecklistLabel(?string $id, DashboardContext $context): string
    {
        if ($id !== null) {
            $labels = $context->getConfigValue('checklist.labels', []);
            if (is_array($labels) && isset($labels[$id])) {
                return $context->l((string) $labels[$id], 'checklistwidget');
            }
        }

        return '';
    }

    public function getTemplates(): array
    {
        return $this->buildTemplateMap('checklist.tpl');
    }
}
