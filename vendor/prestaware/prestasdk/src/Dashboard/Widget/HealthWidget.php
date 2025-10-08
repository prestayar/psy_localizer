<?php

namespace PrestaSDK\V071\Dashboard\Widget;

use Context;
use DateTimeImmutable;
use Exception;
use Throwable;

class HealthWidget extends AbstractDashboardWidget
{
    public function getName(): string
    {
        return 'health';
    }

    public function getData(array $resolved = []): array
    {
        $result = $this->executeResolver($resolved);

        if (!is_array($result)) {
            $result = [];
        }

        $defaults = $this->getOption('defaults', []);
        if (is_array($defaults)) {
            $result = array_replace($defaults, $result);
        }

        $module = $this->getModule();
        $versionData = $this->prepareVersionData($result, $resolved);
        $checklistData = isset($resolved['checklist']) && is_array($resolved['checklist']) ? $resolved['checklist'] : [];

        $support = $this->resolveSupportMetadata($result);
        $refresh = $this->resolveRefreshMetadata($result);
        $licenseInfo = $this->resolveLicenseMetadata($result);
        $license = $licenseInfo['data'];
        $licenseContext = $licenseInfo['provided'];

        return [
            'active' => (bool) $module->active,
            'licensed' => $this->resolveLicensed($result),
            'updateAvailable' => $this->resolveUpdateAvailable($result, $versionData),
            'errors' => $this->resolveErrors($result),
            'ps' => defined('_PS_VERSION_') ? _PS_VERSION_ : null,
            'php' => PHP_VERSION,
            'configCompleteness' => $this->calculateCompleteness($checklistData),
            'supportUrl' => $support['href'],
            'supportTarget' => $support['target'],
            'supportRel' => $support['rel'],
            'supportLabel' => $support['label'],
            'supportTitle' => $support['title'],
            'refresh' => $refresh,
            'moduleManageUrl' => isset($result['moduleManageUrl']) ? $result['moduleManageUrl'] : null,
            'moduleManageTarget' => isset($result['moduleManageTarget']) ? $result['moduleManageTarget'] : null,
            'moduleManageRel' => isset($result['moduleManageRel']) ? $result['moduleManageRel'] : null,
            'license' => $license,
            'licenseContext' => $licenseContext,
            'criticalUpdate' => isset($result['criticalUpdate']) ? $result['criticalUpdate'] : null,
            'updateSeverity' => isset($result['updateSeverity']) ? $result['updateSeverity'] : null,
            'version' => $versionData,
        ];
    }

