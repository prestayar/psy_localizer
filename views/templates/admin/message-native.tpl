<div id="module-info" {if empty($changeFilesDone)}class="module-info-warning"{/if}>
    <div class="module-title">
        <i class="icon-globe"></i>
        <b>بومی ساز</b>
        <span>PS 1.7.8+</span>
    </div>
    <div class="module-info-content">
        <p>
            {l s='ماژول برای اعمال بومی سازی به صورت کامل نیاز به تغییر در فایل های زیر در هسته پرستاشاپ دارد.' mod='psy_localizer'}
            <br>
        </p>
        {if !empty($filesCore)}
        <ul>
            {foreach $filesCore as $fileCore}
                <li>{$fileCore.path} ({$fileCore.title})</li>
            {/foreach}
        </ul>
        {/if}
        <br>
        {if empty($changeFilesDone)}
            <p>{l s='تغییرات در هسته برای بومی سازی کامل انجام نشده است.' mod='psy_localizer'}</p>
            <p>
                <b style="color: red">
                {l s='نکته مهم : در صورت ارتقا هسته پرستاشاپ نیاز خواهید داشت یکبار تنظیمات این صفحه را مجدد انجام دهید تا تغییرات اعمال شود.' mod='psy_localizer'}
                </b>
            </p>
        {else}
            <p>{l s='تغییرات در هسته بدون مشکل انجام شده است و بومی ساز آماده استفاده است.' mod='psy_localizer'}</p>
        {/if}

        <div class="module-info-links">
            <a href="https://prestayar.com/d/89" class="btn btn-default">
                <i class="icon-question-circle"></i>&nbsp;
                {l s='گفتگوی ویژه بومی ساز' mod='psy_localizer'}
            </a>
        </div>
    </div>
</div>