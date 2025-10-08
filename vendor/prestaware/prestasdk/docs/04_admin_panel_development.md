## Chapter 4: Admin Panel Development
This chapter guides you through the process of building a complete admin panel using PrestaSDK's tools. From creating controllers and menus to managing forms and lists, everything is covered here.
### 4.1. Creating an Admin Controller
The first step to creating a page in the admin area is to build a controller class. This class must extend PrestaSDK\V071\Controller\AdminController. This base class provides all the functionalities of PrestaShop's ModuleAdminController along with the features of PanelCore.
Steps:

- Create a PHP file in your module's controllers/admin/ directory. The filename should match the controller class name (e.g., AdminMyPanelController.php).
- Define your class by extending AdminController.

Basic Example:

```php
// controllers/admin/AdminMyPanelController.php
use PrestaSDK\V071\Controller\AdminController;

class AdminMyPanelController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // Initial controller settings go here
    }

    /**
     * This method replaces getContent() and is the main entry point for rendering the panel.
     */
    public function initContent()
    {
        // Instead of calling parent::initContent() directly, we use initAdminPanel()
        // This method manages and renders all panel components (sidebar, content, etc.).
        $this->content = $this->initAdminPanel();
    }
}
```

After defining the controller and linking it to a tab (as explained in Chapter 3), PrestaShop will automatically display it in the admin menu.
### 4.2. Sidebar Menu
To create a navigation menu on the side of your panel, simply implement the getMenuItems() method in your controller class. This method should return an array with a specific structure. PanelCore will automatically render this menu and place it in the Sidebar position.
Array Structure:

The array should contain groups of menu items. Each item can have its own submenu.

```php
// controllers/admin/AdminBulkUpdatePanelController.php
public function getMenuItems()
{
    $menuItems = [
        'main_group' => [ // Group key
            'panel' => [ // Item key
                'title' => $this->l('Panel'),
                'link' => $this->module->getModuleAdminLink('AdminBulkUpdatePanel'),
                'icon' => 'icon-dashboard',
            ],
            'files' => [
                'title' => $this->l('Files'),
                'link' => $this->module->getModuleAdminLink('AdminBulkUpdateFiles'),
                'icon' => 'icon-file-text',
            ],
            'logs' => [
                'title' => $this->l('Logs'),
                'link' => $this->module->getModuleAdminLink('AdminBulkUpdateLogs'),
                'icon' => 'icon-list-ul',
            ],
        ],
    ];

    return $menuItems;
}
```

Notes:

- `$this->module->getModuleAdminLink(...)` is the best way to generate internal panel links.
- The SDK automatically highlights the active menu item based on the current controller.
### 4.3. Managing Pages with Sections
As mentioned in the previous chapter, you can manage the logic for different pages within a single controller using methods prefixed with section.
Example:

Suppose you have a controller for managing custom products. You can handle the list, add, and edit pages as follows:

```php
class AdminCustomProductsController extends AdminController
{
    // ...
    
    // This method runs for a URL without a section parameter (the default page)
    public function sectionIndex()
    {
        // Logic to display the product list
        return $this->renderModuleTemplate('admin/list_products.tpl');
    }

    // This method runs for &section=edit
    public function sectionEdit()
    {
        $id = Tools::getValue('id_product');
        // Logic to display the edit form
        return $this->renderModuleTemplate('admin/edit_form.tpl', ['product_id' => $id]);
    }
}
```
To link to the edit section from your list_products.tpl template, you can do this:

```html
<a href="{$link->getAdminLink('AdminCustomProducts')|escape:'html':'UTF-8'}&section=edit&id_product={$product.id}">
    Edit Product
</a>
```
### 4.4. Form Building with HelperForm

PrestaSDK extends PrestaShop's standard HelperForm class with more utility methods. To use it, simply create an instance of `PrestaSDK\V071\Utility\HelperForm`.

#### Steps to create a settings form:

- Process Form: Check if the form has been submitted.
- Build Form: Create a method to define the form structure ($fields_form).
- Render Form: Render the form using generateForm().

