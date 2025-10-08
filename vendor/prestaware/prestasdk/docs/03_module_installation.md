## Chapter 3: Module Installation Process
This chapter shows you how to fully automate your module's installation process using the Installer classes in PrestaSDK. You just need to define a few properties in your main module class, and the SDK will handle the rest.
### 3.1. Installing Database Tables (TablesInstaller)
To create the necessary tables for your module in the database, simply place your SQL files in the sql/ directory at the root of your module.

- sql/install.sql: SQL commands for creating tables.
- sql/uninstall.sql: SQL commands for dropping tables upon module uninstallation.

TablesInstaller automatically finds and executes these files. It intelligently replaces placeholders like _DB_PREFIX_ and _MYSQL_ENGINE_ with the correct values.

Example (install.sql):

```sql
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_wabulkupdate_file` (
    `id_wabulkupdate_file` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_name` VARCHAR(255) NOT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 0,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_wabulkupdate_file`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8;
```

Note: If you want to place your SQL files in a different directory, you can specify their full paths in the `$pathFileSqlInstall` and `$pathFileSqlUninstall` properties of your main module class.
### 3.2. Adding Admin Tabs (TabsInstaller)
To create menus for your module in the admin panel, define their structure in the `$moduleTabs` property of your main module class.

The structure of this array is `['AdminClassName' => [options]]`.

- **AdminClassName**: The name of your controller class (without the Controller suffix).
- **options**: An array of tab settings:
  - `title`: The title displayed in the menu.
  - `parent_class_name`: The class name of the parent controller. If this is a submenu item, provide the parent's class name here. To create a top-level tab, use a custom name and also define it in the `$moduleGrandParentTab` property.
  - `icon`: (Optional) The class name for the icon (e.g., icon-cogs).
  - `visible`: (Optional) Set to false to hide the tab.
Example (from the wabulkupdate module):

In this example, a main tab AdminBulkUpdate is defined first, and then other tabs are placed as its children.

```php
// wabulkupdate.php
public function initModule()
{
    // ...
    $this->moduleGrandParentTab = 'AdminBulkUpdate'; // Main tab class name
    
    $this->moduleTabs = [
        // Main Tab
        'AdminBulkUpdate' => [
            'title' => $this->l('Bulk Update'),
            'parent_class_name' => '', // No parent, so it's a root tab
            'icon' => 'icon-cloud-upload',
        ],
        // Child Tabs
        'AdminBulkUpdatePanel' => [
            'title' => $this->l('Panel'),
            'parent_class_name' => $this->moduleGrandParentTab, // Child of AdminBulkUpdate
            'icon' => 'icon-dashboard',
        ],
        'AdminBulkUpdateFiles' => [
            'title' => $this->l('Files'),
            'parent_class_name' => $this->moduleGrandParentTab,
            'icon' => 'icon-file-text',
        ],
        'AdminBulkUpdateLogs' => [
            'title' => $this->l('Logs'),
            'parent_class_name' => $this->moduleGrandParentTab,
            'icon' => 'icon-list-ul',
        ]
    ];
}
```
### 3.3. Automatic Hook Registration (HooksInstaller)
One of the biggest advantages of PrestaSDK is that you don't need to register hooks manually. The HooksInstaller class uses PHP's Reflection API to inspect your main module class, finds all public methods that start with the hook prefix, and automatically registers the corresponding hooks for you.
For example, to hook into actionProductUpdate, you just need to create the following method in your main module class:

```php
// mymodule.php
class MyModule extends PrestaSDKModule
{
    // ...
    
    public function hookActionProductUpdate($params)
    {
        // Your logic here...
        $product = $params['product'];
        // Do something with the updated product.
    }

    public function hookDisplayHeader($params)
    {
        // Your logic for header...
    }
}
```

During installation, the actionProductUpdate and displayHeader hooks will be automatically registered for your module.
### 3.4. Managing Initial Configurations (Config)
To define default values for your module's settings, use the `$moduleConfigs` property. This property is a key => value array where key is the variable name in the ps_configuration table and value is its default value.

Example:

```php
// mymodule.php
public function initModule()
{
    // ...
    $this->perfixConfigs = 'MYMODULE'; // (Optional) A prefix to avoid name conflicts
    
    $this->moduleConfigs = [
        'MYMODULE_ENABLE_FEATURE' => 1,
        'MYMODULE_API_KEY' => 'default_api_key',
        'MYMODULE_ITEMS_PER_PAGE' => 10,
    ];
}
```

During installation, the Config class will automatically save all these values to the database. To read these values anywhere in your module, you can use the helper method:

```php
$apiKey = $this->config->getConfig('API_KEY'); 
// Output: 'default_api_key'
// No need to include the prefix
```
