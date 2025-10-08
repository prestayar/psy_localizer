## فصل ۳: فرآیند نصب ماژول (Installation)
این فصل به شما نشان می‌دهد که چگونه با استفاده از کلاس‌های Installer در PrestaSDK، فرآیند نصب ماژول خود را به طور کامل خودکار کنید. کافی است چند پراپرتی را در کلاس اصلی ماژول خود تعریف کنید و SDK بقیه کارها را انجام خواهد داد.
### ۳.۱. نصب جداول دیتابیس (TablesInstaller)
برای ایجاد جداول مورد نیاز ماژول در دیتابیس، کافیست فایل‌های SQL خود را در پوشه sql/ در ریشه ماژول قرار دهید.
- sql/install.sql: دستورات SQL برای ایجاد جداول.
- sql/uninstall.sql: دستورات SQL برای حذف جداول هنگام حذف ماژول.
TablesInstaller به صورت خودکار این فایل‌ها را پیدا کرده و اجرا می‌کند. این کلاس هوشمندانه مقادیر `_DB_PREFIX_` و `_MYSQL_ENGINE_` را با مقادیر صحیح جایگزین می‌کند.

مثال (install.sql):

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

**نکته**: اگر می‌خواهید فایل‌های SQL را در مسیر دیگری قرار دهید، می‌توانید مسیر کامل آن‌ها را در پراپرتی‌های `$pathFileSqlInstall` و `$pathFileSqlUninstall` در کلاس اصلی ماژول خود مشخص کنید.
### ۳.۲. افزودن تب‌های مدیریت (TabsInstaller)
برای ساخت منوهای ماژول در پنل مدیریت، کافیست ساختار آن‌ها را در پراپرتی `$moduleTabs` در کلاس اصلی ماژول خود تعریف کنید.

ساختار این آرایه به صورت `['AdminClassName' => [options]]` است.

- **AdminClassName**: نام کلاس کنترلر شما (بدون پسوند Controller).
- **options**: آرایه‌ای از تنظیمات تب:
  - **title**: عنوانی که در منو نمایش داده می‌شود.
  - **parent_class_name**: نام کلاس کنترلر والد. اگر این تب یک زیرمجموعه است، نام کلاس والد را اینجا قرار دهید. برای ایجاد یک تب اصلی، از یک نام دلخواه استفاده کرده و آن را در پراپرتی `$moduleGrandParentTab` نیز تعریف کنید.
  - **icon**: (اختیاری) کلاس آیکون مورد نظر (مثلاً `icon-cogs`).
  - **visible**: (اختیاری) با مقدار `false` می‌توانید تب را مخفی کنید.

**مثال (از ماژول wabulkupdate)**:

در این مثال، ابتدا یک تب اصلی به نام AdminBulkUpdate تعریف شده و سپس بقیه تب‌ها به عنوان فرزند آن قرار گرفته‌اند.

```php
// wabulkupdate.php

public function initModule()
{
    // ...
    $this->moduleGrandParentTab = 'AdminBulkUpdate'; // نام کلاس تب اصلی
    
    $this->moduleTabs = [
        // تب اصلی
        'AdminBulkUpdate' => [
            'title' => $this->l('Bulk Update'),
            'parent_class_name' => '', // والد ندارد، پس در ریشه قرار می‌گیرد
            'icon' => 'icon-cloud-upload',
        ],
        // تب‌های فرزند
        'AdminBulkUpdatePanel' => [
            'title' => $this->l('Panel'),
            'parent_class_name' => $this->moduleGrandParentTab, // فرزند AdminBulkUpdate است
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
### ۳.۳. ثبت خودکار هوک‌ها (HooksInstaller)
یکی از بزرگترین مزایای PrestaSDK، عدم نیاز به ثبت دستی هوک‌هاست. کلاس HooksInstaller با استفاده از Reflection API در PHP، کلاس اصلی ماژول شما را بررسی کرده و تمام متدهای پابلیک که با پیشوند `hook` شروع می‌شوند را پیدا می‌کند و هوک متناظر با آن‌ها را به صورت خودکار ثبت می‌کند.

برای مثال، اگر بخواهید به هوک `actionProductUpdate` متصل شوید، کافیست متد زیر را در کلاس اصلی ماژول خود ایجاد کنید:

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

هنگام نصب، هوک‌های `actionProductUpdate` و `displayHeader` به صورت خودکار برای ماژول شما ثبت خواهند شد.
### ۳.۴. مدیریت تنظیمات اولیه (Config)
برای تعریف مقادیر پیش‌فرض برای تنظیمات ماژول، از پراپرتی `$moduleConfigs` استفاده کنید. این پراپرتی یک آرایه از `key => value` است که در آن key نام متغیر در جدول `ps_configuration` و value مقدار پیش‌فرض آن است.

مثال:

```php
// mymodule.php

public function initModule()
{
    // ...
    $this->perfixConfigs = 'MYMODULE'; // (اختیاری) یک پیشوند برای جلوگیری از تداخل نام
    
    $this->moduleConfigs = [
        'MYMODULE_ENABLE_FEATURE' => 1,
        'MYMODULE_API_KEY' => 'default_api_key',
        'MYMODULE_ITEMS_PER_PAGE' => 10,
    ];
}
```

در زمان نصب، کلاس Config تمام این مقادیر را به صورت خودکار در دیتابیس ذخیره می‌کند. برای خواندن این مقادیر در هر جای ماژول، می‌توانید از متد کمکی زیر استفاده کنید:

```php
$apiKey = $this->config->getConfig('API_KEY'); 
// خروجی: 'default_api_key'
// نیازی به نوشتن پیشوند نیست
```
