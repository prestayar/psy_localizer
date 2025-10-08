<section class="wsdk-card"
         data-wsdk-card="tips"
         data-state="{$wsdkDashboard.tips.state|escape:'htmlall':'UTF-8'}"
         data-wsdk-tip >
    <header class="wsdk-card__header">
        <div>
            <h2 class="wsdk-card__title">{$wsdkDashboard.tips.title|escape:'htmlall':'UTF-8'}</h2>
            <p class="wsdk-card__description">{$wsdkDashboard.tips.description|escape:'htmlall':'UTF-8'}</p>
        </div>
        <button type="button"
                class="btn btn-link wsdk-card__dismiss"
                data-wsdk-dismiss
                aria-label="{l s='Dismiss tip' d='Modules.Wapaymentmanagerpro.Dashboard'}">
            <span class="material-icons" aria-hidden="true">close</span>
        </button>
    </header>
    <div class="wsdk-card__skeleton">
        <span class="wsdk-skeleton wsdk-skeleton--block"></span>
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
    </div>
    <div class="wsdk-card__content">
        <p class="wsdk-tip__text">{$wsdkDashboard.tips.text|escape:'htmlall':'UTF-8'}</p>
        {if $wsdkDashboard.tips.cta && $wsdkDashboard.tips.link}
            <a href="{$wsdkDashboard.tips.link|escape:'htmlall':'UTF-8'}"
               class="btn btn-primary wsdk-tip__cta"
               target="_blank"
               rel="noopener">
                {$wsdkDashboard.tips.cta|escape:'htmlall':'UTF-8'}
            </a>
        {/if}
    </div>
    <div class="wsdk-card__empty">
        <p>{$wsdkDashboard.tips.emptyMessage|escape:'htmlall':'UTF-8'}</p>
    </div>
</section>
