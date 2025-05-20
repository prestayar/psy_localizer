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

namespace PrestaSDK\V040\Install;

use PrestaShopBundle\Install\SqlLoader;

class TablesInstaller
{
    private \Module $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    /**
     * Module's installation entry point.
     *
     * @return bool
     */
    public function installTables(): bool
    {
        if (!$this->executeSqlFromFile($this->module->pathFileSqlInstall)) {
            return false;
        }

        return true;
    }

    public function uninstallTables(): bool
    {
        if (!$this->executeSqlFromFile($this->module->pathFileSqlUninstall)) {
            return false;
        }

        return true;
    }

    private function executeSqlFromFile(string $filepath): bool
    {
        if (!file_exists($filepath)) {
            return true;
        }

        $allowedCollations = ['utf8mb4_general_ci', 'utf8mb4_unicode_ci'];
        $databaseCollation = \Db::getInstance()->getValue('SELECT @@collation_database');
        $sqlLoader = new SqlLoader();
        $sqlLoader->setMetaData([
            'PREFIX_' => _DB_PREFIX_,
            'ENGINE_TYPE' => _MYSQL_ENGINE_,
            'COLLATION' => (empty($databaseCollation) || !in_array($databaseCollation, $allowedCollations)) ? '' : 'COLLATE ' . $databaseCollation,
        ]);

        return $sqlLoader->parseFile($filepath);
    }
}
