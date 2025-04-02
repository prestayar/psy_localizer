{**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 *}

{if isset($_positions.Header)}
	<div id="wsdk-panel-header">
		{$_positions.Header}
	</div>
{/if}

<div id="wsdk-panel">
	{if isset($_positions.TopContainer)}
		{$_positions.TopContainer}
	{/if}

	<div class="wsdk-panel-content">
		{if isset($_positions.Sidebar)}
			<div class="wsdk-panel-sidebar">
				{include file="../_partials/info.tpl"}
				{$_positions.Sidebar}
			</div>
		{/if}

		<div class="wsdk-panel-main">
			{if isset($_positions.TopContent)}
				{$_positions.TopContent}
			{/if}
			
			{if isset($_flash.message) && !empty($_flash.message)}
				<div class="row">
					<div class="col-sm-12">
						<div class="alert alert-{if isset($_flash.type)}{$_flash.type|escape:'htmlall':'UTF-8'}{else}info{/if} ">
							<button type="button" class="close" data-dismiss="alert">×</button>
							{$_flash.message}
						</div>
					</div>
				</div>
			{/if}
			
			{$_content}
			
			{if isset($_positions.BottomContent)}
				{$_positions.BottomContent}
			{/if}
			
		</div>
	</div>
	
	{if isset($_positions.BottomContainer)}
		{$_positions.BottomContainer}
	{/if}
</div>

{if isset($_positions.Footer)}
	<div id="wsdk-panel-footer">
		{$_positions.Footer}
	</div>
{/if}