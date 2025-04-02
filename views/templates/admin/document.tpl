<div class="panel">
  <h3>توضیحات و نمونه کد استفاده از displayDateCustom</h3>
  <p>
    این تابع امکان نمایش تاریخ با فرمت اختصاصی را فراهم می‌کند. شما می‌توانید تاریخ را به صورت شمسی یا میلادی نمایش دهید.
  </p>
  
  <h4>نمونه استفاده در PHP</h4>
  <pre><code class="language-php">
Module::getInstanceByName('psy_localizer')->displayDateCustom($dateTime, $format, $gregorian);

// or

Tools::displayDateCustom($dateTime, $format, $gregorian);
</code></pre>
  
  <h4>نمونه استفاده در قالب‌های Smarty</h4>
  <pre><code class="language-smarty">
{literal}{Tools::displayDateCustom($dateTime, $format, $gregorian)}{/literal}
</code></pre>
  
  <p>
    در مثال‌های بالا:
    <br>
    <strong>$dateTime</strong>: تاریخ مورد نظر به صورت رشته (مثلاً "2025-02-11 15:30:00")
    <br>
    <strong>$format</strong>: فرمت نمایش تاریخ (مثلاً "Y-m-d H:i:s")
    <br>
    <strong>$gregorian</strong>: تعیین نوع تقویم؛ مقدار <code>1</code> برای تقویم میلادی و <code>0</code> برای تقویم شمسی
  </p>
</div>