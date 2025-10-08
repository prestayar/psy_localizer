## Chapter 2: Core Concepts
This chapter delves deeper into the main components of PrestaSDK. Understanding these concepts will help you leverage the full potential of the SDK to build complex and powerful modules.
### 2.1. The PrestaSDKModule Class
This class is the starting point for any SDK-based module. It extends the standard PrestaShop Module class, adding a wealth of functionalities. By defining a few properties in your module class, you can delegate the entire installation and configuration process to the SDK.
#### Key Properties:

- `$moduleTabs` (array): Defines all the admin tabs (menus) that your module needs. TabsInstaller automatically creates these tabs during module installation.
- `$moduleConfigs` (array): A list of all configuration keys (Configuration) your module uses. Their default values are defined in this array and automatically saved to the database upon installation.
- `$configsAdminController` (string|array): Specifies the name of the main admin controller for the module. This automatically links the "Configure" button in the module list to this controller's page.
- `$moduleGrandParentTab` (string): If you want your module's tabs to be under another main tab (e.g., "Sell"), enter the class name of that tab in this property.
- `$pathFileSqlInstall` / `$pathFileSqlUninstall` (string): Specifies the path to the SQL files for creating or dropping database tables during installation/uninstallation. By default, the SDK looks for install.sql and uninstall.sql in the module's sql/ directory.
#### Module Lifecycle
The install() and uninstall() methods in PrestaSDKModule are overridden to automatically execute the necessary processes. When you call parent::install(), the SDK performs the following tasks in order:

- Install Tabs: Based on the `$moduleTabs` array.
- Register Hooks: By scanning for all hook... methods in your module class.
- Execute SQL: Using the file defined in `$pathFileSqlInstall`.
- Save Configurations: Initial values from `$moduleConfigs` are stored in the database.
- Publish Assets: The SDK's CSS/JS files are copied into your module's views directory.

Therefore, you no longer need to write repetitive logic for these tasks.
### 2.2. Admin Panel Structure
PrestaSDK provides a powerful system for building modern, multi-section admin panels.
#### AdminController and PanelCore
To create a page in the admin area, you simply need to create a controller that extends PrestaSDK\V071\Controller\AdminController. This base class automatically uses a Trait called PanelCore, which contains all the logic for rendering the panel, managing sections, and handling templating.
#### The Concept of Sections
A key feature of AdminController is managing pages through "sections". Instead of creating multiple controllers for different pages (like settings, lists, adding new items), you can implement all the logic in a single controller using methods with the pattern section<Name>.
- Example: If your URL is ...&section=settings, the SDK will automatically call the sectionSettings() method in your controller and display its content.
- Default Section: If the section parameter is not present in the URL, the sectionIndex() method will be executed.

This approach keeps your code much cleaner and more organized.
#### Layout System and Positions
The SDK's admin panel consists of a main layout.tpl file with various positions like Sidebar, Header, TopContent, and Footer. From within your controller, you can inject HTML content into any of these positions.
For example, to add a sidebar menu to the panel, you just need to render the menu's HTML and add it to the Sidebar position with the following method:

```php
$sidebarHtml = $this->renderPanelTemplate('_partials/sidebar.tpl', $vars);
$this->appendToPanel('Sidebar', $sidebarHtml);
```

This feature allows you to create a unified yet fully customizable user interface.
### 2.3. Version Management, Namespace, and Factory
#### Versioned Namespace
A significant challenge in the PrestaShop ecosystem is the potential for conflicts between different modules. If two modules use a shared library like PrestaSDK but require different versions of it, you might encounter fatal errors due to class or function re-declarations.
To solve this, all PrestaSDK classes are placed within a versioned namespace. For example, in version 0.4.0, all classes reside under PrestaSDK\V071:

```php
namespace PrestaSDK\V071;

class PrestaSDKModule extends \Module
{
    // ...
}
```

This structure ensures that if a future version 0.7.1 is released with the namespace PrestaSDK\V071, its code will not conflict with older versions.
#### Developer's Responsibility
When using the SDK, it is your responsibility as a developer to use the correct namespace corresponding to the SDK version installed in your module.

```php
// Correct usage for version 0.4.0
use PrestaSDK\V071\PrestaSDKModule;
use PrestaSDK\V071\Controller\AdminController;

class MyModule extends PrestaSDKModule 
{
    //...
}
```

If you decide to upgrade the SDK version in your module in the future, you must manually update your use statements to the new version (e.g., PrestaSDK\V071\...).
#### PrestaSDKFactory
The PrestaSDKFactory class is a helper tool to simplify the process of creating instances of SDK classes within the same version. Instead of directly calling new \PrestaSDK\V071\Utility\Config(...), you can use the Factory for more readable code.

```php
use PrestaSDK\V071\PrestaSDKFactory;

// This Factory creates an instance of the Config class from the V071 namespace
$config = PrestaSDKFactory::getUtility('Config', [$this->moduleConfigs]);
```

This pattern enhances code readability, but the responsibility of choosing the correct versioned namespace remains with you.
