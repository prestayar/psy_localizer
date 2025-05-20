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

class AssetPublisher
{
    public static function publishAssets($moduleName)
    {
        // Source paths in the vendor directory
        $sourceCssPath = _PS_MODULE_DIR_ . "$moduleName/vendor/prestaware/prestasdk/assets/css";
        $sourceJsPath = _PS_MODULE_DIR_ . "$moduleName/vendor/prestaware/prestasdk/assets/js";

        // Target paths in the public views directory
        $targetCssPath = _PS_MODULE_DIR_ . "$moduleName/views/css";
        $targetJsPath = _PS_MODULE_DIR_ . "$moduleName/views/js";

        // Copy CSS files
        self::copyFiles($sourceCssPath, $targetCssPath);

        // Copy JS files
        self::copyFiles($sourceJsPath, $targetJsPath);
    }

    private static function copyFiles($sourcePath, $targetPath)
    {
        // Skip if the source directory does not exist
        if (!file_exists($sourcePath)) {
            return;
        }

        // Create the target directory if it does not exist
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        // Copy each file from the source directory to the target directory
        foreach (glob($sourcePath . '/*') as $file) {
            $fileName = basename($file);
            copy($file, $targetPath . '/' . $fileName);
        }
    }
}

