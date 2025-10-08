<?php

namespace PrestaSDK\V071\Dashboard\Widget;

use DateTimeImmutable;
use Exception;
use PrestaSDK\V071\Utility\HelperMethods;

class ActivityWidget extends AbstractDashboardWidget
{
    public function getName(): string
    {
        return 'activity';
    }

    public function getData(array $resolved = []): array
    {
        return $this->resolveItems($resolved);
    }

    public function present(array $activity, DashboardContext $context, array $allData = []): array
    {
        $title = $context->overrideConfig('activity.title', $context->l('Recent activity', 'activitywidget'));
        $description = $context->overrideConfig('activity.description', $context->l('View the latest events handled by the module.', 'activitywidget'));
        $emptyMessage = $context->overrideConfig('activity.emptyMessage', $context->l('Activity will show up as soon as events are recorded.', 'activitywidget'));
        $errorMessage = $context->overrideConfig('activity.errorMessage', $context->l('Activity feed could not be loaded.', 'activitywidget'));

        if (empty($activity)) {
            return [
                'title' => $title,
                'description' => $description,
                'state' => 'empty',
                'items' => [],
                'emptyMessage' => $emptyMessage,
                'errorMessage' => $errorMessage,
            ];
        }

        $items = [];
        foreach ($activity as $row) {
            $text = $this->buildActivityMessage($row);

            if ($text === '') {
                continue;
            }

            $items[] = [
                'meta' => $this->resolveActivityMeta($row),
                'text' => $text,
            ];
        }

        $state = empty($items) ? 'empty' : 'ready';

        return [
            'title' => $title,
            'description' => $description,
            'state' => $state,
            'items' => $items,
            'emptyMessage' => $emptyMessage,
            'errorMessage' => $errorMessage,
        ];
    }

    private function resolveItems(array $resolved): array
    {
        $result = $this->executeResolver($resolved);

        if (!is_array($result)) {
            $configured = $this->getOption('items', []);
            $result = is_array($configured) ? $configured : [];
        }

        $limit = (int) $this->getOption('limit', 0);
        if ($limit > 0) {
            $result = array_slice($result, 0, $limit);
        }

        return array_values(array_filter($result, 'is_array'));
    }

    private function buildActivityMessage(array $row): string
    {
        $ruleId = isset($row['id_rule']) ? (int) $row['id_rule'] : 0;

        $ruleData = null;
        if ($ruleId > 0) {
            $ruleData = $this->getModule()->getRuleRepository()->getRuleData($ruleId);
        }

        $displayData = $this->getModule()->prepareRuleLogDisplayData($row, $ruleData);

        return isset($displayData['message']) ? (string) $displayData['message'] : '';
    }

    private function resolveActivityMeta(array $row): array
    {
        $meta = isset($row['meta']) && is_array($row['meta']) ? $row['meta'] : [];

        $time = isset($meta['time']) ? (string) $meta['time'] : '';
        if ($time === '') {
            $time = $this->formatActivityTimeFallback($row);
        }

        $badges = [];
        if (isset($meta['badges']) && is_array($meta['badges'])) {
            foreach ($meta['badges'] as $badge) {
                if (!is_array($badge)) {
                    continue;
                }

                $label = isset($badge['label']) ? trim((string) $badge['label']) : '';
                if ($label === '') {
                    continue;
                }

                $badges[] = [
                    'label' => $label,
                    'href' => isset($badge['href']) ? (string) $badge['href'] : '',
                    'title' => isset($badge['title']) ? (string) $badge['title'] : '',
                    'variant' => isset($badge['variant']) ? (string) $badge['variant'] : '',
                ];
            }
        } elseif (isset($row['id_cart']) || isset($row['id_order'])) {
            // Backward compatibility with legacy payloads.
            if (isset($row['id_cart']) && (int) $row['id_cart'] > 0) {
                $badges[] = [
                    'label' => 'Cart #' . (int) $row['id_cart'],
                    'href' => HelperMethods::getCartLinkAdmin((int) $row['id_cart']),
                    'title' => 'View Cart Details',
                    'variant' => 'info',
                ];
            }

            if (isset($row['id_order']) && (int) $row['id_order'] > 0) {
                $badges[] = [
                    'label' => 'Order #' . (int) $row['id_order'],
                    'href' => HelperMethods::getOrderLinkAdmin((int) $row['id_order']),
                    'title' => 'View Order Details',
                    'variant' => 'success',
                ];
            }
        }

        return [
            'time' => $time,
            'badges' => $badges,
        ];
    }

    private function formatActivityTimeFallback(array $row): string
    {
        if (empty($row['date_add'])) {
            return '--:--';
        }

        try {
            $date = new DateTimeImmutable($row['date_add']);
        } catch (Exception $exception) {
            return (string) $row['date_add'];
        }

        if (class_exists('Tools')) {
            try {
                return \Tools::displayDate($date->format('Y-m-d H:i:s'), true);
            } catch (\Throwable $exception) {
                return \Tools::displayDate($date->format('Y-m-d H:i:s'), null, true);
            }
        }

        return $date->format('Y-m-d H:i');
    }

    public function getTemplates(): array
    {
        return $this->buildTemplateMap('activity.tpl');
    }
}
