# Hookها و رویدادها

## `actionAdminControllerSetMedia`

`LocalizerModule::hookActionAdminControllerSetMedia()` هنگام فعال بودن TinyMCE، assetهای TinyMCE هسته را از صف Back Office حذف می‌کند و TinyMCE 5 همراه ماژول و adapter آن را به‌ترتیب اضافه می‌کند. این Hook فقط برای زبان فارسی و به‌جز `AdminTranslations` اجرا می‌شود.

Hookهای دیگر ماژول همچنان برای بخش‌های Controller مدیریتی `dashboard` و `refresh` استفاده می‌شوند.
