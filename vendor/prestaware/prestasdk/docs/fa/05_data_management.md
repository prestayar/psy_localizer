## فصل ۵: مدیریت داده‌ها (Models)
این فصل به شما نشان می‌دهد که چگونه با استفاده از کلاس BaseModel در PrestaSDK، به سادگی با دیتابیس کار کنید. این کلاس با ارث‌بری از ObjectModel پرستاشاپ، بسیاری از کارهای تکراری را خودکار می‌کند.
### ۵.۱. ساخت یک Model
برای تعریف یک موجودیت (Entity) جدید که به یک جدول در دیتابیس متصل است، یک کلاس در پوشه src/Entity/ (یا هر مسیر دلخواه دیگر) ایجاد کرده و آن را از PrestaSDK\V071\Model\BaseModel ارث‌بری کنید.
مراحل اصلی:
- ارث‌بری: کلاس شما باید از BaseModel ارث‌بری کند.
- تعریف ثابت‌ها: ثابت‌های TABLE و ID را برای مشخص کردن نام جدول و کلید اصلی آن تعریف کنید.
- تعریف $definition: ساختار مدل، شامل نام جدول، کلید اصلی و فیلدها را در پراپرتی استاتیک $definition تعریف کنید. این ساختار کاملاً مشابه ObjectModel استاندارد پرستاشاپ است.
- (اختیاری) تعریف ستون‌های ویژه: برای فعال‌سازی ویژگی‌های خودکار BaseModel، نام ستون‌های تاریخ، وضعیت و فروشگاه را در ثابت‌های مربوطه تعریف کنید.
مثال کامل (از ماژول wabulkupdate):

```php
// src/Entity/File.php

namespace PrestaWare\WaBulkUpdate\Entity;

use PrestaSDK\V071\Model\BaseModel;

class File extends BaseModel
{
    // 1. تعریف ثابت‌های اصلی
    const TABLE = 'wabulkupdate_file';
    const ID = 'id_wabulkupdate_file';

    // 2. تعریف ثابت‌های ویژه برای فعال‌سازی قابلیت‌های BaseModel
    const CREATED_AT_COLUMN = 'date_add';
    const UPDATED_AT_COLUMN = 'date_upd';
    const STATUS_COLUMN = 'status';
    // const ID_SHOP_COLUMN = 'id_shop'; // اگر جدول شما به فروشگاه مرتبط است

    // 3. تعریف پراپرتی‌های کلاس
    public $id;
    public $file_name;
    public $status;
    public $date_add;
    public $date_upd;

    // 4. تعریف ساختار مدل برای پرستاشاپ
    public static $definition = [
        'table' => self::TABLE,
        'primary' => self::ID,
        'fields' => [
            'file_name' => ['type' => self::TYPE_STRING, 'validate' => 'isFileName', 'required' => true, 'size' => 255],
            'status' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
```
### ۵.۲. ویژگی‌های BaseModel
کلاس BaseModel قابلیت‌های زیادی را به صورت خودکار به مدل شما اضافه می‌کند:
#### مدیریت خودکار تاریخ‌ها
اگر ثابت‌های CREATED_AT_COLUMN و UPDATED_AT_COLUMN را تعریف کنید:
- هنگام ایجاد یک رکورد جدید، هر دو فیلد date_add و date_upd به صورت خودکار با تاریخ و زمان فعلی پر می‌شوند.
- هنگام بروزرسانی یک رکورد، فیلد date_upd به صورت خودکار بروز می‌شود.
#### مدیریت خودکار وضعیت (Status)
اگر ثابت STATUS_COLUMN را تعریف کنید، می‌توانید از متد toggleStatus() برای فعال/غیرفعال کردن یک رکورد استفاده کنید. این متد به صورت خودکار مقدار ستون وضعیت را برعکس کرده و رکورد را ذخیره می‌کند. این قابلیت در HelperList بسیار کاربردی است.
```php
$file = new File($id);
$file->toggleStatus(); // وضعیت از 0 به 1 یا برعکس تغییر می‌کند
```
#### ذخیره‌سازی امن با اعتبارسنجی (safeSave)
به جای فراخوانی مستقیم متد save()، می‌توانید از safeSave() استفاده کنید. این متد قبل از ذخیره‌سازی، به صورت خودکار تمام اعتبارسنجی‌های تعریف شده در $definition را اجرا می‌کند. اگر داده‌ها معتبر نباشند، false برمی‌گرداند و از ذخیره رکورد نامعتبر جلوگیری می‌کند.
```php
$file = new File();
$file->file_name = 'invalid-name-@!#.xlsx'; // نامعتبر
$file->status = 1;

if ($file->safeSave()) {
    // این کد اجرا نخواهد شد
    echo "File saved successfully!";
} else {
    echo "Validation failed!";
}
```
#### مدیریت خودکار id_shop
اگر ماژول شما در حالت چندفروشگاهی (multishop) کار می‌کند و جدول شما ستون id_shop دارد، کافیست ثابت ID_SHOP_COLUMN را تعریف کنید. BaseModel به صورت خودکار هنگام ایجاد یک رکورد جدید، id_shop مربوط به فروشگاه فعلی را در آن ذخیره می‌کند.
