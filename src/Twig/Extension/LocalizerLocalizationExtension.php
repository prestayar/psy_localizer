<?php

declare(strict_types=1);

namespace PrestaYar\Localizer\Twig\Extension;

use DateTime;
use DateTimeInterface;
use PrestaShopBundle\Twig\Extension\LocalizationExtension;

class LocalizerLocalizationExtension extends LocalizationExtension
{
    public function dateFormatFull($date): string
    {
        if (!$date instanceof DateTimeInterface) {
            $date = new DateTime($date);
        }

        return \Tools::displayDate($date->format('Y-m-d H:i:s'), true);
    }
}
