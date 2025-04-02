{**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 *}

{if isset($menuItems)}
<div class="wsdk-menu {$active_section}">
	<div class="wsdk-menu-collapse"  onclick="toggleMenu(this)">
		<div class="wsdk-menu-version">
			<i class="icon-info-circle"></i> {$module->displayName} {$module->version}
		</div>
		<div class="wsdk-menu-collapse-arrow">
			<i class="material-icons rtl-flip">chevron_left</i>
			<i class="material-icons rtl-flip">chevron_left</i>
		</div>		
	</div>
	{foreach from=$menuItems item=group key=group_key} 
		<div id="{$group_key}" class="list-group wsdk-panel-menu">
		{foreach from=$group item=item key=item_key}
			{if !isset($item.is_show) OR (isset($item.is_show) AND $item.is_show)}
				<div id="{$item_key}" class="list-group-item{if isset($item.class)} {$item.class}{/if}">
					<a class="{if !empty($item.active) OR ($item_key == $active_section)}active{/if}" href="{if isset($item.link)}{$item.link}{else}#{/if}">
						<i class="{if isset($item.icon) }{$item.icon}{else}icon-caret-right{/if}"></i>
						<span>{$item.title}</span>

						{if isset($item.badge) && !empty($item.badge)}<span class="badge psy_bg_danger">{$item.badge}</span>{/if}

						{if isset($item.label.content) && !empty($item.label.content)}
							<span class="label {if isset($item.label.class)}{$item.label.class}{else}label-default{/if}">
								{$item.label.content}
							</span>
						{/if}
					</a>
					{if isset($item.items) && !empty($item.items)}
						<span class="grower
						{if empty($active_section) && array_key_exists($active_section, $item.items)}active{/if}
						{if !empty($active_section) && array_key_exists($active_section, $item.items) }open{else}close{/if}"></span>
						<ul {if !empty($active_section) && array_key_exists($active_section, $item.items)}class="open"{/if}>
							{foreach from=$item.items item=sub_item key=sub_key}
								<li>
									<a class="{if isset($sub_item.active) || (!empty($active_section) && $sub_key == $active_section)}active{/if}" href="{$sub_item.link}">
										{if isset($sub_item.icon)}<i class="{$sub_item.icon}"></i>{/if} <span>{$sub_item.title}</span>
										{if isset($sub_item.badge) && !empty($sub_item.badge)}<span class="badge psy_bg_danger">{$sub_item.badge}</span>{/if}
									</a>
								</li>
							{/foreach}
						</ul>
					{/if}
				</div>
			{/if}
		{/foreach}

		</div>

	{/foreach}
	</div>
{/if}