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

namespace PrestaYar\Localizer\Native;

use Morilog\Jalali\Jalalian;
use Morilog\Jalali\CalendarUtils;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;

class NativeCorePrestashop
{
    /**
     * The list of changes that must be applied to the core for Prestashop localization.
     *
     * @return array[]
     */
    public static function getCoreChanges(): array
    {
        return [
            // To display Jalali date in Twig
            [
                'title' => 'جهت اعمال تاریخ جلالی',
                'path' => 'src/PrestaShopBundle/Twig/Extension/LocalizationExtension.php',
                'replaces' => [
                    'return $date->format($this->dateFormatFull);' => 'return \Tools::displayDate($date->format(\'Y-m-d H:i:s\'), true); #localizer#',
                    'return $date->format($this->dateFormatLight);' => 'return \Tools::displayDate($date->format(\'Y-m-d\')); #localizer#',
                ]
            ],
            [
                'title' => 'جهت اعمال تاریخ جلالی',
                'path' => 'src/Adapter/Cart/QueryHandler/GetCartForViewingHandler.php',
                'replaces' => [
                    '(new DateTime($customer->date_add))->format($context->language->date_format_lite),' => '\Tools::displayDate($customer->date_add), #localizer#',
                    '(new DateTime($order->date_add))->format($context->language->date_format_lite),' => '\Tools::displayDate($order->date_add), #localizer#',

                ]
            ],

            // To activate the advanced text editor
            [
                'title' => 'جهت فعال سازی ویرایشگر متن پیشرفته',
                'path' => 'js/tiny_mce/tiny_mce.js',
                'replaces' => [
                    "var path_array = baseAdminDir.split('/');" =>
                        "if (typeof Localizer_TinyMCE == 'undefined') { var path_array = baseAdminDir.split('/'); // #localizer#",
                    "$.getScript(final_path+'/js/tiny_mce/tinymce.min.js');" =>
                        "$.getScript(final_path+'/js/tiny_mce/tinymce.min.js');}// #localizer# change psy_localizer",
                ]
            ],
            [
                'title' => 'جهت فعال سازی ویرایشگر متن پیشرفته',
                'path' => 'js/admin/tinymce.inc.js',
                'replaces' => [
                    "function tinySetup(config) {" =>
                        "if (typeof Localizer_TinyMCE == 'undefined') { function tinySetup(config) { // #localizer#",
                    "tinyMCE.init(config);" =>
                        "tinyMCE.init(config);}// #localizer# change psy_localizer",
                ]
            ]
        ];
    }

    /**
     * Make changes to core files
     *
     * @return bool
     */
    public static function changeFiles(): bool
    {
        $files = self::getCoreChanges();
        foreach ($files as $file) {
            $filePath = _PS_ROOT_DIR_ . '/' . $file['path'];

            if (!file_exists($filePath)) {
                continue;
            }

            $fileData = self::fileRead($filePath);
            if (strpos($fileData, "#manager#")) {
                continue;
            }

            if (strpos($fileData, "#localizer#")) {
                continue;
            }

            if (!file_exists($filePath.'.backup')) {
                self::fileWriter($filePath.'.backup', $fileData);
            }

            foreach ($file['replaces'] as $old => $new) {
                $fileData = str_replace($old, $new, $fileData);
            }

            self::fileWriter($filePath, $fileData);
        }

        return true;
    }

    /**
     * Checking the changes in the core
     *
     * @return bool
     */
    public static function checkFiles(): bool
    {
        $files = self::getCoreChanges();
        foreach ($files as $file) {
            $filePath = _PS_ROOT_DIR_ . '/' . $file['path'];
            if (!file_exists($filePath)) {
                return false;
            }

            $fileOpen = fopen($filePath, 'r');
            $fileData = fread($fileOpen,filesize($filePath));
            fclose($fileOpen);

            if (!strpos($fileData, "#manager#") && !strpos($fileData, "#localizer#")) {
                return false;
            }
        }

        return true;
    }

    public static function fixCurrency(): bool|int
    {
        $lang_id = \Language::getIdByIso('fa');
        if (empty($lang_id)) {
            return -10;
        }

        self::editXmlForDisplayFormat();

        $currencyId = \Currency::getIdByIsoCode('IRT');
        if (!empty($currencyId)) {
            $currencyToman = new \Currency($currencyId, $lang_id);

            $currencyToman->name = 'تومان ایران';
            $currencyToman->symbol = 'تومان';
            $currencyToman->pattern = '#,##0.00 ¤';

            if (empty($currencyToman->save())) {
                return -12;
            }

            return true;
        }

        $new = new \Currency();
        foreach (\Language::getLanguages() as $lang){
            if ($lang['iso_code'] == 'fa') {
                $new->name[$lang['id_lang']] = 'تومان ایران';
                $new->symbol[$lang['id_lang']] = 'تومان';
            } else {
                $new->name[$lang['id_lang']] = 'Iranian Toman';
                $new->symbol[$lang['id_lang']] = 'IRT';
            }
            $new->pattern = '#,##0.00 ¤';
        }

        $new->iso_code = 'IRT';
        $new->numeric_iso_code = 365;
        $new->precision = 0;
        $new->conversion_rate = 1.0;
        $new->deleted = 0;
        $new->active = 1;

        if (empty($new->add())) {
            return -11;
        }

        return true;
    }

