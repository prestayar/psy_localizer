<?php

namespace PrestaSDK\V071\Dashboard\Widget;

class TipsWidget extends AbstractDashboardWidget
{
    public function getName(): string
    {
        return 'tips';
    }

    public function getData(array $resolved = []): array
    {
        return $this->resolveTip($resolved);
    }

    public function present(array $tip, DashboardContext $context, array $allData = []): array
    {
        $title = $context->overrideConfig('tips.title', $context->l('Optimization tips', 'tipswidget'));
        $description = $context->overrideConfig('tips.description', $context->l('Discover ways to fine-tune your payment rules.', 'tipswidget'));
        $emptyMessage = $context->overrideConfig('tips.emptyMessage', $context->l('No tips available yet.', 'tipswidget'));
        $errorMessage = $context->overrideConfig('tips.errorMessage', $context->l('Tips could not be loaded.', 'tipswidget'));

        $state = empty($tip) || empty($tip['text']) ? 'empty' : 'ready';
        $tipKey = (string) $context->getConfigValue('options.tipKey', 'wsdkDashboardTipDismissed');

        return [
            'title' => $title,
            'description' => $description,
            'state' => $state,
            'tipKey' => $tipKey,
            'text' => isset($tip['text']) ? (string) $tip['text'] : '',
            'cta' => isset($tip['cta']) ? (string) $tip['cta'] : null,
            'link' => isset($tip['link']) ? (string) $tip['link'] : null,
            'emptyMessage' => $emptyMessage,
            'errorMessage' => $errorMessage,
        ];
    }

    public function getTemplates(): array
    {
        return $this->buildTemplateMap('tips.tpl');
    }

    private function resolveTip(array $resolved): array
    {
        $result = $this->executeResolver($resolved);

        if (!is_array($result)) {
            $result = [];
        }

        $text = isset($result['text']) ? (string) $result['text'] : ($this->getOption('text') ? (string) $this->getOption('text') : null);
        $cta = isset($result['cta']) ? (string) $result['cta'] : ($this->getOption('cta') ? (string) $this->getOption('cta') : null);
        $link = isset($result['link']) ? (string) $result['link'] : ($this->getOption('link') ? (string) $this->getOption('link') : null);

        return [
            'text' => $text,
            'cta' => $cta,
            'link' => $link,
        ];
    }
}