    public function present(array $health, DashboardContext $context, array $allData = []): array
    {
        $title = $context->overrideConfig('health.title', $context->l('Module health', 'healthwidget'));
        $description = $context->overrideConfig('health.description', $context->l('Monitor licensing, status, and completeness.', 'healthwidget'));
        $emptyMessage = $context->overrideConfig('health.emptyMessage', $context->l('Health data is not available yet.', 'healthwidget'));
        $errorMessage = $context->overrideConfig('health.errorMessage', $context->l('Health metrics could not be loaded.', 'healthwidget'));
        $emptySummary = $context->overrideConfig('health.summaryEmpty', $context->l('Module health will appear after the first sync.', 'healthwidget'));
        $badgesEmpty = $context->overrideConfig('health.badges.emptyLabel', $context->l('No status available yet', 'healthwidget'));

        $checklistProvided = array_key_exists('checklist', $allData);
        $rawChecklist = $checklistProvided && is_array($allData['checklist']) ? $allData['checklist'] : [];
        $progress = $this->buildProgressView($rawChecklist, $context, $checklistProvided);
        $support = $this->buildSupportLink($health, $context);

        $rawVersion = isset($health['version']) && is_array($health['version']) ? $health['version'] : [];
        $version = $this->presentVersion($rawVersion, $context);

        $healthData = $health;
        unset($healthData['version']);
        unset($healthData['refresh']);

        if (empty(array_filter($healthData, static function ($value) {
            return $value !== null && $value !== '' && $value !== [] && $value !== false;
        }))) {
            return [
                'title' => $title,
                'description' => $description,
                'state' => 'empty',
                'badges' => [
                    'items' => [],
                    'emptyLabel' => $badgesEmpty,
                ],
                'progress' => $progress,
                'metrics' => [],
                'summary' => $emptySummary,
                'emptyMessage' => $emptyMessage,
                'errorMessage' => $errorMessage,
                'version' => $version,
                'support' => $support,
            ];
        }

        $updateAvailable = !empty($health['updateAvailable']) || !empty($version['updateAvailable']);
        $active = isset($health['active']) ? (bool) $health['active'] : false;
        $licensed = isset($health['licensed']) ? (bool) $health['licensed'] : false;
        $moduleManageLink = isset($health['moduleManageUrl']) ? $this->normalizeLink((string) $health['moduleManageUrl']) : null;
        if ($moduleManageLink === null) {
            $moduleManageLink = $this->resolveModuleManagerLink();
        }
        $moduleManageTarget = isset($health['moduleManageTarget']) ? (string) $health['moduleManageTarget'] : null;
        if ($moduleManageLink !== null && ($moduleManageTarget === null || $moduleManageTarget === '')) {
            $moduleManageTarget = '_self';
        }
        $moduleManageRel = isset($health['moduleManageRel']) ? (string) $health['moduleManageRel'] : null;

        $licenseData = isset($health['license']) && is_array($health['license']) ? $health['license'] : [];
        $licenseContext = !empty($health['licenseContext']) || !empty($licenseData);
        $licenseLink = isset($licenseData['url']) ? $this->normalizeLink((string) $licenseData['url']) : null;

        if ($licenseLink !== null) {
            $licenseData['url'] = $licenseLink;
        } elseif (isset($licenseData['url'])) {
            unset($licenseData['url']);
        }

        $badges = [
            'items' => [],
            'emptyLabel' => $badgesEmpty,
            'modalTarget' => 'wsdk-dashboard-version-modal',
            'triggerTitle' => isset($version['triggerTitle']) ? $version['triggerTitle'] : null,
            'triggerAssistive' => isset($version['triggerLabel']) ? $version['triggerLabel'] : null,
        ];

        $badges['items'][] = $this->buildModuleBadge(
            $active,
            $moduleManageLink,
            $moduleManageTarget,
            $moduleManageRel,
            $context
        );

        $badges['items'][] = $this->buildLicenseBadge(
            $licensed,
            $licenseData,
            $licenseContext,
            $context
        );

        $badges['items'][] = $this->buildUpdateBadge(
            $updateAvailable,
            $health,
            $version,
            $context
        );

        $refreshBadge = $this->buildRefreshBadge($health, $context);
        if ($refreshBadge !== null) {
            $badges['items'][] = $refreshBadge;
        }

        $issues = isset($health['errors']) ? (int) $health['errors'] : 0;
        if ($issues < 0) {
            $issues = 0;
        }

        $summary = $active
            ? $context->overrideConfig('health.summaryOk', $context->l('All systems operational', 'healthwidget'))
            : $context->overrideConfig('health.summaryDisabled', $context->l('Module is disabled', 'healthwidget'));

        $summary = $context->overrideConfig('health.summaryAttention', $context->l('Module health needs attention', 'healthwidget'));

        return [
            'title' => $title,
            'description' => $description,
            'state' => 'ready',
            'badges' => $badges,
            'progress' => $progress,
            'metrics' => [],
            'summary' => $summary,
            'emptyMessage' => $emptyMessage,
            'errorMessage' => $errorMessage,
            'version' => $version,
            'support' => $support,
        ];
    }

    public function getTemplates(): array
    {
        return array_merge(
            $this->buildTemplateMap('health.tpl'),
            $this->buildTemplateMap('version-modal.tpl', 'versionModal')
        );
    }

    /**
     * @param array<string, mixed> $result
     * @param array<string, mixed> $resolved
     *
     * @return array{installed: string, latest: string, checkedAt: string|null, updateAvailable: bool, changelog: array<int, string>, productUrl: ?string}
     */
    private function prepareVersionData(array $result, array $resolved): array
    {
        $data = [];

        if (isset($result['version']) && is_array($result['version'])) {
            $data = $result['version'];
        }

        if (empty($data)) {
            foreach (['installed', 'latest', 'checkedAt', 'updateAvailable', 'changelog', 'productUrl', 'timeUpgrade'] as $key) {
                if (array_key_exists($key, $result)) {
                    $data[$key] = $result[$key];
                }
            }
        }

        if (empty($data) && isset($resolved['version']) && is_array($resolved['version'])) {
            $data = $resolved['version'];
        }

        $configured = $this->getOption('version', []);
        if (is_array($configured)) {
            $data = array_replace($configured, $data);
        }

        return $this->normalizeVersionData($data);
    }

