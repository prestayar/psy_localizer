# Hookها و رویدادها

## `actionAdminControllerSetMedia`

`LocalizerModule::hookActionAdminControllerSetMedia()` هنگام فعال بودن TinyMCE، assetهای TinyMCE هسته را از صف Back Office حذف می‌کند و TinyMCE 5 همراه ماژول و adapter آن را به‌ترتیب اضافه می‌کند. این Hook فقط برای زبان فارسی و به‌جز `AdminTranslations` اجرا می‌شود.

Hook قدیمی `dashboardZoneOne` که فقط وضعیت patchهای هسته را نمایش می‌داد، در upgrade نسخهٔ `1.0.3` از نصب‌های موجود حذف می‌شود.
