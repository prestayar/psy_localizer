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

namespace PrestaYar\Localizer\Controller;

use PrestaShopBundle\Controller\Admin\SpecificPriceController;
use SpecificPrice;

class LocalizerSpecificPriceController extends SpecificPriceController
{
    public function listAction($idProduct)
    {
        $translator = $this->get('translator');
        $response = parent::listAction($idProduct);

        $localizer = \Module::getInstanceByName('psy_localizer');

        $data = json_decode($response->getContent(), true);
        foreach ($data as &$item) {
            $specific_price = new SpecificPrice($item['id_specific_price']);

            if ($specific_price->from == '0000-00-00 00:00:00' && $specific_price->to == '0000-00-00 00:00:00') {
                $period = $translator->trans('Unlimited', [], 'Admin.Global');
            } else {
                $period = '';
                if ($specific_price->from != '0000-00-00 00:00:00') {
                    $period .= $translator->trans('From', [], 'Admin.Global') . ' ';
                    $period .= $localizer->getJalaliDate($specific_price->from);
                }

                if ($specific_price->to != '0000-00-00 00:00:00') {
                    $period .= '<br />';
                    $period .= $translator->trans('to', [], 'Admin.Global') . ' ';
                    $period .= $localizer->getJalaliDate($specific_price->to);
                }
            }

            $item['period'] = $period;
        }

        $response->setData($data);

        return $response;
    }
}