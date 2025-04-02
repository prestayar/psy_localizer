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

use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

trait UseDate
{
    /**
     * Overrile Tools::displayDate
     *
     * @param $date
     * @param bool $full
     * @return mixed|string
     * @throws \PrestaShopException
     */
    public function displayDate($date, bool $full = false): mixed
    {
        if (!$date || !($time = strtotime($date))) {
            return $date;
        }

        if ($date == '0000-00-00 00:00:00' || $date == '0000-00-00') {
            return '';
        }

        if (!\Validate::isDate($date) || !\Validate::isBool($full)) {
            throw new \PrestaShopException('Invalid date');
        }

        $context = \Context::getContext();
        $date_format = ($full ? $context->language->date_format_full : $context->language->date_format_lite);


        if ($this->isConvertDate()) {
            return self::getJalaliDate($date, $date_format);
        }

        return date($date_format, $time);
    }

    /**
     * Display datetime based on custom format
     * Module::getInstanceByName('psy_localizer')->getJalaliDate($dateTime, $format);
     *
     * @param $dateTime
     * @param null $format
     * @return mixed|string
     */
    public static function getJalaliDate($dateTime, $format = null): mixed
    {
        $format = !empty($format)? $format : \Context::getContext()->language->date_format_full;

        if (empty($dateTime)) {
            $time = Carbon::now();
        } else {
            $time = strtotime($dateTime);
            if (empty($time) || $time < 0) {
                return $dateTime;
            }
        }

        // 3000 year jalali
        if ($time < '32503667400') {
            return Jalalian::forge($time)->format($format);
        }

        return date($format, $time);
    }

    /**
     * Display datetime based on custom format (jalali or gregorian)
     * Module::getInstanceByName('psy_localizer')->displayDateCustom($dateTime, $format, $gregorian);
     * {Tools::displayDateCustom($dateTime,$format,$gregorian)}
     *
     * @param $date
     * @param string $format
     * @param bool $gregorian
     * @return mixed|string
     */
    public function displayDateCustom($date, string $format = 'd F Y', bool $gregorian = false): mixed
    {
        if (!empty($gregorian)) {
            return date($format, strtotime($date));
        }

        return self::getJalaliDate($date, $format);
    }
}