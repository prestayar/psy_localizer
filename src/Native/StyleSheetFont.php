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

class StyleSheetFont
{
    public string $font;
    private \Module $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    public function generate(string $font): bool
    {
        $this->font = $font;

        clearstatcache();

        $fileFontCss = $this->module->getModulePath() .'views/css/admin/localizer-font.css';
        if (file_exists($fileFontCss)) {
            $fd = fopen($fileFontCss, 'w');
        } else {
            $fd = @fopen($fileFontCss, 'x');
        }

        if ($fd) {
            fwrite($fd, $this->getContentCss());
            fclose($fd);
            return true;
        }

        return false;
    }

    private function getFileFont(string $format): string
    {
        $filePath = $this->getFontsPath() . $this->font . '/' . $this->font . $format;
        if (!file_exists($filePath)) {
            return '';
        }

        return $filePath;
    }

    private function getFontsPath(): string
    {
        return $this->module->getModulePath() . 'views/fonts/';
    }

    private function getFontFace(string $fileWoff, string $fileWoff2, bool $isBold = false): string
    {
        if (empty($fileWoff) && empty($fileWoff2)) {
            return '';
        }

        $suffix_bold = '';
        if ($isBold) {
            $suffix_bold = '-bold';
        }

        $contentCss = "@font-face {font-family: {nameFont};src:";
        if (!empty($fileWoff)) {
            $contentCss .= "url('{revertFolder}fonts/{folder}/{folder}$suffix_bold.woff') format('woff')";
        }

        if (!empty($fileWoff2)) {
            if (!empty($fileWoff)) {
                $contentCss .= ",";
            }
            $contentCss .= "url('{revertFolder}fonts/{folder}/{folder}$suffix_bold.woff2') format('woff2')";
        }

        if ($isBold) {
            $contentCss .= ";font-weight: bold}";
        } else {
            $contentCss .= ";font-weight: normal}";
        }

        return $contentCss;
    }

    private function getCssSetFont(): string
    {
        $contentCss = ".bootstrap body.lang-fa, body.lang-fa, .lang-fa #content.bootstrap .panel .panel-heading a.btn, .lang-fa #content.bootstrap #dash_version .panel-heading a.btn, .lang-fa #content.bootstrap .message-item-initial .message-item-initial-body .panel-heading a.btn, .lang-fa #content.bootstrap .timeline .timeline-item .timeline-caption .timeline-panel .panel-heading a.btn, .lang-fa #notification .dropdown-menu .notifications .nav-tabs .nav-item .nav-link, .lang-fa .bootstrap .tooltip, .lang-fa .bootstrap .btn-group-action .btn, .lang-fa .bootstrap .page-head h2.page-title, .lang-fa .bootstrap .page-head h4.page-subtitle, .lang-fa .bootstrap #dashboard section > section header .small, .lang-fa .bootstrap #login-panel #shop_name, .bootstrap #login-panel #reset_name, .bootstrap #login-panel #reset_confirm_name, .bootstrap #login-panel #forgot_name, .bootstrap #login-panel #forgot_confirm_name, .bootstrap h1, .lang-fa .bootstrap h2, .lang-fa .bootstrap h3, .lang-fa .bootstrap h4, .lang-fa .bootstrap h5, .lang-fa .bootstrap h6, .lang-fa .bootstrap .h1, .lang-fa .bootstrap .h2, .lang-fa .bootstrap .h3, .lang-fa .bootstrap .h4, .lang-fa .bootstrap .h5, .lang-fa .bootstrap .h6, .lang-fa #content.bootstrap .panel .panel-heading, .lang-fa #content.bootstrap #dash_version .panel-heading, .lang-fa #content.bootstrap .message-item-initial .message-item-initial-body .panel-heading, .lang-fa #content.bootstrap .timeline .timeline-item .timeline-caption .timeline-panel .panel-heading, .bootstrap .nav-tabs li a, .bootstrap .list-empty .list-empty-msg, .bootstrap #dashboard #dashtrends dt, .bootstrap #dashboard #dashproducts nav, .lang-fa .bootstrap #dashboard .tooltip-panel-heading {font-family:{nameFont},sans-serif}";
        $contentCss .= ".bootstrap h1, .bootstrap h2, .bootstrap h3, .bootstrap h4, .bootstrap h5, .bootstrap h6, .bootstrap .h1, .bootstrap .h2, .bootstrap .h3, .bootstrap .h4, .bootstrap .h5, .bootstrap .h6,.h1,.h2,.h3,.h4,.h5,.h6,.module-modal-title>h4,.module-search-result-wording,.onboarding .panel .onboarding-intro h3,.popover,.pstaggerTagsWrapper,.select2-container--prestakit .select2-search--dropdown .select2-search__field,.select2-container--prestakit .select2-selection,.tooltip,body,h1,h2,h3,h4,h5,h6,.bootstrap #login-panel #shop_name,.onboarding-intro h3.text-center,.bootstrap .page-head .page-title, .modal-title, .module-addons-search, .module-search-result-title,.display-modal{font-family:{nameFont},sans-serif}";

        return $contentCss;
    }

    private function getContentCss(): string
    {
        $fileWoff = $this->getFileFont('.woff');
        $fileWoff2 = $this->getFileFont('.woff2');

        $fontFace = $this->getFontFace($fileWoff, $fileWoff2);
        if (empty($fontFace)) {
            return $fontFace;
        }

        $fileBoldWoff = $this->getFileFont('-bold.woff');
        $fileBoldWoff2 = $this->getFileFont('-bold.woff');
        $fontFace .= $this->getFontFace($fileBoldWoff, $fileBoldWoff2, true);

        return $this->setFont($fontFace . $this->getCssSetFont());;
    }

    private function setFont($contentCss): string
    {
        $contentCss = str_replace('{revertFolder}', '../../', $contentCss);
        $contentCss = str_replace('{nameFont}', ucfirst($this->font), $contentCss);
        return str_replace('{folder}', $this->font, $contentCss);
    }
}