<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
declare(strict_types=1);

namespace PrestaSDK\Install;

class HooksInstaller
{
    private \Module $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    public function installHooks(): bool
    {
        if (!$this->module->registerHook($this->getHooksNames())) {
            return false;
        }

        return true;
    }

    public function getHooksNames(): array
    {
        $hookMethods = [];

        $reflection = new \ReflectionObject($this->module);
        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $methodName = $method->getName();

            if (str_starts_with($methodName, 'hook')) {
                $cleanMethodName = str_replace('hook', '', $methodName);
                $hookMethods[] = lcfirst($cleanMethodName);
            }
        }

        return $hookMethods;
    }
}