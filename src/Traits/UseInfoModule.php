<?php
/**
 * Prestashop localizer
 * Comprehensive localization of Prestashop specifically tailored for the Persian language and the Iranian market.
 *
 * @author Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright (c) 2025 - PrestaYar Team
 * @website https://prestayar.com
 */
declare(strict_types=1);

namespace PrestaYar\Localizer\Traits;

trait UseInfoModule
{
    public static array $webServiceResponse = [];
    public static bool $newVersionReleased = false;

    public function getModuleInfo(): mixed
    {
        $servers = [
            'https://ws.prestayar.com/v4/'
        ];

        $serverId = 0;

        do {
            if (!isset($servers[$serverId])) {
                continue;
            }
            $url = $this->getUrlServer($servers[$serverId]);

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            if ($error_num = curl_errno($curl)) {
                $error_curl = curl_error($curl) . '::#' . $error_num;
            }
            curl_close($curl);
            $serverId++;
        } while ($result === false && $serverId < 2);

        if (empty($result)) {
            return false;
        }

        return json_decode($result, true);
    }

    public function getUrlServer(string $server): string
    {
        $url = $server . 'product/info';
        $url .= '?product=localizer';
        return $url . '&version=' . $this->module->version;
    }

    public function setWebserviceInfo(): void
    {
        $wsData = static::$webServiceResponse;
        if (isset($wsData['product_info']['latest_version'])) {
            $hasNewVersion = version_compare($this->module->version, $wsData['product_info']['latest_version'], '<');
            if (empty($hasNewVersion)) {
                $this->pushPanelVar('status_update', 'success');
            } else {
                $this->pushPanelVar('status_update', 'warning');
                // todo "نسخه جدید ماژول منتشر شده است ، لطفا بروز رسانی کنید!"
                $this->pushPanelVar('tooltip_message', $this->l('A new version of the module has been released, please update!', 'useinfomodule'));
            }
        }

        $headerInfoContent = $this->module->renderModuleTemplate('admin/info.tpl', [
            'title' => $this->module->name,
            'webServiceInfo' => $wsData['product_info'],
        ], true);

        $this->appendToPanel('Header', $headerInfoContent);
    }
}