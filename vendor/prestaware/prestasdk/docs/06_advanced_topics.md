## Chapter 6: Advanced Topics
This chapter covers some of the more advanced features and helper classes in PrestaSDK that will help you develop more complex modules.
### 6.1. Asset Management (CSS/JS)
A common challenge in web development is managing browser cache for CSS and JavaScript files. PrestaSDK solves this problem with an automated system.
#### AssetPublisher and Automatic Versioning
The AssetPublisher class is responsible for copying prestasdk.css and prestasdk.js files from the vendor directory into your module's views/css and views/js directories. This is done during module installation.
More importantly, the setMedia() method in AdminController automatically includes these files in your pages and appends a version number to their URLs (e.g., ?v=0.4.0). This version number is read from the SDK's own composer.json file.
What's the benefit?
Whenever you update the PrestaSDK version via Composer, the version number in the file URLs changes. This forces users' browsers to download the new files, completely solving the caching issue.
If the SDK version has changed, AdminController will automatically re-run AssetPublisher to replace the old files with the new ones.
### 6.2. Request Lifecycle and Middlewares
PanelCore (used in AdminController) provides a powerful Middleware-like system for managing the request lifecycle. This system allows you to execute code before or after the main logic of a "section" runs.
This is extremely useful for tasks like access validation, processing POST data before a form is displayed, or loading data that is shared across multiple sections.
#### How to Use middlewaresACL
To use this feature, you need to define the $middlewaresACL property in your controller. This is an array that specifies which methods (middlewares) should run and when.
Array Structure:
```php
$this->middlewaresACL = [
    'before' => [
        // 'section@Controller' => ['middleware_name1', 'middleware_name2'],
    ],
    'after' => [],
    'ignore' => [], // To skip a middleware under certain conditions
];
```
- before: Methods defined here run before the main section... method.
- after: Methods defined here run after the main section... method.
- Definition Patterns:
- *: For all sections in all controllers.
- *@AdminMyController: For all sections in AdminMyController.
- settings@AdminMyController: Only for the settings section in AdminMyController.
Practical Example:
Let's say we want to check if a requested item exists before displaying the edit form (sectionEdit).
```php
class AdminCustomProductsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->middlewaresACL = [
            'before' => [
                'edit@AdminCustomProducts' => ['loadProduct'], // Run before sectionEdit
            ],
        ];
    }
    
    /**
     * Middleware methods must be prefixed with 'middleware'.
     */
    public function middlewareLoadProduct()
    {
        $id_product = (int)Tools::getValue('id_product');
        $product = new Product($id_product);
        
        if (!Validate::isLoadedObject($product)) {
            // If product doesn't exist, redirect to the list
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomProducts'));
        }
        
        // Make the product available for the main method
        $this->product = $product;
        
        return $this->runNext(); // Execute the next middleware or method in the queue
    }

    public function sectionEdit()
    {
        // Thanks to the middleware, we are sure that $this->product is loaded here
        // ...
    }
}
```
Important Note: At the end of each middleware method, you must call $this->runNext() to continue the request lifecycle.
### 6.3. Utility Classes
PrestaSDK includes several other helper classes to simplify everyday tasks.
#### HelperMethods

This class contains static methods for common tasks:

- **setFlashMessage($message, $type)**: Sets a temporary message (Flash Message) to be displayed to the user (e.g., after a redirect).
- **getFlashMessage()**: Reads the flash message and clears it from storage. The SDK admin panel automatically displays these messages.
- **setCookie($name, $data) / getCookie($name, $key)**: For easier interaction with PrestaShop cookies.

#### VersionHelper

This class has a static method getSDKVersion() that reads the current SDK version from its composer.json file. It is used internally by AssetPublisher.
