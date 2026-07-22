# معماری ماژول

## ساختار کلی

`psy_localizer` یک ماژول PrestaShop برای بومی‌سازی فروشگاه با تمرکز بر فارسی و بازار ایران است. کلاس `Psy_Localizer` در ریشهٔ ماژول، پیکربندی و Controller مدیریتی `AdminLocalizerPanel` را ثبت می‌کند.

## جریان پنل مدیریت

- `AdminLocalizerPanelController` بخش پیش‌فرض پنل را `dashboard` قرار می‌دهد.
- بخش `dashboard` اطلاعات نسخه را از `ProductInfoManager` می‌خواند و آن را به `DashboardFactory` می‌سپارد.
- `DashboardFactory` با اجزای Dashboard در `PrestaSDK V071` نمای سلامت و checklist پیکربندی را می‌سازد.
- بخش `refresh` cache اطلاعات ماژول را پاک می‌کند و کاربر را به داشبورد بازمی‌گرداند.

## سازگاری قیمت‌های خاص

`LocalizerSpecificPriceController` Controller قیمت‌های خاص PrestaShop 9 را decorate می‌کند. خروجی endpoint فهرست قیمت‌ها پس از دریافت از Controller هسته، با بازهٔ تاریخ جلالی تکمیل می‌شود.

## دارایی‌های رابط کاربری

Dashboard از `views/css/wsdk_dashboard.css` و `views/js/wsdk_dashboard.js` استفاده می‌کند. اسکریپت عمومی پنل نیز در `views/js/admin/main.js` قرار دارد.
