## فصل ۶: مباحث پیشرفته
این فصل به بررسی برخی از قابلیت‌های پیشرفته‌تر و کلاس‌های کمکی PrestaSDK می‌پردازد که به شما در توسعه ماژول‌های پیچیده‌تر کمک می‌کنند.
### ۶.۱. مدیریت Asset ها (CSS/JS)
یکی از چالش‌های رایج در توسعه وب، مدیریت کش مرورگر برای فایل‌های CSS و JavaScript است. PrestaSDK این مشکل را با یک سیستم خودکار حل کرده است.
#### AssetPublisher و نسخه‌بندی خودکار
کلاس AssetPublisher وظیفه دارد تا فایل‌های prestasdk.css و prestasdk.js را از داخل پوشه vendor به پوشه views/css و views/js ماژول شما کپی کند. این کار هنگام نصب ماژول انجام می‌شود.
مهم‌تر از آن، متد setMedia() در AdminController به صورت خودکار این فایل‌ها را به صفحات اضافه می‌کند و یک شماره نسخه به انتهای URL آن‌ها اضافه می‌کند (مثلاً ?v=0.4.0). این شماره نسخه از فایل composer.json خود SDK خوانده می‌شود.
این فرآیند چه مزیتی دارد؟
هر زمان که شما نسخه PrestaSDK را از طریق Composer بروزرسانی کنید، شماره نسخه در URL فایل‌ها تغییر می‌کند. این کار باعث می‌شود که مرورگر کاربران مجبور شود نسخه جدید فایل‌ها را دانلود کند و مشکل کش به طور کامل حل شود.
اگر نسخه SDK تغییر کرده باشد، AdminController به صورت خودکار AssetPublisher را مجدداً فراخوانی می‌کند تا فایل‌های جدید جایگزین شوند.
### ۶.۲. چرخه درخواست و Middleware ها
PanelCore (که در AdminController استفاده می‌شود) یک سیستم قدرتمند شبیه به Middleware برای مدیریت چرخه حیات درخواست‌ها (Request Lifecycle) ارائه می‌دهد. این سیستم به شما اجازه می‌دهد تا کدهایی را قبل یا بعد از اجرای منطق اصلی یک "بخش" (Section) اجرا کنید.
این قابلیت برای مواردی مانند اعتبارسنجی دسترسی‌ها، پردازش داده‌های POST قبل از نمایش فرم، یا بارگذاری داده‌های مشترک بین چند بخش بسیار مفید است.
#### نحوه استفاده از middlewaresACL
برای استفاده از این قابلیت، باید پراپرتی $middlewaresACL را در کنترلر خود تعریف کنید. این پراپرتی یک آرایه است که مشخص می‌کند کدام متدها (Middleware ها) باید در چه زمانی اجرا شوند.
ساختار آرایه:
$this->middlewaresACL = [
    'before' => [
        // 'بخش@کنترلر' => ['نام_میدلور۱', 'نام_میدلور۲'],
    ],
    'after' => [],
    'ignore' => [], // برای نادیده گرفتن یک میدلور در شرایط خاص
];
- before: متدهای تعریف شده در این بخش، قبل از اجرای متد section... اصلی اجرا می‌شوند.
- after: متدهای تعریف شده در این بخش، بعد از اجرای متد section... اصلی اجرا می‌شوند.
- الگوی تعریف:
- *: برای تمام بخش‌ها در تمام کنترلرها.
- *@AdminMyController: برای تمام بخش‌ها در کنترلر AdminMyController.
- settings@AdminMyController: فقط برای بخش settings در کنترلر AdminMyController.
مثال عملی:
فرض کنید می‌خواهیم قبل از نمایش فرم ویرایش (sectionEdit)، بررسی کنیم که آیا آیتم درخواستی وجود دارد یا خیر.
```php
class AdminCustomProductsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->middlewaresACL = [
            'before' => [
                'edit@AdminCustomProducts' => ['loadProduct'], // قبل از sectionEdit اجرا شود
            ],
        ];
    }
    
    /**
     * متد میدلور باید با پیشوند 'middleware' نام‌گذاری شود.
     */
    public function middlewareLoadProduct()
    {
        $id_product = (int)Tools::getValue('id_product');
        $product = new Product($id_product);
        
        if (!Validate::isLoadedObject($product)) {
            // اگر محصول وجود نداشت، به لیست برگردان
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomProducts'));
        }
        
        // محصول را برای استفاده در متد اصلی در دسترس قرار بده
        $this->product = $product;
        
        return $this->runNext(); // اجرای میدلور یا متد بعدی در صف
    }

    public function sectionEdit()
    {
        // به لطف میدلور، اینجا مطمئن هستیم که $this->product لود شده است
        // ...
    }
}
```
نکته مهم: در پایان هر متد Middleware، باید $this->runNext() را فراخوانی کنید تا اجرای چرخه درخواست ادامه پیدا کند.
### ۶.۳. کلاس‌های کمکی (Utilities)
PrestaSDK شامل چند کلاس کمکی دیگر نیز می‌شود که کارهای روزمره را ساده‌تر می‌کنند.
#### HelperMethods

این کلاس شامل متدهای استاتیک برای کارهای عمومی است:

- **setFlashMessage($message, $type)**: یک پیام موقت (Flash Message) برای نمایش به کاربر تنظیم می‌کند (مثلاً بعد از یک redirect).
- **getFlashMessage()**: پیام تنظیم شده را می‌خواند و آن را از حافظه پاک می‌کند. پنل مدیریت SDK به صورت خودکار این پیام‌ها را نمایش می‌دهد.
- **setCookie($name, $data) / getCookie($name, $key)**: برای کار ساده‌تر با کوکی‌های پرستاشاپ.

#### VersionHelper

این کلاس یک متد استاتیک به نام `getSDKVersion()` دارد که نسخه فعلی SDK را از فایل composer.json آن می‌خواند. این کلاس به طور داخلی توسط AssetPublisher استفاده می‌شود.
