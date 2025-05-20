{**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 *}

<a href="{$href|escape:'html':'UTF-8'}" {if isset($class)}class="{$class}"{/if} {if isset($target)}target="{$target}"{/if} title="{$title}" {if isset($confirm)} onclick="return confirm('{$confirm}');"{/if}>
	<i class="icon-{$icon}"></i> {$title}
</a>