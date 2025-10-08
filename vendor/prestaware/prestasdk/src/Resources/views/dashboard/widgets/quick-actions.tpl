<section class="wsdk-card" data-wsdk-card="actions" data-state="{$wsdkDashboard.quickActions.state|escape:'htmlall':'UTF-8'}">
    <header class="wsdk-card__header">
        <div>
            <h2 class="wsdk-card__title">{$wsdkDashboard.quickActions.title|escape:'htmlall':'UTF-8'}</h2>
            <p class="wsdk-card__description">{$wsdkDashboard.quickActions.description|escape:'htmlall':'UTF-8'}</p>
        </div>
    </header>
    <div class="wsdk-card__skeleton">
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
    </div>
    <div class="wsdk-card__content">
        <ul class="wsdk-actions" role="list">
            {foreach from=$wsdkDashboard.quickActions.items item=action}
                <li>
                    {assign var=href value=null}
                    {if isset($action.href)}{assign var=href value=$action.href}{/if}
                    {assign var=target value=null}
                    {if isset($action.target)}{assign var=target value=$action.target}{/if}
                    {assign var=rel value=null}
                    {if isset($action.rel)}{assign var=rel value=$action.rel}{/if}
                    {if $href}
                        <a href="{$href|escape:'htmlall':'UTF-8'}"
                           class="btn btn-primary btn-block wsdk-actions__btn"
                           data-wsdk-action="{$action.action|escape:'htmlall':'UTF-8'}"
                           aria-label="{$action.label|escape:'htmlall':'UTF-8'}"
                           {if $target}target="{$target|escape:'htmlall':'UTF-8'}"{/if}
                           {if $rel}rel="{$rel|escape:'htmlall':'UTF-8'}"{/if}>
                            <span class="material-icons" aria-hidden="true">{$action.icon|escape:'htmlall':'UTF-8'}</span>
                            <span class="wsdk-actions__label">{$action.label|escape:'htmlall':'UTF-8'}</span>
                        </a>
                    {else}
                        <button type="button"
                                class="btn btn-primary btn-block wsdk-actions__btn"
                                data-wsdk-action="{$action.action|escape:'htmlall':'UTF-8'}"
                                aria-label="{$action.label|escape:'htmlall':'UTF-8'}">
                            <span class="material-icons" aria-hidden="true">{$action.icon|escape:'htmlall':'UTF-8'}</span>
                            <span class="wsdk-actions__label">{$action.label|escape:'htmlall':'UTF-8'}</span>
                        </button>
                    {/if}
                    <p class="wsdk-actions__description">{$action.description|escape:'htmlall':'UTF-8'}</p>
                </li>
            {/foreach}
            {if empty($wsdkDashboard.quickActions.items)}
                <li class="wsdk-empty">{$wsdkDashboard.quickActions.emptyMessage|escape:'htmlall':'UTF-8'}</li>
            {/if}
        </ul>
        <p class="wsdk-actions__help">{$wsdkDashboard.quickActions.help|escape:'htmlall':'UTF-8'}</p>
    </div>
    <div class="wsdk-card__empty">
        <p>{$wsdkDashboard.quickActions.emptyMessage|escape:'htmlall':'UTF-8'}</p>
    </div>
    <div class="wsdk-card__error">
        <p>{$wsdkDashboard.quickActions.errorMessage|escape:'htmlall':'UTF-8'}</p>
    </div>
</section>
