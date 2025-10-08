# مستندات فارسی PrestaSDK

PrestaSDK یک کتابخانهٔ ساده و قابل توسعه برای ایجاد ماژول‌های PrestaShop است.

## نصب
برای افزودن PrestaSDK به ماژول PrestaShop خود از Composer استفاده کنید:

```bash
composer require prestaware/prestasdk
```

## ویژگی‌ها
- کلاس پایه `PrestaSDKModule` برای ساختار یکپارچهٔ ماژول‌ها
- کلاس `PrestaSDKFactory` برای بارگذاری نصب‌کننده‌ها، کنترلرها و ابزارها
- ابزارهایی برای پیکربندی، انتشار دارایی‌ها و ایجاد پنل مدیریت

## استفاده
در ماژول خود از `PrestaSDKModule` ارث‌بری کنید و تنظیمات را در `initModule` تعریف کنید.

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

## مستندات
راهنمای توسعه ماژول به فصل‌های زیر تقسیم شده است:

1. [مقدمه و شروع سریع](01_introduction_quick_start.md)
2. [مفاهیم اصلی و پایه](02_core_concepts.md)
3. [فرآیند نصب ماژول](03_module_installation.md)
4. [توسعه پنل مدیریت](04_admin_panel_development.md)
5. [مدیریت داده‌ها](05_data_management.md)
6. [مباحث پیشرفته](06_advanced_topics.md)
7. [جمع‌بندی و مراحل بعدی](07_conclusion.md)

برای مشاهدهٔ یک نمونهٔ پیاده‌سازی، فایل [`examples/module_integration.php`](../../examples/module_integration.php) را ببینید.
