## فصل ۴: توسعه پنل مدیریت
این فصل شما را با فرآیند ساخت یک پنل مدیریت کامل با استفاده از ابزارهای PrestaSDK آشنا می‌کند. از ساخت کنترلر و منو گرفته تا مدیریت فرم‌ها و لیست‌ها، همه چیز در اینجا پوشش داده می‌شود.
### ۴.۱. ساخت کنترلر مدیریت
اولین قدم برای ایجاد یک صفحه در بخش مدیریت، ساخت یک کلاس کنترلر است. این کلاس باید از PrestaSDK\V071\Controller\AdminController ارث‌بری کند. این کلاس پایه، تمام قابلیت‌های ModuleAdminController پرستاشاپ را به همراه ویژگی‌های PanelCore در اختیار شما قرار می‌دهد.
مراحل:
- یک فایل PHP در مسیر controllers/admin/ ماژول خود ایجاد کنید. نام فایل باید با نام کلاس کنترلر شما یکسان باشد (مثلاً AdminMyPanelController.php).
- کلاس خود را با ارث‌بری از AdminController تعریف کنید.
مثال پایه:
```php
// controllers/admin/AdminMyPanelController.php

use PrestaSDK\V071\Controller\AdminController;

class AdminMyPanelController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // تنظیمات اولیه کنترلر در اینجا قرار می‌گیرد
    }

    /**
     * این متد جایگزین getContent() می‌شود و نقطه ورود اصلی برای رندر پنل است.
     */
    public function initContent()
    {
        // به جای فراخوانی مستقیم parent::initContent()، از متد initAdminPanel() استفاده می‌کنیم
        // این متد تمام اجزای پنل (سایدبار، محتوا و ...) را مدیریت و رندر می‌کند.
        $this->content = $this->initAdminPanel();
    }
}
```
پس از تعریف کنترلر و اتصال آن به یک تب (همانطور که در فصل ۳ توضیح داده شد)، پرستاشاپ به صورت خودکار آن را در منوی مدیریت نمایش خواهد داد.
### ۴.۲. منوی کناری (Sidebar)
برای ایجاد منوی ناوبری در سمت راست پنل، کافیست متد getMenuItems() را در کلاس کنترلر خود پیاده‌سازی کنید. این متد باید یک آرایه با ساختار مشخص برگرداند. PanelCore به صورت خودکار این منو را رندر کرده و در جایگاه Sidebar قرار می‌دهد.
ساختار آرایه:
آرایه باید شامل گروه‌هایی از آیتم‌های منو باشد. هر آیتم می‌تواند خود شامل زیرمجموعه باشد.
```php
// controllers/admin/AdminBulkUpdatePanelController.php

public function getMenuItems()
{
    $menuItems = [
        'main_group' => [ // کلید گروه
            'panel' => [ // کلید آیتم
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
نکات:
- $this->module->getModuleAdminLink(...) بهترین روش برای تولید لینک‌های داخلی پنل است.
- SDK به صورت خودکار آیتم فعال منو را بر اساس کنترلر فعلی تشخیص می‌دهد.
### ۴.۳. مدیریت صفحات با بخش‌ها (Sections)
همانطور که در فصل قبل اشاره شد، شما می‌توانید منطق صفحات مختلف را در یک کنترلر با استفاده از متدهایی با پیشوند section مدیریت کنید.
مثال:
فرض کنید یک کنترلر برای مدیریت محصولات سفارشی دارید. می‌توانید صفحات لیست، افزودن و ویرایش را به شکل زیر مدیریت کنید:
```php
class AdminCustomProductsController extends AdminController
{
    // ...
    
    // این متد برای URL بدون پارامتر section اجرا می‌شود (صفحه پیش‌فرض)
    public function sectionIndex()
    {
        // منطق نمایش لیست محصولات
        return $this->renderModuleTemplate('admin/list_products.tpl');
    }

    // این متد برای &section=edit اجرا می‌شود
    public function sectionEdit()
    {
        $id = Tools::getValue('id_product');
        // منطق نمایش فرم ویرایش
        return $this->renderModuleTemplate('admin/edit_form.tpl', ['product_id' => $id]);
    }
}
```
برای لینک‌دهی به بخش ویرایش از داخل قالب list_products.tpl می‌توانید به این صورت عمل کنید:
```smarty
<a href="{$link->getAdminLink('AdminCustomProducts')|escape:'html':'UTF-8'}&section=edit&id_product={$product.id}">
    Edit Product