#### Complete example for a settings page:

```php
// controllers/admin/AdminMySettingsController.php
class AdminMySettingsController extends AdminController
{
    // ...
    
    // Main section of the controller
    public function sectionIndex()
    {
        $output = '';
        // If the form is submitted, save the values
        if (Tools::isSubmit('submit' . $this->module->name)) {
            $this->saveSettings();
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
        
        // Render the form and add it to the output
        $output .= $this->renderSettingsForm();
        return $output;
    }

    protected function saveSettings()
    {
        $configs = [
            'MYMODULE_API_KEY' => Tools::getValue('MYMODULE_API_KEY'),
            'MYMODULE_ENABLE_FEATURE' => Tools::getValue('MYMODULE_ENABLE_FEATURE'),
        ];
        Config::updateConfigs($configs);
    }
    
    protected function renderSettingsForm()
    {
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                ['type' => 'text', 'label' => $this->l('API Key'), 'name' => 'MYMODULE_API_KEY', 'required' => true],
                ['type' => 'switch', 'label' => $this->l('Enable Feature'), 'name' => 'MYMODULE_ENABLE_FEATURE', 'is_bool' => true, 'values' => [/* Yes/No values */]]
            ],
            'submit' => ['title' => $this->l('Save'), 'class' => 'btn btn-default pull-right']
        ];

        $helper = new \PrestaSDK\V071\Utility\HelperForm($this->module);
        
        // Automatically fill values from the ps_configuration table
        $helper->setFieldsByArray(['MYMODULE_API_KEY', 'MYMODULE_ENABLE_FEATURE']);
        
        return $helper->generateForm($fields_form);
    }
}
```
### 4.5. Displaying Lists (HelperList)

You can use PrestaShop's standard HelperList to display data lists. The AdminController in the SDK is designed to be fully compatible with it.

Simply define the list-related properties in your controller's __construct method, just like you would with a regular ModuleAdminController.

#### Example (AdminBulkUpdateFilesController.php):

```php
// controllers/admin/AdminBulkUpdateFilesController.php
class AdminBulkUpdateFilesController extends AdminController
{
    public function __construct()
    {
        $this->table = 'wabulkupdate_file';
        $this->className = 'PrestaWare\WaBulkUpdate\Entity\File';
        $this->identifier = 'id_wabulkupdate_file';
        $this->bootstrap = true;
        
        parent::__construct();

        $this->fields_list = [
            'id_wabulkupdate_file' => ['title' => $this->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'],
            'file_name' => ['title' => $this->l('File Name')],
            'status' => ['title' => $this->l('Status'), 'align' => 'center', 'type' => 'bool', 'active' => 'status'],
            'date_add' => ['title' => $this->l('Date Add'), 'type' => 'datetime'],
        ];

        // Add actions to the list
        $this->addRowAction('view');
        $this->addRowAction('delete');
    }
}
```

Here, the renderList() or renderForm() methods are automatically called by AdminController, and their output is displayed within the SDK's panel layout.tpl. You don't need to override initContent() unless you want to change the default behavior.
### 4.6. Working with Templates

PanelCore provides two main methods for rendering templates:

- **renderModuleTemplate($template, $vars)**: Renders a template file from your module's views/templates/ path. This is suitable for your module's custom templates.

```php
$vars = ['my_variable' => 'Hello World'];
return $this->renderModuleTemplate('admin/my_page.tpl', $vars);
```

- **renderPanelTemplate($template, $vars)**: Renders a template from the SDK's default template path (vendor/prestaware/prestasdk/src/Resources/views/). This is useful for using pre-built components like _partials/sidebar.tpl.

#### Injecting Content into the Layout:

As mentioned earlier, you can use the `appendToPanel($position, $html)` method to add any HTML content to one of the layout positions (Header, Sidebar, Footer, etc.). This gives you great flexibility in customizing the panel's appearance.

```php
$customHeader = "<div class='alert alert-info'>Welcome to the panel!</div>";
$this->appendToPanel('TopContent', $customHeader);
```
