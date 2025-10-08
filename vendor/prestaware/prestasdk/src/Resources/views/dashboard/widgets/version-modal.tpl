<div id="wsdk-dashboard-version-modal"
     class="wsdk-modal"
     data-wsdk-version-modal
     aria-hidden="true">
    <div class="wsdk-modal__backdrop" data-wsdk-version-close></div>
    <div class="wsdk-modal__dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="wsdk-dashboard-version-title"
         aria-describedby="wsdk-dashboard-version-description"
         data-wsdk-version-dialog>
        <header class="wsdk-modal__header">
            <div class="wsdk-modal__header-main">
                <h3 class="wsdk-modal__title" id="wsdk-dashboard-version-title">{$wsdkDashboard.health.version.title|unescape:'htmlall'|escape:'htmlall':'UTF-8'}</h3>
                <span class="wsdk-badge wsdk-badge--{$wsdkDashboard.health.version.statusBadge.type|escape:'htmlall':'UTF-8'}">{$wsdkDashboard.health.version.statusBadge.label|unescape:'htmlall'|escape:'htmlall':'UTF-8'}</span>
                <p class="wsdk-modal__description" id="wsdk-dashboard-version-description">{$wsdkDashboard.health.version.description|unescape:'htmlall'|escape:'htmlall':'UTF-8'}</p>
            </div>
            <button type="button"
                    class="wsdk-modal__close"
                    data-wsdk-version-close
                    aria-label="{$wsdkDashboard.health.version.closeLabel|escape:'htmlall':'UTF-8'}">
                <span class="material-icons" aria-hidden="true">close</span>
            </button>
        </header>
        <div class="wsdk-modal__body">
            <ul class="wsdk-version-list">
                <li>
                    <span class="wsdk-version-list__label">{l s='Installed'}</span>
                    <span class="wsdk-version-list__value">{$wsdkDashboard.health.version.installed|escape:'htmlall':'UTF-8'}</span>
                </li>
                <li>
                    <span class="wsdk-version-list__label">{l s='Latest'}</span>
                    <span class="wsdk-version-list__value">{$wsdkDashboard.health.version.latest|escape:'htmlall':'UTF-8'}</span>
                </li>
                {if $wsdkDashboard.health.version.checkedAtLabel}
                    <li class="wsdk-version-list__checked">
                        <span class="material-icons" aria-hidden="true">schedule</span>
                        <span>{$wsdkDashboard.health.version.checkedAtLabel|escape:'htmlall':'UTF-8'}</span>
                    </li>
                {/if}
            </ul>
            <div class="wsdk-changelog">
                <h4 class="wsdk-changelog__title">
                {l s='Latest changes' }
                {if $wsdkDashboard.health.version.timeUpgradeLabel}
                    - {$wsdkDashboard.health.version.timeUpgradeLabel|escape:'htmlall':'UTF-8'}
                {/if}
                </h4>
                <ul class="wsdk-changelog__list">
                    {foreach from=$wsdkDashboard.health.version.changelog item=entry}
                        <li>{$entry|escape:'htmlall':'UTF-8'}</li>
                    {/foreach}
                    {if empty($wsdkDashboard.health.version.changelog)}
                        <li class="wsdk-empty">{$wsdkDashboard.health.version.emptyMessage|escape:'htmlall':'UTF-8'}</li>
                    {/if}
                </ul>
            </div>
        </div>
        <footer class="wsdk-modal__footer">
            {if $wsdkDashboard.health.version.updateAvailable && $wsdkDashboard.health.version.productUrl}
                <a href="{$wsdkDashboard.health.version.productUrl|escape:'htmlall':'UTF-8'}"
                   class="btn btn-primary"
                   target="_blank"
                   rel="noopener">
                    <span class="material-icons" aria-hidden="true">open_in_new</span>
                    <span>{$wsdkDashboard.health.version.productCta|escape:'htmlall':'UTF-8'}</span>
                </a>
            {/if}
            <button type="button"
                    class="btn btn-outline-secondary"
                    data-wsdk-version-close
                    data-wsdk-version-initial>{$wsdkDashboard.health.version.closeLabel|escape:'htmlall':'UTF-8'}</button>
        </footer>
    </div>
</div>
