<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
namespace PrestaSDK\V040\Utility;

trait PanelCore
{
    protected string $sdkContent = '';
    protected array $sdkVars = [];
    protected array $sdkPositions = [];

    public $middlewaresACL = [];
	public $runnableRequests = [];

    public $panelPath = null;
    public $panelLayout = 'layout.tpl';
    public $prestaSDKPath = 'vendor/prestaware/prestasdk/src';

    public function initSDKPanel()
    {
        $vars = [
            '_content' => $this->requestRunner(),
            '_positions' => $this->releasePositions(),
            '_flash' => HelperMethods::getFlashMessage(),

            'controller' => \Tools::getValue('controller'),
			'section' => $this->module->getRequestSection(),

            'panel_path' => $this->getPanelPath(),
            'panel_layout' => $this->panelLayout,
            'panel_url' => $this->getPanelUrl(),

            'module_logo_src' => $this->module->getModuleUrl() . 'logo.png',
            'module_displayName' => $this->module->displayName,
            'module_name' => $this->module->name,
            'module_version' => $this->module->version,
            'module_content' => '',

            'bootstrap' => true,
            'errors' => $this->errors,

            'status_update' => 'info',
            'tooltip_message' => '',

            //'menu_items' => $this->getMenuItems(),
        ];

        $this->sdkVars = array_merge($vars, $this->sdkVars);

        $this->context->smarty->assign($this->sdkVars);
    }

    public function requestRunner()
    {
        if ($return = $this->requestManager()) {
            return $return;
        }

        return $this->{$this->runNext()}();
    }

    /**
    * run next method name
    *
    * @return string return Method Name call
    */
	public function runNext()
	{
		/** set runnableRequests & reversed in requestManager() */
        $next = array_pop($this->runnableRequests);

        /** if not found any request call fireContent() for return content */
        if ($next === null) {
            $next = 'fireContent';
        }

        return $next;
    }

    /**
    * last method for return all content data
    *
    * @return string
    */
	public function fireContent() : string
    {
        return $this->sdkContent;
    }

    /**
    * Manage & Build Runnable Requests
    *
    * @return string|null
    */
    public function requestManager()
    {
        $controllerReq = strtolower(\Tools::getValue('controller'));
        $sectionReq = strtolower($this->module->getRequestSection());
        $rawRequests = [];
        $requestLine = [];

        foreach ($this->middlewaresACL as $case => $list) {
            if (!in_array($case, ['before', 'after', 'ignore'])) {
                continue;
            }

            foreach ($list as $schema => $middlewares) {
                $schema = strtolower(preg_replace('/\s+/', '', $schema));

                if (!isset($rawRequests[$case])) {
                    $rawRequests[$case] = [];
                }

                if ($schema == '*') {
                    $rawRequests[$case] = array_merge($rawRequests[$case], $middlewares);
                }

                if ($schema == '*@' . $controllerReq) {
                    $rawRequests[$case] = array_merge($rawRequests[$case], $middlewares);
                }

                if ($schema == $sectionReq . '@' . $controllerReq) {
                    $rawRequests[$case] = array_merge($rawRequests[$case], $middlewares);
                }
            }
        }

        if (!empty($rawRequests['before'])) {
            $requestLine = array_merge($requestLine, $rawRequests['before']);
        }

        $requestLine[] = 'MainContent';

        if (!empty($rawRequests['after'])) {
            $requestLine = array_merge($requestLine, $rawRequests['after']);
        }

        if (!empty($rawRequests['ignore'])) {
            $requestLine = array_diff($requestLine, $rawRequests['ignore']);
        }

        $requestList = array_unique($requestLine);

        foreach ($requestList as $key => &$request) {
            $middlewareName = $request;
            $request = 'middleware' . ucfirst($request);

            if (!method_exists($this, $request)) {
                if (isset($this->middlewaresACL['optional']) && in_array($middlewareName, $this->middlewaresACL['optional'])) {
                    $this->module->sdkDebug[] = "<b>$request</b> method not found. (Optional)";
                } else {
                    $this->module->sdkDebug[] = "ERROR: <b>$request</b> method not found. (Required)";
                    return $this->_middlewareNotFound($request);
                }

                unset($requestList[$key]);
            }
        }

        $this->runnableRequests = array_reverse($requestList);
    }

        /**
    * for push MainContent ( main middleware )
    *
    * @return string   Call next method
    */
	public function middlewareMainContent()
    {
        $sectionRequest = $this->module->getRequestSection();
        $sectionMethod = $this->module->sectionQueryKey . ucfirst($sectionRequest);

		/**  calling before-section-after Methods if section method is Exist */
		if (method_exists($this,$sectionMethod)) {
            $_SectionMethod = ucfirst($sectionMethod);

            $beforeMethod = 'before' . $_SectionMethod;
            if (method_exists($this, $beforeMethod)) {
                $this->pushMainContent($this->{$beforeMethod}());
            }

            $this->pushMainContent($this->{$sectionMethod}());

            $afterMethod = 'after' . $_SectionMethod;
            if (method_exists($this, $afterMethod)) {
                $this->pushMainContent($this->{$afterMethod}());
            }
        } else {
            $this->pushMainContent($this->_methodNotFound($sectionMethod));
        }

        return $this->{$this->runNext()}();
    }

