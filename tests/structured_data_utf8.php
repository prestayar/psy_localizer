<?php

declare(strict_types=1);

namespace PrestaSDK\V071 {
    class PrestaSDKModule
    {
        public object $context;
    }
}

namespace {
    define('_PS_VERSION_', '9.2.1');

    error_reporting(E_ALL);
    set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    });

    class ProductControllerCore
    {
        public array $features = [];
        public int $reads = 0;

        public function getTemplateVarProduct(): array
        {
            ++$this->reads;

            return ['grouped_features' => $this->features];
        }
    }

    require dirname(__DIR__) . '/src/Traits/UseDate.php';
    require dirname(__DIR__) . '/src/LocalizerModule.php';
    require dirname(__DIR__) . '/upgrade/upgrade-2.2.0.php';

    function check(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $module = new \PrestaYar\Localizer\LocalizerModule();
    $controller = new ProductControllerCore();
    $module->context = (object) ['controller' => $controller];

    $value = "متوسط\r\nمدرن";
    $broken = preg_replace('/\R+/', ', ', $value);
    check(json_encode($broken) === false, 'Core regex did not reproduce the UTF-8 error');

    $controller->features = [
        ['name' => '  مدل  ', 'value' => " {$value} "],
        ['name' => 'Size', 'value' => 'Large'],
    ];

    $properties = [
        ['@type' => 'PropertyValue', 'name' => 'مدل', 'value' => $broken, 'unitText' => 'Example'],
        ['@type' => 'PropertyValue', 'name' => 'Size', 'value' => 'Large'],
        ['@type' => 'PropertyValue', 'name' => 'Extra value', 'value' => 'مدرن'],
    ];
    $templateVars = [
        'structured_data' => [
            'product' => ['additionalProperty' => $properties, 'offers' => ['price' => '10']],
        ],
    ];
    $original = $templateVars;
    $params = ['templateVars' => &$templateVars];
    $module->hookActionFrontControllerSetVariables($params);

    $expected = $original;
    $expected['structured_data']['product']['additionalProperty'][0]['value'] = 'متوسط, مدرن';
    check($templateVars === $expected, 'Hook did not repair only the matching feature value');
    check(json_encode($templateVars['structured_data']) !== false, 'Structured data still cannot be encoded');

    $module->hookActionFrontControllerSetVariables($params);
    check($templateVars === $expected && $controller->reads === 1, 'Valid data triggered another repair');

    $templateVars = $original;
    $module->context->controller = new \stdClass();
    $module->hookActionFrontControllerSetVariables($params);
    check($templateVars === $original, 'Non-product data changed');

    $module->context->controller = $controller;
    $templateVars['structured_data']['product']['additionalProperty'][0]['name'] = 'Custom';
    $unmatched = $templateVars;
    $module->hookActionFrontControllerSetVariables($params);
    check($templateVars === $unmatched, 'An unmatched value from another source changed');

    $upgradeTarget = new class {
        public bool $result = true;
        public array $hooks = [];

        public function registerHook(array $hooks): bool
        {
            $this->hooks = $hooks;

            return $this->result;
        }
    };

    check(upgrade_module_2_2_0($upgradeTarget), 'Upgrade did not register the hook');
    check($upgradeTarget->hooks === ['actionCartGridDataModifier', 'actionFrontControllerSetVariables'], 'Upgrade did not register both hooks');
    $upgradeTarget->result = false;
    check(!upgrade_module_2_2_0($upgradeTarget), 'Upgrade hid a hook registration failure');

    echo "Structured data UTF-8 checks passed.\n";
}
