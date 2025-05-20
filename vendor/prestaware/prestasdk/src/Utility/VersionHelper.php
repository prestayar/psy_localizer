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

class VersionHelper
{
    public static function getSDKVersion()
    {
        // Path to composer.json in the vendor directory
        $composerFilePath = __DIR__ . '/../../composer.json';

        if (!file_exists($composerFilePath)) {
            return 'unknown'; // Return a fallback if the file doesn't exist
        }

        // Read and decode the JSON file
        $composerData = json_decode(file_get_contents($composerFilePath), true);

        // Return the version field or 'unknown' if not set
        return $composerData['version'] ?? 'unknown';
    }
}