</a>
```
### ۴.۴. فرم‌سازی با HelperForm
PrestaSDK کلاس HelperForm استاندارد پرستاشاپ را با متدهای کاربردی‌تری گسترش داده است. برای استفاده از آن، کافیست یک نمونه از کلاس PrestaSDK\V071\Utility\HelperForm بسازید.
مراحل ساخت یک فرم تنظیمات:
- پردازش فرم: ابتدا بررسی کنید که آیا فرم ارسال شده است یا خیر.
- ساخت فرم: یک متد برای تعریف ساختار فرم ($fields_form) ایجاد کنید.
- رندر فرم: فرم را با استفاده از generateForm() رندر کنید.
مثال کامل برای یک صفحه تنظیمات:
```php
// controllers/admin/AdminMySettingsController.php

class AdminMySettingsController extends AdminController
{
    // ...
    
    // بخش اصلی کنترلر
    public function sectionIndex()
    {
        $output = '';
        // اگر فرم ارسال شده باشد، مقادیر را ذخیره کن
        if (Tools::isSubmit('submit' . $this->module->name)) {
            $this->saveSettings();
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
        
        // فرم را رندر کن و به خروجی اضافه کن
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
                [
                    'type' => 'text',
                    'label' => $this->l('API Key'),
                    'name' => 'MYMODULE_API_KEY',
                    'required' => true,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Enable Feature'),
                    'name' => 'MYMODULE_ENABLE_FEATURE',
                    'is_bool' => true,
                    'values' => [ /* مقادیر بله/خیر */ ],
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new \PrestaSDK\V071\Utility\HelperForm($this->module);
        
        // پر کردن خودکار مقادیر از جدول ps_configuration
        $helper->setFieldsByArray(['MYMODULE_API_KEY', 'MYMODULE_ENABLE_FEATURE']);
        
        return $helper->generateForm($fields_form);
    }
}
```
### ۴.۵. نمایش لیست‌ها (HelperList)
شما می‌توانید از HelperList استاندارد پرستاشاپ برای نمایش لیست داده‌ها استفاده کنید. AdminController در SDK به گونه‌ای طراحی شده که با آن کاملاً سازگار باشد.
کافیست مانند یک ModuleAdminController معمولی، پراپرتی‌های مربوط به لیست را در __construct کنترلر خود تعریف کنید.
مثال (AdminBulkUpdateFilesController.php):
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

        // افزودن اکشن‌ها به لیست
        $this->addRowAction('view');
        $this->addRowAction('delete');
    }
}
```
در اینجا، متدهای renderList() یا renderForm() به صورت خودکار توسط AdminController فراخوانی شده و خروجی آن‌ها در layout.tpl پنل SDK نمایش داده می‌شود. شما نیازی به بازنویسی initContent() ندارید مگر اینکه بخواهید رفتار پیش‌فرض را تغییر دهید.
### ۴.۶. کار با قالب‌ها (Templating)
PanelCore دو متد اصلی برای رندر کردن قالب‌ها ارائه می‌دهد:
- renderModuleTemplate($template, $vars): این متد یک فایل قالب را از مسیر views/templates/ ماژول شما رندر می‌کند. این برای قالب‌های اختصاصی ماژول شما مناسب است.
```php
$vars = ['my_variable' => 'Hello World'];
return $this->renderModuleTemplate('admin/my_page.tpl', $vars);
```
- renderPanelTemplate($template, $vars): این متد قالب را از مسیر پیش‌فرض قالب‌های SDK (vendor/prestaware/prestasdk/src/Resources/views/) رندر می‌کند. این برای استفاده از اجزای آماده مانند _partials/sidebar.tpl کاربرد دارد.
تزریق محتوا به Layout:
همانطور که قبلاً اشاره شد، با استفاده از متد appendToPanel($position, $html) می‌توانید هر محتوای HTML را به یکی از جایگاه‌های (Header, Sidebar, Footer و...) اضافه کنید. این کار به شما انعطاف‌پذیری بالایی در سفارشی‌سازی ظاهر پنل می‌دهد.
```php
$customHeader = "<div class='alert alert-info'>Welcome to the panel!</div>";
$this->appendToPanel('TopContent', $customHeader);
```