    /**
     * @param array<string, mixed> $version
     *
     * @return array{installed: string, latest: string, checkedAt: string|null, updateAvailable: bool, changelog: array<int, string>, productUrl: ?string}
     */
    private function normalizeVersionData(array $version): array
    {
        $module = $this->getModule();

        $installed = isset($version['installed']) ? (string) $version['installed'] : $module->version;
        $latest = isset($version['latest']) ? (string) $version['latest'] : $installed;
        $checkedAt = isset($version['checkedAt']) ? (string) $version['checkedAt'] : null;
        $updateAvailable = isset($version['updateAvailable']) ? (bool) $version['updateAvailable'] : null;
        $timeUpgrade = isset($version['timeUpgrade']) ? (string) $version['timeUpgrade'] : null;

        $changelog = [];
        if (!empty($version['changelog']) && is_array($version['changelog'])) {
            foreach ($version['changelog'] as $entry) {
                if (is_scalar($entry)) {
                    $changelog[] = (string) $entry;
                }
            }
        }

        $productUrl = isset($version['productUrl']) ? (string) $version['productUrl'] : null;

        if ($checkedAt === null) {
            $checkedAt = (new DateTimeImmutable('-1 hour'))->format(DATE_ATOM);
        }

        if ($updateAvailable === null && $installed !== '' && $latest !== '') {
            $updateAvailable = version_compare($latest, $installed, '>');
        }

        return [
            'installed' => $installed,
            'latest' => $latest,
            'checkedAt' => $checkedAt,
            'updateAvailable' => (bool) $updateAvailable,
            'changelog' => $changelog,
            'productUrl' => $productUrl,
            'timeUpgrade' => $timeUpgrade,
        ];
    }

    /**
     * @param array<string, mixed> $result
     *
     * @return array{data: array<string, mixed>, provided: bool}
     */
    private function resolveLicenseMetadata(array $result): array
    {
        $license = [];
        $provided = false;

        $configured = $this->getOption('license');
        if (is_array($configured)) {
            $provided = true;
            $license = $this->mergeLicenseMap($license, $configured);
        }

        if (array_key_exists('license', $result) && is_array($result['license'])) {
            $provided = true;
            $license = $this->mergeLicenseMap($license, $result['license']);
        }

        $legacy = [];
        $map = [
            'licenseUrl' => 'url',
            'licenseTarget' => 'target',
            'licenseRel' => 'rel',
            'licenseStatus' => 'status',
            'licenseExpiresAt' => 'expiresAt',
            'licenseExpiresIn' => 'expiresIn',
            'licenseExpiresSoon' => 'expiresSoon',
            'licenseExpired' => 'expired',
        ];

        foreach ($map as $legacyKey => $normalizedKey) {
            if (array_key_exists($legacyKey, $result)) {
                $legacy[$normalizedKey] = $result[$legacyKey];
                $provided = true;
            }
        }

        if (!empty($legacy)) {
            $license = $this->mergeLicenseMap($license, $legacy);
        }

        return [
            'data' => $license,
            'provided' => $provided,
        ];
    }

