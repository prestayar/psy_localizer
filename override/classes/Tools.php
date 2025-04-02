<?php

class Tools extends ToolsCore
{
    public static function displayDate($date, $full = false)
    {
        if (Module::isEnabled('psy_localizer')) {
            $localizer = Module::getInstanceByName('psy_localizer');
            return $localizer->displayDate($date, $full);
        }

        return parent::displayDate($date, $full);
    }

    public static function displayDateCustom($date, $format = 'd F Y', $gregorian = false)
    {
        if (Module::isEnabled('psy_localizer')) {
            $localizer = Module::getInstanceByName('psy_localizer');
            return $localizer->displayDateCustom($date, $format, $gregorian);
        }

        return Tools::displayDate($date);
    }

    public static function getAllValues()
    {
        $values = parent::getAllValues();
        if (!empty($values)) {
            foreach ($values as $key => $value) {
                if (is_string($value)) {
                    $values[$key] = self::convertChars($value);
                }
            }
        }
        return $values;
    }

    public static function getValue($key, $default_value = false)
    {
        $value = parent::getValue($key, $default_value);

        if (is_string($value)) {
            return self::convertChars($value);
        }

        return $value;
    }

    public static function convertChars(string $srting): string
    {
        $en_chars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'ک', 'ی'];
        $fa_chars = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', 'ك', 'ي'];
        $ar_chars = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', 'ك', 'ي'];
        
        return str_replace($fa_chars + $ar_chars, $en_chars + $en_chars, $srting);
    }
}