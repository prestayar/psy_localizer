# PrestaSDK Documentation
## Chapter 1: Introduction & Quick Start
This chapter provides a general overview of PrestaSDK, how to install it, and the initial setup. By the end of this chapter, you will have created your first module using this SDK.
### 1.1. What is PrestaSDK?
PrestaSDK is a Software Development Kit (SDK) for building PrestaShop modules, designed to increase speed, standardize development, and improve code maintainability. Module development in PrestaShop can sometimes involve repetitive code and complex processes for common tasks like installation, configuration management, or creating an admin panel. PrestaSDK automates and simplifies these processes by providing a structured framework and a set of helper tools.
#### Main Goals:
- Rapid Development: By automating repetitive tasks such as registering hooks, installing admin tabs, and managing database tables, it allows you to focus on your module's core logic.
- Standardized Structure: All modules developed with this SDK follow a consistent architecture, making it easier to learn, develop, and collaborate on projects.
- High Maintainability: The code you write will be more readable, cleaner, and better organized, which simplifies future maintenance and updates.
#### Key Advantages:
- Automatic Installers: The Installer components easily handle the installation of tabs, hooks, and database tables.
- Ready-Made Admin Panel: Using AdminController and PanelCore, you get a beautiful and functional admin panel structure with a sidebar and a templating system.
- Powerful Helper Classes: Tools are provided for working with forms (HelperForm), configurations (Config), and general methods (HelperMethods).
- Automatic Asset Management: CSS and JavaScript files are automatically copied to the correct path and versioned to prevent browser caching issues.
### 1.2. Installation and Setup
To use PrestaSDK in your module, you just need to add it as a dependency via Composer.
#### Prerequisites:
- PHP: Version 7.4 or higher
- PrestaShop: Version 1.7.8 or higher (Recommended: 8.1.0+)
- Composer: Installed on your system
#### Installation Steps:

- Navigate to your module's root directory.
- Run the following command in your terminal to add the SDK to your project:

```bash
composer require prestaware/prestasdk
```

This command will create a vendor directory and an autoload.php file in your module's root.

- In your main module file (e.g., mymodule.php), include the autoload.php file to make the SDK classes available:

```php
// mymodule/mymodule.php
if (file_exists(dirname(__FILE__).'/vendor/autoload.php')) {
    require_once dirname(__FILE__).'/vendor/autoload.php';
}
```
### 1.3. Creating Your First Module (Hello World)
In this section, we'll create a simple module that demonstrates the basics of working with PrestaSDK.
#### 1. Initial File Structure

First, create the following folder structure for your module:

```
/modules
  /myhelloworld
    - myhelloworld.php   (Main module file)
    - composer.json
    - logo.png
    - config.xml
```
#### 2. Creating the Main Module Class

Create the content of myhelloworld.php as follows. The most important point is that your main class must extend PrestaSDK\V071\PrestaSDKModule.

```php
<?php
// myhelloworld/myhelloworld.php

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(dirname(__FILE__).'/vendor/autoload.php')) {
    require_once dirname(__FILE__).'/vendor/autoload.php';
}

use PrestaSDK\V071\PrestaSDKModule;

class MyHelloWorld extends PrestaSDKModule
{
    public function __construct()
    {
        // All initial settings should be placed in the initModule method
        // This method is called by the parent constructor
        parent::__construct();
    }

    /**
     * The main and initial settings of the module are defined in this method.
     */
    public function initModule()
    {
        $this->name = 'myhelloworld';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->displayName = $this->l('My Hello World Module');
        $this->description = $this->l('A simple module created with PrestaSDK.');

        $this->ps_versions_compliancy = ['min' => '1.7.8', 'max' => _PS_VERSION_];
    }

    /**
     * Install method - the SDK does the rest
     */
    public function install()
    {
        return parent::install();
    }

    /**
     * Uninstall method - the SDK does the rest
     */
    public function uninstall()
    {
        return parent::uninstall();
    }
}
```
#### 3. Introducing the initModule Method
As you can see in the code above, all the main module properties like name, version, displayName, etc., are defined inside the initModule() method.
This method is automatically called by the PrestaSDKModule's constructor (__construct). This separates the module's initialization logic from the main constructor, resulting in cleaner and more organized code.
With just these few lines of code, you have created a standard module. You can now install it. In the following chapters, we will learn how to add controllers, settings, admin tabs, and more.
