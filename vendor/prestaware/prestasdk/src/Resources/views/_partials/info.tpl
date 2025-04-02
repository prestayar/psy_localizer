{if isset($module_about_url)}
<a id="wsdk-info" href="{$module_about_url}" target="_blank" {if $status_update == 'warning'}class="wsdk-info--update"{/if}>
{else}
<div id="wsdk-info" {if $status_update == 'warning'}class="wsdk-info--update"{/if}>
{/if}
    <div class="wsdk-info__title">
        <div class="wsdk-info__logo">
            <img src="{$module_logo_src}" alt="{$module_name}">
        </div>
        <div class="wsdk-info__name">
            <h4>{$module_displayName}</h4>

            {if !empty($tooltip_message)}
                <span data-placement="bottom" data-toggle="pstooltip" class="tooltip-link" title="{$tooltip_message}">
            {/if}

            <span class="badge badge-{$status_update}">{$module_name} V{$module_version}</span>

            {if !empty($tooltip_message)}
                </span>
            {/if}
        </div>
    </div>

{if isset($module_about_url)}
</a>
{else}
</div>
{/if}

