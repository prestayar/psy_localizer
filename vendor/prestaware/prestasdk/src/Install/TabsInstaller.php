<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
declare(strict_types=1);

namespace PrestaSDK\Install;

use Doctrine\ORM\Query\Expr\Func;

class TabsInstaller
{
    private \Module $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function installTabs(): bool
    {
        if (empty($this->module->moduleTabs)) {
            return true;
        }

        $moduleTabs = $this->setDefaultValueTabs($this->module->moduleTabs);

        $result = true;
        foreach ($moduleTabs as $tabItem) {
            $result = $result && $this->saveTab($tabItem);
        }

        return $result;
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function uninstallTabs(): bool
    {
        if (empty($this->module->moduleTabs)) {
            return true;
        }

        $moduleTabs = $this->setDefaultValueTabs($this->module->moduleTabs);

        $result = true;
        foreach ($moduleTabs as $tabItem) {
            if ($tabItem['class_name'] === $this->module->moduleGrandParentTab) {
                continue;
            }

            $id_tab = (int) \Tab::getIdFromClassName($tabItem['class_name']);

            $tab = new \Tab($id_tab);
            if (\Validate::isLoadedObject($tab) && $tab->module === $this->module->name) {
                $result = $result && $tab->delete();
            }
        }

        return $result;
    }

    public function saveTab($tabItem) {
        if (empty($tabItem)) {
            return false;
        }

        $tabId = \Tab::getIdFromClassName($tabItem['class_name']);

        if (!$tabId) {
            $tabId = null;
        }

        $tab = new \Tab($tabId);
        $tab->id_parent = $tabItem['parent_class_name'] ? \Tab::getIdFromClassName($tabItem['parent_class_name']) : 0;
        $tab->class_name = $tabItem['class_name'];
        $tab->route_name = isset($tabItem['route_name']) ? $tabItem['route_name'] : '';
        $tab->icon = isset($tabItem['icon']) ? $tabItem['icon'] : '';
        $tab->active = !empty($tabItem['visible']);
        $tab->enabled = 1;
        $tab->module = $this->module->name;
        $tab->name = [];

        $languages = \Language::getLanguages(false);
        foreach ($languages as $language) {
            $tab->name[(int) $language['id_lang']] = $tabItem['name'];
        }

        return $tab->save();
    }

    public function getDefaultValueTab($key, $item): array
    {
        if (is_array($item)) {
            $tab = $item;
        } else {
            $tab = [
                'title' => $item,
            ];
        }

        $tab['class_name'] = $key;

        if (!isset($tab['visible'])) {
            $tab['visible'] = true;
        } 

        $tab['wording_domain'] = $this->getWordingDomain();

        if (isset($tab['parent_class_name'])) {
            $tab['parent_class_name'] = $tab['parent_class_name'];
        } else {
            $tab['parent_class_name'] = $this->prentClassName($key);
        }

        if (!isset($tab['name'])) {
            $tab['name'] = $tab['title'];
        }

        return $tab;
    }

    public function setDefaultValueTabs($tabsModules): array
    {
        $tabs = [];

        if (count($tabsModules) > 1) {
            if (!empty($this->module->configsAdminController) && isset($tabsModules[$this->module->configsAdminController])) {
                $tab = $this->getDefaultValueTab($this->module->configsAdminController. 'Parent', $tabsModules[$this->module->configsAdminController]);
    
                $tabs[] = array_merge(
                    $tab, [
                        'parent_class_name' => $this->module->moduleGrandParentTab,
                    ]
                );
            }
        }

        foreach ($tabsModules as $key => $item) {
            $tabs[] = $this->getDefaultValueTab($key, $item);
        }
    
        return $tabs;
    }

    public function prentClassName($key)
    {
        if ($key == $this->module->configsAdminController . 'Parent') {
            return '';
        }

        if (count($this->module->moduleTabs) == 1) {
            return $this->module->moduleGrandParentTab;
        }

        return $this->module->configsAdminController . 'Parent';
    }

    public function getWordingDomain()
    {
        return 'Module.' . $this->module->name . 'Admin';
    }
}