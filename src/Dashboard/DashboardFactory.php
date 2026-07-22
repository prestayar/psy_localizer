<?php

namespace PrestaYar\Localizer\Dashboard;

use Configuration;
use Context;
use DateTimeImmutable;
use Module;
use PrestaSDK\V071\Dashboard\DashboardBuilder;
use PrestaSDK\V071\Dashboard\Widget\ChecklistWidget;
use PrestaSDK\V071\Dashboard\Widget\HealthWidget;

class DashboardFactory
{
    public static function create(Module $module, Context $context, array $productInfo = []): DashboardBuilder
    {
        return new DashboardBuilder(
            $module,
            $context,
            null,
            null,
            self::buildConfiguration($module, $productInfo)
        );
    }

    public static function buildConfiguration(Module $module, array $productInfo = []): array
    {
        return [
            'assets' => [
                'css' => $module->getPathUri() . 'views/css/wsdk_dashboard.css',
                'js' => $module->getPathUri() . 'views/js/wsdk_dashboard.js',
            ],
            'default_widgets' => [
                'health' => ['class' => HealthWidget::class],
                'checklist' => ['class' => ChecklistWidget::class],
            ],
            'widget_configuration' => [
                'checklist' => [
                    'resolver' => function (Module $module, array $resolved = [], array $configuration = []) {
                        return self::buildChecklist($module);
                    },
                ],
                'health' => [
                    'defaults' => [
                        'support' => [
                            'href' => self::resolveSupportUrl($productInfo),
                            'target' => '_blank',
                            'label' => $module->l('Contact support', 'dashboardfactory'),
                            'title' => $module->l('Need assistance? Our team can help.', 'dashboardfactory'),
                        ],
                        'version' => self::buildVersion($module, $productInfo),
                        'refresh' => [
                            'url' => $module->getModuleAdminLink('AdminLocalizerPanel', 'refresh'),
                            'label' => $module->l('Refresh version info', 'dashboardfactory'),
                            'title' => $module->l('Check for latest version information', 'dashboardfactory'),
                        ],
                    ],
                ],
            ],
            'presenter' => [
                'options' => [
                    'orientationKey' => 'wsdkDashboardNavOrientation',
                    'tipKey' => 'wsdkDashboardTipDismissed',
                ],
            ],
        ];
    }

    private static function buildChecklist(Module $module): array
    {
        $configureLink = self::sanitizeLink($module->getModuleAdminLink($module->configsAdminController, [
            'section' => 'configure',
        ])) ?: '#';

        return [
            [
                'id' => 'nativeActive',
                'ok' => self::hasNativeActive(),
                'link' => $configureLink,
                'label' => $module->l('Native active', 'dashboardfactory'),
            ],
        ];
    }

    private static function buildVersion(Module $module, array $productInfo = []): array
    {
        $installed = trim((string) $module->version);
        $latest = self::extractString($productInfo, ['update_info', 'version']) ?? '';
        $checkedAt = self::extractString($productInfo, ['meta', 'fetched_at']);

        if ($checkedAt === null) {
            $checkedAt = (new DateTimeImmutable('-1 hour'))->format(DATE_ATOM);
        }

        $updateAvailable = $installed !== '' && $latest !== '' && version_compare($latest, $installed, '>');

        $changelog = self::buildChangelogEntries($productInfo);

        $productUrl = self::sanitizeLink(self::extractString($productInfo, ['product_info', 'product_url']));
        if ($productUrl === null) {
            $productUrl = '';
        }

        $timeUpgrade = self::extractString($productInfo, ['update_info', 'time_upgrade']) ?? '';
        return [
            'installed' => $installed,
            'latest' => $latest,
            'checkedAt' => $checkedAt,
            'updateAvailable' => $updateAvailable,
            'timeUpgrade' => $timeUpgrade,
            'changelog' => $changelog,
            'productUrl' => $productUrl,
        ];
    }

    private static function hasNativeActive(): bool
    {
        $value = Configuration::get('Localizer_Native_Active');

        return $value !== false && $value !== null;
    }

    private static function extractString(array $data, array $path): ?string
    {
        $value = $data;

        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        if (is_scalar($value)) {
            $stringValue = trim((string) $value);

            return $stringValue === '' ? null : $stringValue;
        }

        return null;
    }

    private static function resolveSupportUrl(array $productInfo): string
    {
        $supportUrl = self::sanitizeLink(self::extractString($productInfo, ['product_info', 'support_url']));

        return $supportUrl ?: 'https://prestayar.com/support';
    }

    private static function buildChangelogEntries(array $productInfo): array
    {
        $entries = [];
        $rawChangelog = [];

        if (isset($productInfo['update_info']) && is_array($productInfo['update_info']) && array_key_exists('changelog', $productInfo['update_info'])) {
            $rawChangelog = $productInfo['update_info']['changelog'];
        }

        if (is_array($rawChangelog)) {
            foreach ($rawChangelog as $entry) {
                if (is_array($entry)) {
                    $title = isset($entry['title']) && is_scalar($entry['title']) ? trim((string) $entry['title']) : '';
                    if ($title === '') {
                        continue;
                    }

                    $link = isset($entry['link']) && is_scalar($entry['link']) ? trim((string) $entry['link']) : '';
                    if ($link !== '') {
                        $title .= ' (' . $link . ')';
                    }

                    $entries[] = $title;
                    continue;
                }

                if (is_scalar($entry)) {
                    $value = trim((string) $entry);
                    if ($value !== '') {
                        $entries[] = $value;
                    }
                }
            }
        }

        return $entries;
    }

    private static function sanitizeLink(?string $link): ?string
    {
        if ($link === null) {
            return null;
        }

        $trimmed = trim($link);
        if ($trimmed === '') {
            return null;
        }

        return $trimmed;
    }
}
