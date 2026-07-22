# ثبت تصمیم‌ها

## داشبورد مدیریت با PrestaSDK V071

Dashboard ماژول مستقیماً از namespace `PrestaSDK\\V071` استفاده می‌کند، چون همین نسخه در `composer.json` و autoload وابستگی نصب‌شده تعریف شده است. این کار مانع خطای `Class not found` هنگام باز کردن پنل می‌شود.

## وضعیت checklist از Configuration

وضعیت آیتم Native در checklist از `Localizer_Native_Active` خوانده می‌شود تا داشبورد وضعیت واقعی تنظیمات را نمایش دهد، نه یک مقدار ثابت.

## سازگاری Specific Price با PrestaShop 9

Decorator قیمت‌های خاص از Controller و امضای `listAction` در PrestaShop 9 استفاده می‌کند. پاسخ جدید شامل کلید `specificPrices` است؛ بنابراین تبدیل تاریخ فقط روی همین فهرست انجام می‌شود.
