<div class="panel">
  <h3>راهنمای توسعه‌دهندگان</h3>

  <section>
    <h4>نمایش تاریخ با <code>displayDateCustom</code></h4>
    <p>این تابع امکان نمایش تاریخ با فرمت دلخواه را به‌صورت شمسی یا میلادی فراهم می‌کند.</p>

    <h5>استفاده در PHP</h5>
    <pre><code class="language-php">Module::getInstanceByName('psy_localizer')->displayDateCustom($dateTime, $format, $gregorian);

// or

Tools::displayDateCustom($dateTime, $format, $gregorian);</code></pre>

    <h5>استفاده در قالب‌های Smarty</h5>
    <pre><code class="language-smarty">{literal}{Tools::displayDateCustom($dateTime, $format, $gregorian)}{/literal}</code></pre>

    <p>
      <strong>$dateTime</strong>: تاریخ مورد نظر به‌صورت رشته، مانند <code>2025-02-11 15:30:00</code>.<br>
      <strong>$format</strong>: فرمت نمایش تاریخ، مانند <code>Y-m-d H:i:s</code>.<br>
      <strong>$gregorian</strong>: مقدار <code>1</code> برای تقویم میلادی و <code>0</code> برای تقویم شمسی.
    </p>
  </section>

  <hr>

  <section>
    <h4>جست‌وجوی فارسی و بازسازی فهرست</h4>
    <p>
      PrestaShop به‌طور پیش‌فرض برای نرمال‌سازی متن از ICU استفاده می‌کند. این تبدیل می‌تواند واژه‌های فارسی را به معادل لاتین تبدیل کند؛ برای مثال <code>باغبانی</code> به <code>baghbany</code> تبدیل می‌شود. این رفتار باعث می‌شود بعضی واژه‌های فارسی در فهرست جست‌وجو به‌درستی ثبت یا پیدا نشوند.
    </p>
    <p>
      این ماژول حروف فارسی و عربی را هنگام نرمال‌سازی حفظ می‌کند و نرمال‌سازی سایر زبان‌ها را بدون تغییر نگه می‌دارد. پس از نصب یا به‌روزرسانی ماژول، لازم است فهرست جست‌وجو را بازسازی کنید تا واژه‌های قبلی با شکل صحیح فارسی دوباره ثبت شوند.
    </p>

    <h5>مراحل لازم</h5>
    <ol>
      <li>کش فروشگاه را پاک کنید.</li>
      <li>در بخش <strong>پیکربندی فروشگاه ← جست‌وجو</strong>، گزینه <strong>بازسازی کل فهرست‌بندی</strong> را اجرا کنید.</li>
      <li>تا پایان عملیات صبر کنید و سپس جست‌وجوی چند واژه فارسی را در فروشگاه بررسی کنید.</li>
    </ol>

    <p class="alert alert-warning">
      بازسازی فهرست برای این تغییر ضروری است؛ واژه‌هایی که پیش از فعال‌شدن ماژول ایندکس شده‌اند خودکار اصلاح نمی‌شوند.
    </p>
  </section>
</div>
