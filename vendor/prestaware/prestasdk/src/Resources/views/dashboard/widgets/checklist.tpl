<section class="wsdk-card" data-wsdk-card="checklist" data-state="{$wsdkDashboard.checklist.state|escape:'htmlall':'UTF-8'}">
    <header class="wsdk-card__header">
        <div>
            <h2 class="wsdk-card__title">{$wsdkDashboard.checklist.title|escape:'htmlall':'UTF-8'}</h2>
            <p class="wsdk-card__description">{$wsdkDashboard.checklist.description|escape:'htmlall':'UTF-8'}</p>
        </div>
    </header>
    <div class="wsdk-card__skeleton">
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
    </div>
    <div class="wsdk-card__content">
        <ul class="wsdk-checklist" role="list">
            {foreach from=$wsdkDashboard.checklist.items item=item}
                <li class="wsdk-checklist__item{if $item.ok} is-complete{/if}">
                    <span class="material-icons" aria-hidden="true">{if $item.ok}check_circle{else}error_outline{/if}</span>
                    <a href="{$item.link|escape:'htmlall':'UTF-8'}" class="wsdk-checklist__link">{$item.label|escape:'htmlall':'UTF-8'}</a>
                </li>
            {/foreach}
            {if empty($wsdkDashboard.checklist.items)}
                <li class="wsdk-empty">{$wsdkDashboard.checklist.emptyMessage|escape:'htmlall':'UTF-8'}</li>
            {/if}
        </ul>
        <p class="wsdk-checklist__summary">{$wsdkDashboard.checklist.summary|escape:'htmlall':'UTF-8'}</p>
    </div>
    <div class="wsdk-card__empty">
        <p>{$wsdkDashboard.checklist.emptyMessage|escape:'htmlall':'UTF-8'}</p>
    </div>
    <div class="wsdk-card__error">
        <p>{$wsdkDashboard.checklist.errorMessage|escape:'htmlall':'UTF-8'}</p>
    </div>
</section>