    public static function editXmlForDisplayFormat(): bool
    {
        if (!function_exists('simplexml_load_file')) {
            return false;
        }

        $xmlPath = _PS_ROOT_DIR_. '/localization/CLDR/core/common/main/fa.xml';
        if (file_exists($xmlPath)) {
            $this->generateBackUpFile($xmlPath);
            $xml = simplexml_load_file($xmlPath);

            // Currency Display Format
            $selector = '//ldml/numbers/currencyFormats[@numberSystem="arab"]/currencyFormatLength/currencyFormat[@type="standard"]';

            if ($xml->xpath($selector)[0]->pattern) {
                $xml->xpath($selector)[0]->pattern = '#,##0.00 ¤';
            }

            // Currency Display Format
            $selector = '//ldml/numbers/currencyFormats[@numberSystem="arabext"]/currencyFormatLength/currencyFormat[@type="standard"]';

            if ($xml->xpath($selector)[0]->pattern) {
                $xml->xpath($selector)[0]->pattern = '#,##0.00 ¤';
            }

            $xml->asXML($xmlPath);
        }

        return true;
    }

    public function generateBackUpFile($filePath): bool
    {
        if (!file_exists($filePath.'.backup')) {
            $fileContent = $this->fileRead($filePath);
            $this->fileWriter($filePath.'.backup', $fileContent);
        }

        return true;
    }

    public static function fileWriter($filePath, $fileData): bool
    {
        $fileOpen = fopen($filePath, 'w+');
        fwrite($fileOpen, $fileData);
        fclose($fileOpen);

        return true;
    }

    public static function fileRead($filePath): bool|string
    {
        $fileOpen = fopen($filePath, 'r');
        $fileContent = fread($fileOpen, filesize($filePath));
        fclose($fileOpen);

        return $fileContent;
    }

        /**
     * Convert date for save in the database
     * @param $object
     */
    public function convertDate($object)
    {
        if (!\Configuration::get('Localizer_Native_Active')) {
            return;
        }

        if (!empty(\Configuration::get('Localizer_JalaliDate'))) {
            $class_name = get_class($object);
            $definition = \ObjectModel::getDefinition($class_name);

            if (!empty($definition['fields'])) {
                foreach ($definition['fields'] as  $field => $def)
                {
                    if (isset($def['validate']) && in_array($def['validate'], array('isDate', 'isDateFormat', 'isBirthDate')))
                    {
                        $date = $object->$field;
                        if ($date == '0000-00-00 00:00:00' || $date == '0000-00-00' || !$date) {
                            continue;
                        }

                        $object->$field = $this->getDateGregorian($date);
                    }
                }
            }
        }
    }

    public function getDateGregorian($date)
    {
        $dateArray = $this->getDateArray($date);
        if (empty($dateArray) || count($dateArray) < 3) {
            return $date;
        }

        $jy = $dateArray['year'];
        if (!($jy >= 1900 && $jy <= 2100)){
            $date = CalendarUtils::toGregorian($dateArray['year'], $dateArray['month'], $dateArray['day']);
            $gDate = sprintf("%04d", intval($date[0]) ) . '-' .
                     sprintf("%02d", intval($date[1]) ) . '-' .
                     sprintf("%02d", intval($date[2]) );

            $gTime = isset($dateArray['hour']) ? ' '. $dateArray['hour'].':'.$dateArray['minute'].':'.$dateArray['second'] : '';

            return $gDate . $gTime;
        }

        return $date;
    }

    public function getDateArray($date = null)
    {
        if (empty($date)) {
            return false;
        }

        $dateParts = explode("/", $date);
        if (count($dateParts) > 1) {
            // '2017/07/10
            $dateParts = explode("/", $date);
            return [
                "year"      => sprintf("%04d", intval($dateParts[0])),
                "month"     => sprintf("%02d", intval($dateParts[1])),
                "day"       => sprintf("%02d", intval($dateParts[2]))
            ];
        } else {
            //hour, minute, second, month, day, year
            $dateParts = explode(" ", $date);
            $dataDateParts = explode("-", $dateParts[0]);

            if (count($dateParts) > 1) {
                // '2017-07-10 15:00:00'
                $dataTimeParts = explode(":", $dateParts[1]);
                return [
                    "year"      => sprintf("%04d", intval($dataDateParts[0])),
                    "month"     => sprintf("%02d", intval($dataDateParts[1])),
                    "day"       => sprintf("%02d", intval($dataDateParts[2])),
                    "hour"      => sprintf("%02d", intval($dataTimeParts[0])),
                    "minute"    => sprintf("%02d", intval($dataTimeParts[1])),
                    "second"    => sprintf("%02d", intval($dataTimeParts[2]))
                ];
            } else {
                // '2017-07-10
                return [
                    "year"      => sprintf("%04d", intval($dataDateParts[0])),
                    "month"     => sprintf("%02d", intval($dataDateParts[1])),
                    "day"       => sprintf("%02d", intval($dataDateParts[2]))
                ];
            }
        }
    }

    public function getDataModifiedRecords($data)
    {
        $records = [];
        foreach ($data->getRecords()->all() as $record) {
            foreach ($record as $key => $value) {
                if (!\Validate::isDate($value)) {
                    continue;
                }

                $date = strtotime($value);
                if (!empty($date) && $date > 0) {
                    $record[$key] = Jalalian::forge($date)->format('Y-m-d H:i:s');
                }
            }
            $records[] = $record;
        }

        return new GridData(
            new RecordCollection($records),
            $data->getRecordsTotal(),
            $data->getQuery()
        );
    }

}