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

namespace PrestaSDK\V071\Controller;

use PrestaSDK\V071\Utility\AssetPublisher;
use PrestaSDK\V071\Utility\PanelCore as PanelCoreTrait;
use PrestaSDK\V071\Utility\VersionHelper;

abstract class AdminController extends \ModuleAdminController
{
    use PanelCoreTrait {
        PanelCoreTrait::initSDKPanel as protected traitInitSDKPanel;
    }

    /**
     * Cached orientation that will be injected into templates.
     *
     * @var string|null
     */
    protected $resolvedSidebarOrientation = null;

    public $model = null;

    public $formSectionName = NULL;
	public $listSectionName = NULL;
	public $viewSectionName = NULL;

    /*
    *  Constructor
    */
    public function __construct()
    {
        parent::__construct();

        if (!empty($this->className)) {
            $this->model = new $this->className();

            $def = \ObjectModel::getDefinition($this->className);
            $this->lang = !empty($def['multilang']);
        }

        if (!empty($this->model)) {
            $this->bootstrap = true;
            $this->table = $this->model::TABLE;
            $this->identifier = $this->model::ID;
        }
    }

    public function initShopAdmin()
    {
        if (!empty($this->model) && !empty($this->model->getIdShopColumn())) {
            if (\Shop::isFeatureActive()) {
                if (\Context::getContext()->shop->getContext() != \Shop::CONTEXT_SHOP) {
                    $this->fields_list['shop_name'] = [
                        'title' => $this->module->l('shop', 'admincontroller'),
                        'align' => 'center',
                    ];

                    if (!empty($this->_select)) {
                        $this->_select .= ',shop.name as shop_name ';
                    } else {
                        $this->_select = 'shop.name as shop_name ';
                    }

                    $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON a.`' . $model->getIdShopColumn() . '` = shop.`id_shop`';
                } else {
                    $this->_where .= ' AND ' . $model->getIdShopColumn() . ' = ' . $this->module->getThisIdShop();
                }
            } elseif (!\Shop::isFeatureActive()) {
                $this->_where .= ' AND ' . $model->getIdShopColumn() . ' = ' . $this->module->getThisIdShop();
            }
        }
    }

    public function initAdminPanel()
    {
        $this->initSidebarPanel();

        $this->initSDKPanel();
        return $this->renderPanelTemplate('layouts/' . $this->panelLayout);
    }

    public function initSDKPanel()
    {
        $this->pushPanelVar('sidebar_orientation', $this->getSidebarOrientation());

        $this->traitInitSDKPanel();
    }

    public function initSidebarPanel()
    {
        $orientation = $this->getSidebarOrientation();

        $toggleTemplatePath = $this->getPanelTemplatePath('_partials/sidebar-orientation-toggle.tpl');
        if (!is_file($toggleTemplatePath)) {
            $toggleTemplatePath = null;
        }

        $sidebarVars = [
            'menuItems' => $this->getMenuItems(),
            'active_section' => $this->module->getRequestSection(),
            'module' => $this->module,
            'controller' => \Tools::getValue('controller'),
            'sidebar_orientation' => $orientation,
            'sidebar_toggle_label' => $this->module->l('Switch menu layout', 'admincontroller'),
            'sidebar_toggle_switch_to_horizontal_label' => $this->module->l('Switch to horizontal menu', 'admincontroller'),
            'sidebar_toggle_switch_to_vertical_label' => $this->module->l('Switch to vertical menu', 'admincontroller'),
            'sidebar_toggle_template' => $toggleTemplatePath,
        ];

        $sideMenu = $this->renderPanelTemplate('_partials/sidebar.tpl', $sidebarVars);
        $this->appendToPanel('Sidebar', $sideMenu);
    }

    public function getMenuItems()
    {
        return [];
    }

    public function setSidebarOrientation(string $orientation): void
    {
        $this->resolvedSidebarOrientation = $this->normalizeSidebarOrientation($orientation);
    }

    protected function getSidebarOrientation(): string
    {
        if ($this->resolvedSidebarOrientation !== null) {
            return $this->resolvedSidebarOrientation;
        }

        if (property_exists($this, 'sidebarOrientation')) {
            $this->resolvedSidebarOrientation = $this->normalizeSidebarOrientation($this->sidebarOrientation);

            return $this->resolvedSidebarOrientation;
        }

        $this->resolvedSidebarOrientation = 'horizontal';

        return $this->resolvedSidebarOrientation;
    }

    protected function normalizeSidebarOrientation($orientation): string
    {
        $orientation = strtolower(trim((string) $orientation));

        return in_array($orientation, ['horizontal', 'vertical'], true) ? $orientation : 'horizontal';
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        // Use PrestaShop cache to store the current SDK version temporarily
        $cacheKey = 'PRESTASDK_VERSION_' . $this->module->name;
        $cachedVersion = \Cache::retrieve($cacheKey);

        if (!$cachedVersion) {
            $cachedVersion = \Configuration::get('PRESTASDK_VERSION_' . $this->module->name);
            \Cache::store($cacheKey, $cachedVersion);
        }

        $sdkVersion = VersionHelper::getSDKVersion();
        if ($cachedVersion !== $sdkVersion) {
            // Update the version in Configuration and Cache
            \Configuration::updateValue('PRESTASDK_VERSION_' . $this->module->name, $sdkVersion);
            \Cache::store($cacheKey, $sdkVersion);

            // Republish assets
            AssetPublisher::publishAssets($this->module->name);
        }

        // Add CSS and JS files with versioning
        $this->addCSS($this->module->getPathUri() . 'views/css/prestasdk.css');
        $this->addJS($this->module->getPathUri() . 'views/js/prestasdk.js?v=' . $sdkVersion);
    }

    public function getRenderList()
    {
        return parent::renderList();
    }

    public function getRenderForm()
    {
        $model = false;
        if (!empty($this->className)) {
            $model = new $this->className();
        }

        if (empty($this->display) && (empty($model) || !empty($model->getIdShopColumn())) && \Shop::isFeatureActive() && \Context::getContext()->shop->getContext() != \Shop::CONTEXT_SHOP) {
            return $this->module->displayWarning($this->module->l('To add a new item in multi-shop mode, you must select the shop management section you want from the top and left. ', 'admincontroller'));
        }

        return parent::renderForm();
    }

    public function renderList()
    {
        if ($this->listSectionName && !\Tools::getValue($this->module->sectionQueryKey)) {
            $this->module->sectionForce = $this->listSectionName;
        }

        return $this->initAdminPanel();
    }

    public function renderForm()
    {
        if ($this->formSectionName) {
            $this->module->sectionForce = $this->formSectionName;
        }

        return $this->initAdminPanel();
    }

    public function renderView()
    {
        if ($this->viewSectionName) {
            $this->module->sectionForce = $this->viewSectionName;
        }

        return $this->initAdminPanel();
    }

    protected function displayError($msg)
    {
        return $this->module->displayError($msg);
    }

    protected function displayConfirmation($msg)
    {
        return $this->module->displayConfirmation($msg);
    } 

    protected function getFromConfigs(string $string)
    {
        return $this->module->getFromConfigs($string);
    }

    public function displayListAction($params) {
        return $this->renderPanelTemplate('_partials/helpers/list/list_action.tpl', $params);
    }
}