    public function _methodNotFound($methodName): string
    {
        return "<div class='alert alert-danger'> class method <b>$methodName<b> Not Found.</div>";
    }

    public function _middlewareNotFound($middlewareName): string
    {
        return "<div class='alert alert-danger'> Error: class method <b>$middlewareName</b> Not Found !</div>";
    }

    /**
    * Append Content To Positions
    *
    * @return string
    */
    public function appendToPanel($posName, $value, $order = 10, $key = null): void
    {
        if (!is_string($value) || empty($value)) {
            return;
        }

        $newContent = [
            'value' => $value,
            'order' => is_int($order) ? $order : 10,
            'key' => !empty($key) ? (string) $key : uniqid(),
        ];

        if (!isset($this->sdkPositions[$posName]) || !is_array($this->sdkPositions[$posName])) {
            $this->sdkPositions[$posName] = [];
        }

        foreach ($this->sdkPositions[$posName] as $oldContentKey => $oldContent) {
            if ($oldContent['key'] == $newContent['key']) {
                unset($this->sdkPositions[$posName][$oldContentKey]);
                break;
            }
        }

        $this->sdkPositions[$posName][] = $newContent;
    }

    /**
    * Append Contents By Array To Panel
    *
    * @return string
    */
	public function appendToPanelByArray(array $dataArray)
    {
        foreach ($dataArray as $posName => $posData) {
            if (empty($posData)) {
                continue;
            }

            if (is_string($posData)) {
                $this->appendToPanel($posName, $posData);
            } elseif (is_array($posData)) {
                $value = $posData['value'] ?? null;
                $order = $posData['order'] ?? null;
                $key = $posData['key'] ?? null;

                $this->appendToPanel($posName, $value, $order, $key);
            }
        }
    }

    /**
    * Sorted And Ready Positions for RELEASE
    *
    * @return string
    */
	public function releasePositions()
    {
        $mergedArray = [];

        foreach ($this->sdkPositions as $posName => &$allAppended) {
            usort($allAppended, fn($p1, $p2) => $p1['order'] - $p2['order']);

            $posMerge = '';
            foreach ($allAppended as $contentArray) {
                $posMerge .= $contentArray['value'];
            }

            $mergedArray[$posName] = $posMerge;
        }

        return $mergedArray;
    }

    /**
    * set Middlewares Access Control Lists
    *
    * @return void
    */
    public function setMiddlewares(array $middlewares): void
    {
        $this->middlewaresACL = $middlewares;
    }

    /**
    * helper method for remove middleware Request dynamically 
    *
    * @return void
    */
	public function removeMiddleware($middlewareName)
    {
        if (($key = array_search($middlewareName, $this->runnableRequests)) !== false) {
            unset($this->runnableRequests[$key]);
        }
    }

    public function putMiddleware($newMiddleware, $addType = 'next', $existMiddleware = null): void
    {
        // Implementation for adding middleware dynamically
    }

    public function pushMainContent($newContent): void
    {
        if (is_string($newContent)) {
            $this->sdkContent .= $newContent;
        } else {
            $this->module->sdkDebug[] = 'Content must be a string in <b>' . debug_backtrace()[1]['function'] . '</b>';
        }
    }

    public function pushPanelVar($name, $value): void
    {
        $this->sdkVars[$name] = $value;
    }

    public function pushPanelVars(array $vars): void
    {
        $this->sdkVars = array_merge($this->sdkVars, $vars);
    }

    public function setPanelPath(string $path): void
    {
        $this->panelPath = (substr($path, 0, 1) == '/') ? substr($path, 1) : $path;
    }

    public function getPanelPath(): string
    {
        //$basePathLayout = '@Modules/' . $this->module->name . '/';
        $basePathLayout = $this->module->getModulePath();
        $path = empty($this->panelPath) ? $basePathLayout . $this->prestaSDKPath . '/Resources/views' : $basePathLayout . 'views/templates/' . $this->panelPath;

        return (substr($path, -1) == '/') ? $path : $path . '/';
    }

    public function getPanelUrl(): string
    {
        $path = $this->module->getModuleUrl() . $this->panelPath;
        return (substr($path, -1) == '/') ? $path : $path . '/';
    }

    public function renderPanelTemplate($tpl, array $vars = []): string
    {
        // todo :: check => $vars['_positions'] = $this->releasePositions();
        return $this->module->fetchTemplate($this->getPanelTemplatePath($tpl), $vars);
    }

    public function getPanelTemplatePath($tplPath): string
    {
        $tplPath = ltrim($tplPath, '\/');
        return $this->getPanelPath() . $tplPath;
    }
}