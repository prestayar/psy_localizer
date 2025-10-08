# PrestaSDK

PrestaSDK is a simple and extendable library for developing PrestaShop modules.

## Installation
Use Composer to add PrestaSDK to your PrestaShop module:

```bash
composer require prestaware/prestasdk
```

## Features
- Base `PrestaSDKModule` for uniform module structure
- `PrestaSDKFactory` for loading installers, controllers and utilities
- Utilities for configuration, asset publishing and admin panels

## Usage
Extend `PrestaSDKModule` in your module and define its settings inside `initModule`.

```php
<?php
use PrestaSDK\V071\PrestaSDKModule;

class MyModule extends PrestaSDKModule
{
    public function initModule()
    {
        $this->name = 'my_module';
        $this->version = '1.0.0';
    }
}
```

## Documentation

The module development guide is split into chapters:

1. [Introduction & Quick Start](docs/01_introduction_quick_start.md)
2. [Core Concepts](docs/02_core_concepts.md)
3. [Module Installation](docs/03_module_installation.md)
4. [Admin Panel Development](docs/04_admin_panel_development.md)
5. [Data Management](docs/05_data_management.md)
6. [Advanced Topics](docs/06_advanced_topics.md)
7. [Conclusion](docs/07_conclusion.md)

Persian documentation is available in [docs/fa/README.md](docs/fa/README.md).

For an example integration, see [`examples/module_integration.php`](examples/module_integration.php).

