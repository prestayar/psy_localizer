# رابط‌های عمومی

## Controller مدیریت

- `AdminLocalizerPanelController::sectionDashboard(): string` — داشبورد سلامت، نسخه و checklist ماژول را render می‌کند.
- `AdminLocalizerPanelController::sectionRefresh(): void` — cache اطلاعات نسخه را تازه‌سازی کرده و به داشبورد redirect می‌کند.
- `AdminLocalizerPanelController::sectionConfigure()` — فرم تنظیمات بومی‌سازی را نمایش و ذخیره می‌کند.
- `LocalizerSpecificPriceController::listAction(Request $request, int $productId): JsonResponse` — فهرست قیمت‌های خاص را با بازهٔ تاریخ جلالی بازمی‌گرداند.
- `LocalizerGetCartForViewingHandler::handle(GetCartForViewing $query)` — خروجی نمایش سبد را با تاریخ‌های محلی‌شده بازمی‌گرداند.

## Twig Extension

- `LocalizerLocalizationExtension::dateFormatFull(DateTimeInterface|string $date): string` — فیلتر `date_format_full` را با فرمت زبان کاربر و مسیر نمایش تاریخ ماژول ارائه می‌کند.

## Factory داشبورد

- `DashboardFactory::create(Module $module, Context $context, array $productInfo = []): DashboardBuilder` — سازندهٔ Dashboard آمادهٔ render را بازمی‌گرداند.
- `DashboardFactory::buildConfiguration(Module $module, array $productInfo = []): array` — پیکربندی widgetها، دارایی‌ها و اطلاعات نسخه را تولید می‌کند.
