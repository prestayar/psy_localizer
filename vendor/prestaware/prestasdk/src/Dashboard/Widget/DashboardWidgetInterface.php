<?php

namespace PrestaSDK\V071\Dashboard\Widget;

interface DashboardWidgetInterface
{
    public function getName(): string;

    /**
     * @param array<string, mixed> $resolved
     *
     * @return array<mixed>
     */
    public function getData(array $resolved = []): array;

    /**
     * @param array<string, mixed> $data
     * @param DashboardContext $context
     * @param array<string, mixed> $allData
     *
     * @return array<string, mixed>
     */
    public function present(array $data, DashboardContext $context, array $allData = []): array;

    /**
     * @return array<string, string>
     */
    public function getTemplates(): array;
}
