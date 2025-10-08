{assign var=startupOptions value=$wsdkDashboard.options|json_encode}
{assign var=rootAttributes value=$wsdkDashboard.attributes|default:[]}
{assign var=rootAttrString value=''}
{foreach from=$rootAttributes key=attr item=value}
    {capture assign=currentAttr}
        {if $value === null}
            {$attr|escape:'htmlall':'UTF-8'}
        {else}
            {$attr|escape:'htmlall':'UTF-8'}="{$value|escape:'htmlall':'UTF-8'}"
        {/if}
    {/capture}
    {assign var=rootAttrString value=$rootAttrString|cat:' '|cat:$currentAttr}
{/foreach}
{if isset($wsdkDashboard.assets.css) && $wsdkDashboard.assets.css}
<link rel="stylesheet" href="{$wsdkDashboard.assets.css|escape:'htmlall':'UTF-8'}" type="text/css" media="all">
{/if}
<div{if $wsdkDashboard.id} id="{$wsdkDashboard.id|escape:'htmlall':'UTF-8'}"{/if} class="{$wsdkDashboard.classes|escape:'htmlall':'UTF-8'}" dir="{$wsdkDashboard.dir|escape:'htmlall':'UTF-8'}"{$rootAttrString}>
    {if isset($wsdkDashboard.templates.health)}
        {include file=$wsdkDashboard.templates.health}
    {/if}
    <div class="row wsdk-dashboard__grid">
        <div class="col-12 col-lg-8 wsdk-dashboard__column">
            {if isset($wsdkDashboard.templates.activity)}
                {include file=$wsdkDashboard.templates.activity}
            {/if}
        </div>
        <div class="col-12 col-lg-4 wsdk-dashboard__column">
            {if isset($wsdkDashboard.templates.checklist)}
                {if !isset($wsdkDashboard.checklist.completed) || empty($wsdkDashboard.checklist.completed)}
                    {include file=$wsdkDashboard.templates.checklist}
                {/if}
            {/if}        
            {if isset($wsdkDashboard.templates.quickActions)}
                {include file=$wsdkDashboard.templates.quickActions}
            {/if}
            {if isset($wsdkDashboard.templates.tips)}
                {include file=$wsdkDashboard.templates.tips}
            {/if}
        </div>
    </div>
</div>
{if isset($wsdkDashboard.assets.js) && $wsdkDashboard.assets.js}
<script type="module">
    import { initDashboard } from '{$wsdkDashboard.assets.js|escape:'htmlall':'UTF-8'}';
    window.WSDK = window.WSDK || {};
    window.WSDK.initDashboard = initDashboard;
    window.WSDK.initDashboard('{$wsdkDashboard.container|escape:'javascript'}', {$startupOptions nofilter});
</script>
{/if}
