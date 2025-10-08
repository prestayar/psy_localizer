<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
namespace PrestaSDK\V071\Utility;

class HelperMethods
{
    /**
     * set prestashop cookie by Name And Data
     */
    public static function setCookie($cookieName, array $data, $expireTime = null)
    {
        $cookie = new \Cookie($cookieName);

        if ($expireTime) {
            $cookie->setExpire($expireTime);
        }

        foreach ($data as $cKey => $cValue) {
            if (isset($cookie->$cKey)) {
                unset($cookie->$cKey);
            }

            $cookie->$cKey = $cValue;
        }

        return $cookie->write();
    }

    /**
     * get prestashop cookie by key Or Keys
     *
     * @param string $cookieName cookie name
     * @param string|array $key_or_keys key OR array of keys
     * @return string|array
     */
    public static function getCookie($cookieName, $key_or_keys, $default = null, $unset = false)
    {
        $cookie = new \Cookie($cookieName);

        if (is_string($key_or_keys)) {
            return isset($cookie->$key_or_keys) ? $cookie->$key_or_keys : $default;
        }

        $values = [];

        foreach ($key_or_keys as $key) {
            if (isset($cookie->$key)) {
                $values[$key] = $cookie->$key;

                if ($unset) {
                    unset($cookie->$key);
                }
            } else {
                $values[$key] = $default;
            }
        }

        return $values;
    }


    public static function setFlashMessage($message, $type = 'info')
    {
        $flashMessage = [
            'message' => $message,
            'type' => $type,
        ];

        return static::setCookie('FlashMessage', $flashMessage);
    }

    public static function getFlashMessage($destroy = true)
    {
        return static::getCookie('FlashMessage', ['message', 'type'], null, $destroy);
    }

    public static function replaces($string, array $replaces)
    {
        foreach ($replaces as $old => $new) {
            $string = str_replace($old, $new, $string);
        }

        return $string;
    }

    public static function uniMerge(array $array1, array $array2)
    {
        return array_unique(array_merge($array1, $array2));
    }

    public function isAssocArray(array $array)
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Generate admin link for cart view
     *
     * @param int $id_cart Cart ID
     * @return string Cart admin link
     */
    public static function getCartLinkAdmin($id_cart)
    {
        if (empty($id_cart)) {
            return '';
        }

        $context = \Context::getContext();
        $link = $context->link;

        $viewLink = $link->getAdminLink('AdminCarts', true, [], [
            'viewcart' => '',
            'id_cart' => $id_cart
        ]);

        // For PrestaShop 1.7.7+ use Symfony routing
        if (\Tools::version_compare(_PS_VERSION_, '1.7.7', '>=')) {
            global $kernel;
            if ($kernel instanceof \Symfony\Component\HttpKernel\HttpKernelInterface) {
                try {
                    $sfRouter = $kernel->getContainer()->get('router');
                    $viewLink = $sfRouter->generate('admin_carts_view', [
                        'cartId' => $id_cart
                    ]);
                } catch (\Exception $e) {
                    // Fallback to legacy link if Symfony routing fails
                }
            }
        }

        return $viewLink;
    }

    /**
     * Generate admin link for order view
     *
     * @param int $id_order Order ID
     * @return string Order admin link
     */
    public static function getOrderLinkAdmin($id_order)
    {
        if (empty($id_order)) {
            return '';
        }

        $context = \Context::getContext();
        $link = $context->link;

        $viewLink = $link->getAdminLink('AdminOrders', true, [], [
            'vieworder' => '',
            'id_order' => $id_order
        ]);

        // For PrestaShop 1.7.7+ use Symfony routing
        if (\Tools::version_compare(_PS_VERSION_, '1.7.7', '>=')) {
            global $kernel;
            if ($kernel instanceof \Symfony\Component\HttpKernel\HttpKernelInterface) {
                try {
                    $sfRouter = $kernel->getContainer()->get('router');
                    $viewLink = $sfRouter->generate('admin_orders_view', [
                        'orderId' => $id_order
                    ]);
                } catch (\Exception $e) {
                    // Fallback to legacy link if Symfony routing fails
                }
            }
        }

        return $viewLink;
    }
}
