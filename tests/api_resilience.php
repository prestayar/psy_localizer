<?php

declare(strict_types=1);

namespace PrestaSDK\V071\Utility {
    class CurlClient
    {
        public static array $response = [];
        public static ?\Throwable $failure = null;
        public static int $calls = 0;
        public static array $query = [];

        public function __construct(...$arguments)
        {
        }

        public function request($method, $url, array $options): array
        {
            self::$calls++;
            self::$query = $options['query'];
            if (self::$failure !== null) {
                throw self::$failure;
            }

            return self::$response;
        }
    }

    class CacheManager
    {
        public static mixed $cached = null;
        public static ?\Throwable $failure = null;
        public static bool $warn = false;
        public static int $writes = 0;

        public function __construct($moduleName)
        {
        }

        public function remember($key, $ttl, \Closure $callback): mixed
        {
            if (self::$warn) {
                trigger_error('Cache is not writable', E_USER_WARNING);
            }
            if (self::$failure !== null) {
                throw self::$failure;
            }
            if (self::$cached !== null) {
                return self::$cached;
            }

            $data = $callback();
            self::$writes++;
            return $data;
        }
    }
}

namespace PrestaSDK\V071\Controller {
    class AdminController
    {
        public \Module $module;
        public array $panel = [];
        public bool $initialized = false;

        public function initSDKPanel()
        {
            $this->initialized = true;
        }

        public function pushPanelVar($key, $value): void
        {
            $this->panel[$key] = $value;
        }
    }
}

namespace {
    use PrestaSDK\V071\Utility\CacheManager;
    use PrestaSDK\V071\Utility\CurlClient;
    use PrestaYar\Localizer\Api\Service\ProductInfoManager;

    define('_PS_VERSION_', '9.1.0');
    define('_PS_MODE_DEV_', ($argv[1] ?? '') === 'debug');
    error_reporting(E_ALL);

    class Module
    {
        public string $name = 'psy_localizer';
        public string $version = '2.2.0';

        public function log(...$arguments): void
        {
        }

        public function l($message, $class): string
        {
            return $message;
        }
    }

    require dirname(__DIR__) . '/src/Api/Config/ApiConfig.php';
    require dirname(__DIR__) . '/src/Api/Service/ApiService.php';
    require dirname(__DIR__) . '/src/Api/Service/ProductInfoManager.php';
    require dirname(__DIR__) . '/src/LocalizerAdmin.php';

    class TestAdmin extends \PrestaYar\Localizer\LocalizerAdmin
    {
        public bool $failConstruction = false;

        public function __construct(private ProductInfoManager $manager)
        {
            $this->module = new Module();
        }

        protected function getProductInfoManager(): ProductInfoManager
        {
            if ($this->failConstruction) {
                throw new \Error('Unable to initialize API dependencies');
            }

            return $this->manager;
        }
    }

    function check(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $manager = new ProductInfoManager(new Module(), 'example.com');
    $success = [
        'success' => true,
        'status_code' => 200,
        'body' => json_encode([
            'success' => true,
            'data' => ['update_info' => ['version' => '2.3.0']],
        ]),
    ];

    foreach (['timeout', 'http', 'json', 'scalar', 'type', 'cache', 'cache-value', 'warning', 'cached-error', 'initialization', 'success'] as $case) {
        CurlClient::$response = $success;
        CurlClient::$failure = null;
        CurlClient::$calls = 0;
        CacheManager::$cached = null;
        CacheManager::$failure = null;
        CacheManager::$warn = false;
        CacheManager::$writes = 0;

        switch ($case) {
            case 'timeout':
                CurlClient::$response = ['success' => false, 'error' => ['message' => 'Timed out']];
                break;
            case 'http':
                CurlClient::$response = ['success' => false, 'status_code' => 500, 'body' => 'Server error'];
                break;
            case 'json':
                CurlClient::$response['body'] = '<html>Bad gateway</html>';
                break;
            case 'scalar':
                CurlClient::$response['body'] = 'null';
                break;
            case 'type':
                CurlClient::$failure = new \TypeError('Invalid response shape');
                break;
            case 'cache':
                CacheManager::$failure = new \Error('Cache failed');
                break;
            case 'cache-value':
                CacheManager::$cached = false;
                break;
            case 'warning':
                CacheManager::$warn = true;
                break;
            case 'cached-error':
                CacheManager::$cached = ['data' => null, 'errors' => [['message' => 'Old cached error']]];
                break;
        }

        $admin = new TestAdmin($manager);
        $admin->failConstruction = $case === 'initialization';
        $previousHandler = static function (): bool {
            throw new \RuntimeException('Error handler was not restored');
        };
        set_error_handler($previousHandler);
        ob_start();
        $admin->initSDKPanel();
        $output = ob_get_clean();
        $restored = set_error_handler($previousHandler);
        restore_error_handler();
        restore_error_handler();

        check($restored === $previousHandler, $case . ': previous error handler not restored');
        check($output === '', $case . ': unexpected output');
        check($admin->initialized, $case . ': panel initialization stopped');
        check(CurlClient::$calls <= 1, $case . ': unnecessary retry');
        $errors = $admin->panel['module_info']['errors'];
        check(!empty($errors) === (_PS_MODE_DEV_ && $case !== 'success'), $case . ': wrong error visibility');
        if ($case === 'success') {
            check($admin->panel['status_update'] === 'warning', 'Update notice lost');
            check(CurlClient::$query['php_version'] === PHP_VERSION, 'PHP version was shortened');
        } elseif (in_array($case, ['timeout', 'http', 'json', 'scalar', 'type'], true)) {
            check(CacheManager::$writes === 0, $case . ': failed request cached');
        }
    }

    echo 'API resilience checks passed (' . (_PS_MODE_DEV_ ? 'debug' : 'production') . ").\n";
}
