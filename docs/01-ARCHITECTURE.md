# معماری ماژول

## ساختار کلی

`psy_localizer` یک ماژول PrestaShop برای بومی‌سازی فروشگاه با تمرکز بر فارسی و بازار ایران است. کلاس `Psy_Localizer` در ریشهٔ ماژول، پیکربندی و Controller مدیریتی `AdminLocalizerPanel` را ثبت می‌کند.

## جریان پنل مدیریت

- `AdminLocalizerPanelController` بخش پیش‌فرض پنل را `dashboard` قرار می‌دهد.
- بخش `dashboard` اطلاعات نسخه را از `ProductInfoManager` می‌خواند و آن را به `DashboardFactory` می‌سپارد.
- `DashboardFactory` با اجزای Dashboard در `PrestaSDK V071` نمای سلامت و checklist پیکربندی را می‌سازد.
- بخش `refresh` cache اطلاعات ماژول را پاک می‌کند و کاربر را به داشبورد بازمی‌گرداند.

## نمایش تاریخ در Twig

`LocalizerLocalizationExtension`، Service مربوط به `LocalizationExtension` هسته را decorate می‌کند. فیلتر `date_format_full` تاریخ را به `Tools::displayDate()` می‌سپارد تا تنظیم تاریخ کامل زبان کاربر و تبدیل جلالی ماژول اعمال شود.

## سازگاری قیمت‌های خاص

`LocalizerSpecificPriceController` Controller قیمت‌های خاص PrestaShop 9 را decorate می‌کند. خروجی endpoint فهرست قیمت‌ها پس از دریافت از Controller هسته، با بازهٔ تاریخ جلالی تکمیل می‌شود.

## نمایش جزئیات سبد

`LocalizerGetCartForViewingHandler` Handler نمایش سبد را از طریق QueryBus جایگزین می‌کند. منطق اصلی هسته ابتدا اجرا می‌شود و ماژول فقط تاریخ ثبت‌نام مشتری، ثبت سفارش، ایجاد و ویرایش سبد را در `CartView` بازنویسی می‌کند.

## دارایی‌های رابط کاربری

Dashboard از `views/css/wsdk_dashboard.css` و `views/js/wsdk_dashboard.js` استفاده می‌کند. اسکریپت عمومی پنل نیز در `views/js/admin/main.js` قرار دارد.
