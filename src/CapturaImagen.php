<?php

namespace Blacktrue;

use hotrush\Webshotter\Webshot;

class CapturaImagen
{
    /**
     * @param string $url
     * @param string $path
     *
     * @return string
     */
    public static function capturar($url = '', $path = '')
    {
        try {
            $webshot = new Webshot('/usr/local/bin/phantomjs');

            return $webshot
                ->setUrl($url)
                ->setWidth(400)
                ->setHeight(300)
                ->setTimeout(300)
                ->setFullPage(true)
                ->saveToPng(md5($url), $path);
        } catch (\Exception $e) {
            return 'ERROR_NO_CONTENT';
        }
    }
}
