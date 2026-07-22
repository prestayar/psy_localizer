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

use PrestaShopBundle\Controller\Admin\Sell\Catalog\Product\SpecificPriceController;
use SpecificPrice;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LocalizerSpecificPriceController extends SpecificPriceController
{
    public function listAction(Request $request, int $productId): JsonResponse
    {
        $response = parent::listAction($request, $productId);
        $data = $response->getData(true);

        $localizer = \Module::getInstanceByName('psy_localizer');
        if (!is_array($data) || !isset($data['specificPrices']) || !is_array($data['specificPrices']) || !$localizer) {
            return $response;
        }

        foreach ($data['specificPrices'] as &$item) {
            if (!is_array($item) || empty($item['id'])) {
                continue;
            }

            $specificPrice = new SpecificPrice((int) $item['id']);

            if ($specificPrice->from === '0000-00-00 00:00:00' && $specificPrice->to === '0000-00-00 00:00:00') {
                $period = $this->trans('Unlimited', [], 'Admin.Global');
            } else {
                $period = '';
                if ($specificPrice->from !== '0000-00-00 00:00:00') {
                    $period .= $this->trans('From', [], 'Admin.Global') . ' ';
                    $period .= $localizer->getJalaliDate($specificPrice->from);
                }

                if ($specificPrice->to !== '0000-00-00 00:00:00') {
                    $period .= '<br />';
                    $period .= $this->trans('to', [], 'Admin.Global') . ' ';
                    $period .= $localizer->getJalaliDate($specificPrice->to);
                }
            }

            $item['period'] = $period;
        }
        unset($item);

        $response->setData($data);

        return $response;
    }
}
