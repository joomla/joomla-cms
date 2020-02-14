<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFFontselectPluginConfig
{
    protected static $fonts = array('Andale Mono=andale mono,times', 'Arial=arial,helvetica,sans-serif', 'Arial Black=arial black,avant garde', 'Book Antiqua=book antiqua,palatino', 'Comic Sans MS=comic sans ms,sans-serif', 'Courier New=courier new,courier', 'Georgia=georgia,palatino', 'Helvetica=helvetica', 'Impact=impact,chicago', 'Symbol=symbol', 'Tahoma=tahoma,arial,helvetica,sans-serif', 'Terminal=terminal,monaco', 'Times New Roman=times new roman,times', 'Trebuchet MS=trebuchet ms,geneva', 'Verdana=verdana,geneva', 'Webdings=webdings', 'Wingdings=wingdings,zapf dingbats');

    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $settings['fontselect_fonts'] = self::getFonts();
    }

    /**
     * Get a list of editor font families.
     *
     * @return string font family list
     *
     * @param string $add    Font family to add
     * @param string $remove Font family to remove
     */
    protected static function getFonts()
    {
        $wf = WFApplication::getInstance();

        $fonts = $wf->getParam('fontselect.fonts');

        // decode string
        $fonts = htmlspecialchars_decode($fonts);

        // get fonts using legacy parameters
        if (empty($fonts)) {
            $fonts = self::$fonts;

            $add = $wf->getParam('editor.theme_advanced_fonts_add');
            $remove = $wf->getParam('editor.theme_advanced_fonts_remove');

            if (empty($remove) && empty($add)) {
                return '';
            }

            $remove = preg_split('/[;,]+/', $remove);

            if (count($remove)) {
                foreach ($fonts as $key => $value) {
                    foreach ($remove as $gone) {
                        if ($gone && preg_match('/^'.$gone.'=/i', $value)) {
                            // Remove family
                            unset($fonts[$key]);
                        }
                    }
                }
            }
            foreach (explode(';', $add) as $new) {
                // Add new font family
                if (preg_match('/([^\=]+)(\=)([^\=]+)/', trim($new)) && !in_array($new, $fonts)) {
                    $fonts[] = $new;
                }
            }

            natcasesort($fonts);
            $fonts = implode(';', $fonts);
        }

        return $fonts;
    }
}
