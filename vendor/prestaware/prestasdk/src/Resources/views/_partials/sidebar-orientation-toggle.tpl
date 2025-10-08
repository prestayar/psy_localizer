{if isset($sidebar_orientation)}
    {assign var='orientation' value=$sidebar_orientation|lower}
    {if $orientation != 'horizontal' && $orientation != 'vertical'}
        {assign var='orientation' value='horizontal'}
    {/if}
    {assign var='labelHorizontal' value=$switch_to_horizontal_label|default:''}
    {assign var='labelVertical' value=$switch_to_vertical_label|default:''}
    {assign var='groupLabel' value=$toggle_label|default:'Switch menu layout'}
    {if empty($groupLabel)}
        {assign var='groupLabel' value='Switch menu layout'}
    {/if}
    {if $orientation == 'horizontal'}
        {assign var='nextOrientation' value='vertical'}
        {assign var='nextLabel' value=$labelVertical}
        {assign var='nextIcon' value='view_stream'}
    {else}
        {assign var='nextOrientation' value='horizontal'}
        {assign var='nextLabel' value=$labelHorizontal}
        {assign var='nextIcon' value='view_week'}
    {/if}
    {if empty($nextLabel)}
        {assign var='nextLabel' value=$groupLabel}
    {/if}
    <div class="wsdk-sidebar-toggle"
         role="group"
         {if !empty($groupLabel)}aria-label="{$groupLabel|escape:'htmlall':'UTF-8'}"{/if}
         data-initial-orientation="{$orientation|escape:'htmlall':'UTF-8'}"
         data-current-orientation="{$orientation|escape:'htmlall':'UTF-8'}"
         data-label-horizontal="{$labelHorizontal|escape:'htmlall':'UTF-8'}"
         data-label-vertical="{$labelVertical|escape:'htmlall':'UTF-8'}"
         data-icon-horizontal="view_week"
         data-icon-vertical="view_stream">
        <button type="button"
                class="wsdk-sidebar-toggle__button"
                data-next-orientation="{$nextOrientation|escape:'htmlall':'UTF-8'}"
                aria-label="{$nextLabel|escape:'htmlall':'UTF-8'}"
                title="{$nextLabel|escape:'htmlall':'UTF-8'}">
            <span class="material-icons" aria-hidden="true">{$nextIcon|escape:'htmlall':'UTF-8'}</span>
            <span class="wsdk-sidebar-toggle__text wsdk-visually-hidden">{$nextLabel|escape:'htmlall':'UTF-8'}</span>
        </button>
    </div>
{/if}
