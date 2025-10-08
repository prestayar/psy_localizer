<section class="wsdk-card" data-wsdk-card="activity" data-state="{$wsdkDashboard.activity.state|escape:'htmlall':'UTF-8'}">
    <header class="wsdk-card__header">
        <div>
            <h2 class="wsdk-card__title">{$wsdkDashboard.activity.title|escape:'htmlall':'UTF-8'}</h2>
            <p class="wsdk-card__description">{$wsdkDashboard.activity.description|escape:'htmlall':'UTF-8'}</p>
        </div>
    </header>
    <div class="wsdk-card__skeleton">
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
    </div>
    <div class="wsdk-card__content">
        <ul class="wsdk-activity">
            {foreach from=$wsdkDashboard.activity.items item=entry}
                <li class="wsdk-activity__item">
                    <div class="wsdk-activity__meta">
                        <span class="wsdk-activity__time">{$entry.meta.time|escape:'htmlall':'UTF-8'}</span>
                        {if isset($entry.meta.badges)}
                            {foreach from=$entry.meta.badges item=badge}
                                <a href="{$badge.href|escape:'htmlall':'UTF-8'}"
                                   class="badge {if isset($badge.variant) && $badge.variant !== ''}badge-{$badge.variant|escape:'htmlall':'UTF-8'} {/if}badge-sm ml-1"
                                   title="{$badge.title|escape:'htmlall':'UTF-8'}">
                                    {$badge.label|escape:'htmlall':'UTF-8'}
                                </a>
                            {/foreach}
                        {/if}
                    </div>
                    <span class="wsdk-activity__text">{$entry.text nofilter}</span>
                </li>
            {/foreach}
        </ul>
    </div>
    <div class="wsdk-card__empty">
        <p>{$wsdkDashboard.activity.emptyMessage|escape:'htmlall':'UTF-8'}</p>
    </div>
    <div class="wsdk-card__error">
        <p>{$wsdkDashboard.activity.errorMessage|escape:'htmlall':'UTF-8'}</p>
    </div>
</section>
