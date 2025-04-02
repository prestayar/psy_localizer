<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
namespace PrestaSDK\Utility;

class HelperModules
{
    /**
     * Get Mobile Customer (Module psy_smartlogin)
     *
     * @param int $id_customer
     * @param bool $onlyValid
     *
     * @return bool|string
     */
    public function getMobileCustomer($id_customer, $onlyValid = true)
    {
        if (empty($id_customer)) {
            return false;
        }

        if (\Module::isEnabled('psy_smartlogin')) {
            $smartlogin = \Module::getInstanceByName('psy_smartlogin');
            $mobile = $smartlogin->getMobileCustomer($id_customer, $onlyValid);

            if (!empty($mobile)) {
                return $mobile;
            }
        }

        return false;
    }
}
