<?php

namespace PrestaSDK\V071\Dashboard\Widget;

use Configuration;
use Context;
use Module;

abstract class AbstractDashboardWidget implements DashboardWidgetInterface
{
    /** @var Module */
    protected $module;

    /** @var array<string, mixed> */
    protected $configuration;

    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(Module $module, array $configuration = [])
    {
        $this->module = $module;
        $this->configuration = $configuration;
    }

    protected function getModule(): Module
    {
        return $this->module;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getOption(string $key, $default = null)
    {
        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }

        return $default;
    }

    /**
     * @param array<string, mixed> $resolved
     * @param string $key
     *
     * @return mixed
     */
    protected function executeResolver(array $resolved, string $key = 'resolver')
    {
        $resolver = $this->getOption($key);

        if (is_callable($resolver)) {
            return $resolver($this->module, $resolved, $this->configuration);
        }

        return null;
    }

    protected function l(string $string, array $params = [], string $class = 'dashboard'): string
    {
        if ($params) {
            $string = vsprintf($string, $params);
        }

        if (method_exists($this->module, 'l')) {
            return $this->module->l($string, $class);
        }

        return $string;
    }

    public function getTemplates(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    protected function buildTemplateMap(string $file, ?string $key = null): array
    {
        $key = $key ?: $this->getName();

        return [
            $key => 'file:' . dirname(__DIR__, 2) . '/Resources/views/dashboard/widgets/' . ltrim($file, '/'),
        ];
    }

    protected function buildModuleAdminLink(string $controller, array $parameters = []): ?string
    {
        if (!method_exists($this->module, 'getModuleAdminLink')) {
            return null;
        }

        $link = $this->module->getModuleAdminLink($controller, $parameters);

        if (!is_string($link) || trim($link) === '') {
            return null;
        }

        return $link;
    }

    protected function resolveLanguageId(): ?int
    {
        if (class_exists(Context::class)) {
            $context = Context::getContext();
            if ($context && isset($context->language) && $context->language && isset($context->language->id)) {
                $idLang = (int) $context->language->id;
                if ($idLang > 0) {
                    return $idLang;
                }
            }
        }

        if (!class_exists(Configuration::class)) {
            return null;
        }

        $default = (int) Configuration::get('PS_LANG_DEFAULT');

        return $default > 0 ? $default : null;
    }

    protected function getConfigurationValue(string $key, ?int $idLang = null)
    {
        if (!class_exists(Configuration::class)) {
            return null;
        }

        if (null !== $idLang) {
            $value = Configuration::get($key, $idLang);
            if ($value !== false && $value !== null && $value !== '') {
                return $value;
            }
        }

        return Configuration::get($key);
    }
}