    /**
     * @param array<string, mixed> $result
     */
    private function resolveLicensed(array $result): bool
    {
        if (array_key_exists('licensed', $result)) {
            return (bool) $result['licensed'];
        }

        $configured = $this->getOption('licensed');
        if ($configured !== null) {
            return (bool) $configured;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $result
     * @param array<string, mixed> $version
     */
    private function resolveUpdateAvailable(array $result, array $version): bool
    {
        if (array_key_exists('updateAvailable', $result)) {
            return (bool) $result['updateAvailable'];
        }

        $configured = $this->getOption('updateAvailable');
        if ($configured !== null) {
            return (bool) $configured;
        }

        if (!empty($version['updateAvailable'])) {
            return true;
        }

        $installed = isset($version['installed']) ? (string) $version['installed'] : '';
        $latest = isset($version['latest']) ? (string) $version['latest'] : '';

        if ($installed === '' || $latest === '') {
            return false;
        }

        return version_compare($latest, $installed, '>');
    }

    /**
     * @param array<string, mixed> $result
     */
    private function resolveErrors(array $result): int
    {
        if (array_key_exists('errors', $result) && is_numeric($result['errors'])) {
            return max(0, (int) $result['errors']);
        }

        $configured = $this->getOption('errors');
        if (is_numeric($configured)) {
            return max(0, (int) $configured);
        }

        return 0;
    }

    /**
     * @param array<int, array<string, mixed>> $checklist
     */
    private function calculateCompleteness(array $checklist): int
    {
        if (empty($checklist)) {
            return 0;
        }

        $total = 0;
        $completed = 0;

        foreach ($checklist as $item) {
            if (!is_array($item)) {
                continue;
            }

            $total++;
            if (!empty($item['ok'])) {
                $completed++;
            }
        }

        if ($total === 0) {
            return 0;
        }

        $value = (int) round(($completed / $total) * 100);

        return max(0, min(100, $value));
    }

    /**
     * @param array<string, mixed> $result
     *
     * @return array{url: ?string, label: ?string, title: ?string, target: ?string, rel: ?string}
     */
    private function resolveRefreshMetadata(array $result): array
    {
        $defaults = [
            'url' => null,
            'label' => null,
            'title' => null,
            'target' => null,
            'rel' => null,
        ];

        $configured = $this->getOption('refresh', []);
        if (is_array($configured)) {
            $defaults = array_replace($defaults, $this->filterStringMap($configured));
        }

        if (isset($result['refresh']) && is_array($result['refresh'])) {
            $defaults = array_replace($defaults, $this->filterStringMap($result['refresh']));
        }

        if (isset($result['refreshUrl'])) {
            $defaults['url'] = (string) $result['refreshUrl'];
        }
        if (isset($result['refreshLabel'])) {
            $defaults['label'] = (string) $result['refreshLabel'];
        }
        if (isset($result['refreshTitle'])) {
            $defaults['title'] = (string) $result['refreshTitle'];
        }
        if (isset($result['refreshTarget'])) {
            $defaults['target'] = (string) $result['refreshTarget'];
        }
        if (isset($result['refreshRel'])) {
            $defaults['rel'] = (string) $result['refreshRel'];
        }

        return $defaults;
    }

    /**
     * @param array<string, mixed> $result
     *
     * @return array{href: ?string, target: ?string, rel: ?string, label: ?string, title: ?string}
     */
    private function resolveSupportMetadata(array $result): array
    {
        $defaults = [
            'href' => null,
            'target' => null,
            'rel' => null,
            'label' => null,
            'title' => null,
        ];

        $configured = $this->getOption('support', []);
        if (is_array($configured)) {
            $defaults = array_replace($defaults, $this->filterStringMap($configured));
        }

        if (isset($result['support']) && is_array($result['support'])) {
            $defaults = array_replace($defaults, $this->filterStringMap($result['support']));
        }

        if (isset($result['supportUrl'])) {
            $defaults['href'] = (string) $result['supportUrl'];
        }
        if (isset($result['supportTarget'])) {
            $defaults['target'] = (string) $result['supportTarget'];
        }
        if (isset($result['supportRel'])) {
            $defaults['rel'] = (string) $result['supportRel'];
        }
        if (isset($result['supportLabel'])) {
            $defaults['label'] = (string) $result['supportLabel'];
        }
        if (isset($result['supportTitle'])) {
            $defaults['title'] = (string) $result['supportTitle'];
        }

        if ($defaults['target'] === '_blank' && !$defaults['rel']) {
            $defaults['rel'] = 'noopener';
        }

        return $defaults;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, ?string>
     */
    private function filterStringMap(array $values): array
    {
        $filtered = [];

        foreach ($values as $key => $value) {
            if ($value === null) {
                $filtered[$key] = null;
                continue;
            }

            if (is_scalar($value)) {
                $filtered[$key] = (string) $value;
            }
        }

        return $filtered;
    }

    /**
     * @param array<string, mixed> $license
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private function mergeLicenseMap(array $license, array $values): array
    {
        $allowed = [
            'url' => 'string',
            'target' => 'string',
            'rel' => 'string',
            'status' => 'string',
            'expiresAt' => 'string',
            'expiresIn' => 'int',
            'expiresSoon' => 'bool',
            'expired' => 'bool',
        ];

        foreach ($allowed as $key => $type) {
            if (!array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];

            if ($value === null) {
                unset($license[$key]);

                continue;
            }

            if ($type === 'string') {
                $stringValue = trim((string) $value);
                if ($stringValue === '') {
                    unset($license[$key]);

                    continue;
                }

                $license[$key] = $stringValue;

                continue;
            }

            if ($type === 'int') {
                if (is_numeric($value)) {
                    $license[$key] = (int) $value;
                } else {
                    unset($license[$key]);
                }

                continue;
            }

            if ($type === 'bool') {
                $license[$key] = (bool) $value;
            }
        }

        return $license;
    }

    private function buildSupportLink(array $health, DashboardContext $context): ?array
    {
        if (empty($health['supportUrl'])) {
            return null;
        }

        $target = isset($health['supportTarget']) ? (string) $health['supportTarget'] : '_blank';
        $rel = isset($health['supportRel']) ? (string) $health['supportRel'] : null;
        if ($target === '_blank' && !$rel) {
            $rel = 'noopener';
        }

        $label = isset($health['supportLabel']) && $health['supportLabel'] !== null
            ? (string) $health['supportLabel']
            : $context->overrideConfig('health.support.label', $context->l('Contact support', 'healthwidget'));
        $title = isset($health['supportTitle']) && $health['supportTitle'] !== null
            ? (string) $health['supportTitle']
            : $context->overrideConfig('health.support.title', $context->l('Get help from our support team', 'healthwidget'));

        return [
            'label' => $label,
            'title' => $title,
            'href' => (string) $health['supportUrl'],
            'target' => $target,
            'rel' => $rel,
        ];
    }



    private function presentVersion(array $version, DashboardContext $context): array
    {
        $module = $this->getModule();
        $title = $context->overrideConfig('version.title', $context->l('Version & updates', 'healthwidget'));
        $description = $context->overrideConfig('version.description', $context->l('Stay updated with the latest releases and changelog entries.', 'healthwidget'));
        $emptyMessage = $context->overrideConfig('version.emptyMessage', $context->l('Version information is not available.', 'healthwidget'));
        $errorMessage = $context->overrideConfig('version.errorMessage', $context->l('Version feed could not be loaded.', 'healthwidget'));
        $triggerLabel = $context->overrideConfig('version.triggerLabel', $context->l('Version details', 'healthwidget'));
        $triggerTitle = $context->overrideConfig('version.triggerTitle', $context->l('View module version and changelog', 'healthwidget'));
        $closeLabel = $context->overrideConfig('version.closeLabel', $context->l('Close', 'healthwidget'));
        $productCta = $context->overrideConfig('version.productCta', $context->l('Open module page', 'healthwidget'));
        $statusUpdate = $context->overrideConfig('version.status.updateAvailable', $context->l('Update available', 'healthwidget'));
        $statusOk = $context->overrideConfig('version.status.upToDate', $context->l('Your module is up to date.', 'healthwidget'));

        if (empty($version)) {
            return [
                'title' => $title,
                'description' => $description,
                'state' => 'empty',
                'installed' => $module->version,
                'latest' => $module->version,
                'updateAvailable' => false,
                'changelog' => [],
                'checkedAtLabel' => null,
                'statusBadge' => [
                    'label' => $context->overrideConfig('version.status.empty', $context->l('No data available', 'healthwidget')),
                    'type' => 'muted',
                ],
                'emptyMessage' => $emptyMessage,
                'errorMessage' => $errorMessage,
                'triggerLabel' => $triggerLabel,
                'triggerTitle' => $triggerTitle,
                'closeLabel' => $closeLabel,
                'productCta' => $productCta,
                'productUrl' => null,
            ];
        }

        $installed = $version['installed'] ?? $module->version;
        $latest = $version['latest'] ?? $installed;

        $updateAvailable = !empty($version['updateAvailable']);
        if (!$updateAvailable && $installed && $latest) {
            $updateAvailable = version_compare($latest, $installed, '>');
        }

        $timeUpgradeLabel = null;
        if (!empty($version['timeUpgrade'])) {
            try {
                $date = new DateTimeImmutable($version['timeUpgrade']);
                if (class_exists('Tools')) {
                    $timeUpgradeLabel = \Tools::displayDate($date->format('Y-m-d H:i:s'));
                } else {
                    $timeUpgradeLabel = $date->format('Y-m-d H:i');
                }
            } catch (Exception $exception) {
                $timeUpgradeLabel = $version['timeUpgrade'];
            }
        }

        $checkedAtLabel = null;
        if (!empty($version['checkedAt'])) {
            try {
                $date = new DateTimeImmutable($version['checkedAt']);
                if (class_exists('Tools')) {
                    $checkedAtLabel = \Tools::displayDate($date->format('Y-m-d H:i:s'), true);
                } else {
                    $checkedAtLabel = $date->format('Y-m-d H:i');
                }
            } catch (Exception $exception) {
                $checkedAtLabel = $version['checkedAt'];
            }
        }

        // Check if latest version is unknown
        if (empty($version['latest'])) {
            $statusBadge = [
                'label' => $context->overrideConfig('version.status.unknown', $context->l('Update status unknown', 'healthwidget')),
                'type' => 'muted',
            ];
        } else {
            $statusBadge = $updateAvailable
                ? [
                    'label' => $statusUpdate,
                    'type' => 'warning',
                ]
                : [
                    'label' => $statusOk,
                    'type' => 'success',
                ];
        }

        $changelog = [];
        if (!empty($version['changelog']) && is_array($version['changelog'])) {
            $changelog = array_slice($version['changelog'], 0, 3);
        }

        return [
            'title' => $title,
            'description' => $description,
            'state' => 'ready',
            'installed' => $installed,
            'latest' => $latest,
            'updateAvailable' => $updateAvailable,
            'changelog' => $changelog,
            'checkedAtLabel' => $checkedAtLabel,
            'timeUpgradeLabel' => $timeUpgradeLabel,
            'statusBadge' => $statusBadge,
            'emptyMessage' => $emptyMessage,
            'errorMessage' => $errorMessage,
            'triggerLabel' => $triggerLabel,
            'triggerTitle' => $triggerTitle,
            'closeLabel' => $closeLabel,
            'productCta' => $productCta,
            'productUrl' => isset($version['productUrl']) ? $version['productUrl'] : null,
        ];
    }

    private function buildProgressView(array $checklist, DashboardContext $context, bool $checklistProvided): ?array
    {
        if (!$checklistProvided) {
            return null;
        }

        $total = 0;
        $completed = 0;

        foreach ($checklist as $item) {
            if (!is_array($item)) {
                continue;
            }

            $total++;
            if (!empty($item['ok'])) {
                $completed++;
            }
        }

        if ($total === 0) {
            return [
                'value' => 0,
                'label' => $context->overrideConfig('checklist.progress.label', $context->l('Configuration progress', 'healthwidget')),
                'assistive' => $context->overrideConfig('checklist.emptyMessage', $context->l('No checklist items available.', 'healthwidget')),
            ];
        }

        $value = (int) round(($completed / $total) * 100);
        $value = max(0, min(100, $value));

        return [
            'value' => $value,
            'label' => $context->overrideConfig('checklist.progress.label', $context->l('Configuration progress', 'healthwidget')),
            'assistive' => $context->l('%1$s of %2$s checklist items complete', 'healthwidget', [$completed, $total]),
        ];
    }

    private function buildModuleBadge(
        bool $active,
        ?string $manageUrl,
        ?string $target,
        ?string $rel,
        DashboardContext $context
    ): array {
        $enabledLabel = $context->overrideConfig('health.badges.module.enabled', $context->l('MODULE ENABLED', 'healthwidget'));
        $disabledLabel = $context->overrideConfig('health.badges.module.disabled', $context->l('MODULE DISABLED', 'healthwidget'));

        if ($active) {
            return [
                'label' => $enabledLabel,
                'type' => 'success',
                'state' => 'enabled',
                'assistive' => $context->overrideConfig('health.badges.module.enabledAssistive', $context->l('Module is currently enabled', 'healthwidget')),
            ];
        }

        $badge = [
            'label' => $disabledLabel,
            'type' => 'danger',
            'state' => 'disabled',
            'assistive' => $context->overrideConfig('health.badges.module.disabledAssistive', $context->l('Open the module manager to enable the module', 'healthwidget')),
        ];

        if ($manageUrl !== null) {
            $badge['action'] = 'link';
            $badge['href'] = $manageUrl;
            $badge['title'] = $context->overrideConfig('health.badges.module.disabledTitle', $context->l('Go to module manager', 'healthwidget'));
            if ($target !== null && $target !== '') {
                $badge['target'] = $target;
            }
            if ($rel !== null && $rel !== '') {
                $badge['rel'] = $rel;
            }
        }

        return $badge;
    }

    private function buildLicenseBadge(
        bool $licensed,
        array $license,
        bool $licenseContext,
        DashboardContext $context
    ): array {
        $status = $this->resolveLicenseStatus($license, $licensed, $licenseContext);

        if ($status === 'invalid') {
            $badge = [
                'label' => $context->overrideConfig('health.badges.license.invalid', $context->l('LICENSE INVALID / EXPIRED', 'healthwidget')),
                'type' => 'danger',
                'state' => 'license-invalid',
                'assistive' => $context->overrideConfig('health.badges.license.invalidAssistive', $context->l('Resolve license issues to restore coverage', 'healthwidget')),
            ];
        } elseif ($status === 'expiring') {
            $badge = [
                'label' => $context->overrideConfig('health.badges.license.expiring', $context->l('LICENSE EXPIRING SOON', 'healthwidget')),
                'type' => 'warning',
                'state' => 'license-warning',
                'assistive' => $context->overrideConfig('health.badges.license.expiringAssistive', $context->l('Renew your license to avoid interruptions', 'healthwidget')),
            ];
        } elseif ($status === 'verified') {
            $badge = [
                'label' => $context->overrideConfig('health.badges.license.valid', $context->l('LICENSE VERIFIED', 'healthwidget')),
                'type' => 'success',
                'state' => 'license-ok',
                'assistive' => $context->overrideConfig('health.badges.license.validAssistive', $context->l('License verification is valid', 'healthwidget')),
            ];
        } else {
            $badge = [
                'label' => $context->overrideConfig('health.badges.license.free', $context->l('FREE', 'healthwidget')),
                'type' => 'info',
                'state' => 'license-free',
                'assistive' => $context->overrideConfig('health.badges.license.freeAssistive', $context->l('No license is required for this module', 'healthwidget')),
            ];
        }

        $licenseUrl = isset($license['url']) ? (string) $license['url'] : null;
        $licenseTarget = isset($license['target']) ? (string) $license['target'] : null;
        $licenseRel = isset($license['rel']) ? (string) $license['rel'] : null;

        if ($licenseTarget !== null && $licenseTarget === '') {
            $licenseTarget = null;
        }

        if ($licenseRel !== null && $licenseRel === '') {
            $licenseRel = null;
        }

        if ($licenseUrl !== null && $licenseUrl === '') {
            $licenseUrl = null;
        }

        if ($status !== 'free' && $licenseUrl !== null) {
            $badge['action'] = 'link';
            $badge['href'] = $licenseUrl;
            if (!isset($badge['title'])) {
                $badge['title'] = $context->overrideConfig('health.badges.license.linkTitle', $context->l('Manage license', 'healthwidget'));
            }
            if ($licenseTarget !== null && $licenseTarget !== '') {
                $badge['target'] = $licenseTarget;
            }
            if ($licenseRel !== null && $licenseRel !== '') {
                $badge['rel'] = $licenseRel;
            }
        }

        return $badge;
    }

    private function buildUpdateBadge(
        bool $updateAvailable,
        array $health,
        array $version,
        DashboardContext $context
    ): array {
        $modalBadge = [
            'action' => 'version',
        ];

        if ($updateAvailable) {
            $severity = $this->resolveUpdateSeverity($health, $version);
            $isCritical = $severity === 'critical';

            $modalBadge['label'] = $isCritical
                ? $context->overrideConfig('health.badges.update.critical', $context->l('CRITICAL UPDATE REQUIRED', 'healthwidget'))
                : $context->overrideConfig('health.badges.update.available', $context->l('UPDATE AVAILABLE', 'healthwidget'));
            $modalBadge['type'] = $isCritical ? 'danger' : 'warning';
            $modalBadge['state'] = $isCritical ? 'update-critical' : 'update-available';
            $modalBadge['assistive'] = $isCritical
                ? $context->overrideConfig('health.badges.update.criticalAssistive', $context->l('Open the changelog to apply the security update', 'healthwidget'))
                : $context->overrideConfig('health.badges.update.availableAssistive', $context->l('Review changes and update the module', 'healthwidget'));

            return $modalBadge;
        } 
        
        if (empty($version['latest'])) {
            $modalBadge['label'] = $context->overrideConfig('health.badges.update.ok', $context->l('UNKNOWN', 'healthwidget'));
            $modalBadge['type'] = 'info';
            $modalBadge['state'] = 'update-unknown';
            $modalBadge['assistive'] = $context->overrideConfig('health.badges.update.unknownAssistive', $context->l('Module version is unknown', 'healthwidget'));
            
            return $modalBadge;            
        }

        $modalBadge['label'] = $context->overrideConfig('health.badges.update.ok', $context->l('UP TO DATE', 'healthwidget'));
        $modalBadge['type'] = 'success';
        $modalBadge['state'] = 'update-ok';
        $modalBadge['assistive'] = $context->overrideConfig('health.badges.update.okAssistive', $context->l('Module is running the latest version', 'healthwidget'));

        return $modalBadge;
    }

    /**
     * Build refresh badge with processing health data and context
     */
    private function buildRefreshBadge(array $health, DashboardContext $context): ?array
    {
        // Get URL from health data - return null if not available
        $href = $health['refreshUrl'] ?? $health['refresh']['url'] ?? '';
        if ($href === '') {
            return null;
        }

        // Get other refresh properties with defaults
        $target = $health['refreshTarget'] ?? $health['refresh']['target'] ?? '_self';
        $rel = $health['refreshRel'] ?? $health['refresh']['rel'] ?? null;
        $label = $health['refreshLabel'] ?? $health['refresh']['label'] ?? 
                $context->overrideConfig('health.refresh.label', $context->l('Refresh', 'healthwidget'));
        $title = $health['refreshTitle'] ?? $health['refresh']['title'] ?? 
                $context->overrideConfig('health.refresh.title', $context->l('Refresh health data', 'healthwidget'));

        // Build badge array
        $badge = [
            'action' => 'link',
            'href' => $href,
            'type' => 'default',
            'state' => 'refresh',
            'icon' => 'refresh',
            'iconClass' => 'material-icons wsdk-refresh-icon',
            'iconOnly' => true,
            'title' => $title,
            'assistive' => $label,
        ];

        // Add optional properties only if they have meaningful values
        if ($target !== '_self') {
            $badge['target'] = $target;
        }
        if ($rel !== null && $rel !== '') {
            $badge['rel'] = $rel;
        }

        return $badge;
    }

    private function resolveLicenseStatus(array $license, bool $licensed, bool $hasContext): string
    {
        $status = $this->resolveExplicitLicenseStatus($license);
        if ($status !== null) {
            return $status;
        }

        if (!empty($license['expired'])) {
            return 'invalid';
        }

        if (!empty($license['expiresSoon'])) {
            return 'expiring';
        }

        if (isset($license['expiresIn']) && is_numeric($license['expiresIn'])) {
            $remaining = (int) $license['expiresIn'];
            if ($remaining <= 0) {
                return 'invalid';
            }

            if ($remaining <= 30) {
                return 'expiring';
            }
        }

        if (!empty($license['expiresAt']) && is_string($license['expiresAt'])) {
            try {
                $expiry = new DateTimeImmutable($license['expiresAt']);
                $now = new DateTimeImmutable('now');
                $diff = $now->diff($expiry);

                if ($diff->invert === 1) {
                    return 'invalid';
                }

                if ($diff->days !== false && $diff->days <= 30) {
                    return 'expiring';
                }
            } catch (Exception $exception) {
                // Ignore parsing errors and fall back to default status.
            }
        }

        if (!$licensed) {
            return $hasContext ? 'invalid' : 'free';
        }

        return $hasContext ? 'verified' : 'free';
    }

    /**
     * @param array<string, mixed> $license
     */
    private function resolveExplicitLicenseStatus(array $license): ?string
    {
        if (!isset($license['status'])) {
            return null;
        }

        $status = strtolower(trim((string) $license['status']));
        if ($status === '') {
            return null;
        }

        if (in_array($status, ['free', 'open', 'none', 'unlicensed'], true)) {
            return 'free';
        }

        if (in_array($status, ['invalid', 'expired', 'revoked', 'inactive', 'blocked'], true)) {
            return 'invalid';
        }

        if (in_array($status, ['expiring', 'warning', 'soon', 'grace', 'renewal'], true)) {
            return 'expiring';
        }

        if (in_array($status, ['valid', 'verified', 'active', 'licensed'], true)) {
            return 'verified';
        }

        return null;
    }

    private function resolveUpdateSeverity(array $health, array $version): string
    {
        $candidates = [];

        if (isset($health['updateSeverity'])) {
            $candidates[] = strtolower((string) $health['updateSeverity']);
        }

        if (!empty($health['criticalUpdate'])) {
            return 'critical';
        }

        if (!empty($health['updateCritical'])) {
            return 'critical';
        }

        if (isset($version['severity'])) {
            $candidates[] = strtolower((string) $version['severity']);
        }

        if (!empty($version['critical'])) {
            return 'critical';
        }

        if (!empty($version['security'])) {
            return 'critical';
        }

        foreach ($candidates as $candidate) {
            if (in_array($candidate, ['critical', 'security', 'major', 'high'], true)) {
                return 'critical';
            }
        }

        return 'normal';
    }

    private function normalizeLink(?string $link): ?string
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

    private function resolveModuleManagerLink(): ?string
    {
        if (!class_exists(Context::class)) {
            return null;
        }

        try {
            $context = Context::getContext();
            if (!$context || !isset($context->link) || !$context->link) {
                return null;
            }

            $link = $context->link->getAdminLink('AdminModulesSf');

            return $this->normalizeLink($link);
        } catch (Throwable $exception) {
            return null;
        }
    }
}
