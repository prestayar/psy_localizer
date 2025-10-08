<header class="wsdk-dashboard__header" data-state="{$wsdkDashboard.health.state|escape:'htmlall':'UTF-8'}">
    <div class="wsdk-dashboard__header-body">
        <div class="wsdk-dashboard__header-top">
            <span class="wsdk-dashboard__header-label">{$wsdkDashboard.health.title|escape:'htmlall':'UTF-8'}</span>
            <div class="wsdk-health-badges"
                 data-wsdk-badges="{$wsdkDashboard.health.badges|json_encode|escape:'htmlall':'UTF-8'}"></div>
            {if $wsdkDashboard.health.support}
                <a href="{$wsdkDashboard.health.support.href|escape:'htmlall':'UTF-8'}"
                   class="wsdk-health__support wsdk-dashboard__support-link"
                   {if $wsdkDashboard.health.support.target}target="{$wsdkDashboard.health.support.target|escape:'htmlall':'UTF-8'}"{/if}
                   {if $wsdkDashboard.health.support.rel}rel="{$wsdkDashboard.health.support.rel|escape:'htmlall':'UTF-8'}"{elseif $wsdkDashboard.health.support.target == '_blank'}rel="noopener"{/if}
                   {if $wsdkDashboard.health.support.title}title="{$wsdkDashboard.health.support.title|escape:'htmlall':'UTF-8'}"{/if}>
                    <span class="material-icons" aria-hidden="true">support_agent</span>
                    <span>{$wsdkDashboard.health.support.label|escape:'htmlall':'UTF-8'}</span>
                </a>
            {/if}
        </div>
        {if $wsdkDashboard.health.summary}
            <p class="wsdk-dashboard__header-summary">{$wsdkDashboard.health.summary|escape:'htmlall':'UTF-8'}</p>
        {/if}
    </div>
    {if $wsdkDashboard.health.progress}
        <div class="wsdk-dashboard__header-progress">
            <div class="wsdk-progress wsdk-progress--inline" data-wsdk-progress="{$wsdkDashboard.health.progress.value|escape:'htmlall':'UTF-8'}">
                <div class="wsdk-progress__track">
                    <div class="wsdk-progress__bar"
                         data-wsdk-progress-bar
                         role="progressbar"
                         aria-valuemin="0"
                         aria-valuemax="100"
                         aria-valuenow="0">
                        <span class="sr-only">{$wsdkDashboard.health.progress.assistive|escape:'htmlall':'UTF-8'}</span>
                    </div>
                </div>
                <div class="wsdk-progress__meta">
                    <span class="wsdk-progress__label">{$wsdkDashboard.health.progress.label|escape:'htmlall':'UTF-8'}</span>
                    <span class="wsdk-progress__value" data-wsdk-progress-value>0%</span>
                </div>
            </div>
        </div>
    {/if}
    <div class="wsdk-dashboard__header-skeleton">
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
        <span class="wsdk-skeleton wsdk-skeleton--line"></span>
        <span class="wsdk-skeleton wsdk-skeleton--block"></span>
    </div>
    <div class="wsdk-dashboard__header-empty">
        <p>{$wsdkDashboard.health.emptyMessage|escape:'htmlall':'UTF-8'}</p>
    </div>
    <div class="wsdk-dashboard__header-error">
        <p>{$wsdkDashboard.health.errorMessage|escape:'htmlall':'UTF-8'}</p>
    </div>
    {if isset($wsdkDashboard.templates.versionModal)}
        {include file=$wsdkDashboard.templates.versionModal}
    {/if}
</header>
