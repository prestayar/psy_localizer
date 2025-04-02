# PrestaShop Localizer

A comprehensive localization solution for PrestaShop specifically designed for the Persian language and Iranian market.

## Overview

PrestaShop Localizer (psy_localizer) is a module that adapts PrestaShop for Persian-speaking users and the Iranian market. It provides various localization features to enhance the user experience for both customers and store administrators.

## Features

- **Jalali Calendar**: Option to use the Persian (Jalali) calendar system
- **Persian Fonts**: Integration of 5 different Persian fonts for the back office
- **RTL Support**: Fixes issues with Right-to-Left display in PrestaShop
- **Advanced Text Editor**: Enhances the default TinyMCE editor with additional features optimized for Persian content
- **Currency Adaptation**: Adds Toman currency and fixes Rial settings for the Iranian market
- **Character Conversion**: Converts Arabic letters to Persian equivalents
- **Number Conversion**: Converts English and Arabic numerals to Persian numerals

## Requirements

- PrestaShop 8.1.0 or higher
- PHP 7.4 or higher

## Installation

1. Download the zip file from the [GitHub Releases](https://github.com/prestayar/psy_localizer/releases) page
2. Go to the Modules page in your PrestaShop back office
3. Click on "Upload a module" button at the top
4. Select the downloaded zip file
5. The module will be automatically uploaded and installed

## Configuration

After installation, you can configure the module by going to: (Configure > Localizer)

### Available Settings

- **Native Active**: Enable/disable native localization features
- **Jalali Date**: Enable/disable Persian calendar system
- **Backoffice Font**: Choose from 5 different Persian fonts for the admin panel
- **TinyMCE Support**: Enable/disable enhanced Persian support in TinyMCE editor

## Developer Guide

### Custom Date Display

Developers can display dates in Jalali (Persian) or Gregorian calendar with custom formatting using the following methods:

#### Using in PHP

```php
// Method 1: Using the module instance
Module::getInstanceByName('psy_localizer')->displayDateCustom($dateTime, $format, $gregorian);

// Method 2: Using Tools class
Tools::displayDateCustom($dateTime, $format, $gregorian);
```

#### Using in Smarty Templates

```smarty
{Tools::displayDateCustom($dateTime, $format, $gregorian)}
```

#### Parameters
- $dateTime : The date string (e.g., "2025-02-11 15:30:00")
- $format : Date format string (e.g., "Y-m-d H:i:s")
- $gregorian : Calendar type; 1 for Gregorian calendar, 0 for Jalali (Persian) calendar

#### Examples
```php
// Display current date in Jalali format
echo Tools::displayDateCustom(date('Y-m-d H:i:s'), 'Y/m/d', 0);
// Output example: 1403/11/22

// Display a specific date in Gregorian format
echo Tools::displayDateCustom('2025-02-11 15:30:00', 'd F Y H:i', 1);
// Output example: 11 February 2025 15:30
```

## Support

For support, bug reports, or feature requests, please contact:
- Website: [https://prestayar.com](https://prestayar.com)
- Email: support@prestayar.com

## License
This module is released under the GNU General Public License v3.0 (GPL-3.0) .
Copyright © 2025 PrestaYar Team