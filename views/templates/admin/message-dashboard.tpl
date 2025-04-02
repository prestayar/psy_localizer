{if empty($changeFileOK)}
    <div class="alert alert-danger">
        {l s='بومی سازی به صورت ناقص انجام شده است ، به پیکربندی ماژول بومی ساز مراجعه کنید:' mod='psy_localizer'}

        <a href="{$nativeLink}" target="_blank">
            {l s='تنظیمات بومی ساز' mod='psy_localizer'}
        </a>
    </div>
{/if}