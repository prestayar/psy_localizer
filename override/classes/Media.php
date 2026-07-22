<?php

class Media extends MediaCore
{
    public static function getCSSPath($cssUri, $cssMediaType = 'all', $needRtl = true)
    {
        if ($needRtl && Context::getContext()->language->is_rtl) {
            $cssUriRtl = preg_replace('/^([^?#]*)(\.css)([?#].*)?$/', '$1_rtl$2$3', $cssUri);
            $rtlMedia = Media::getMediaPath($cssUriRtl, $cssMediaType);
            if ($rtlMedia !== false) {
                return $rtlMedia;
            }
        }

        return Media::getMediaPath($cssUri, $cssMediaType);
    }
}
