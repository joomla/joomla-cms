<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFFormatPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $settings['inline_styles'] = $wf->getParam('editor.inline_styles', 1, 1);

        // Paragraph handling
        $forced_root_block = $wf->getParam('editor.forced_root_block', 'p');

        // set as boolean if disabled
        if (is_numeric($forced_root_block)) {
            $settings['forced_root_block'] = (bool) intval($forced_root_block);

            if ($wf->getParam('editor.force_br_newlines', 0, 0, 'boolean') === false) {
                // legacy
                $settings['force_p_newlines'] = $wf->getParam('editor.force_p_newlines', 1, 0, 'boolean');
            }
        } else {
            if (strpos($forced_root_block, '|') !== false) {
                // multiple values
                foreach (explode('|', $forced_root_block) as $option) {
                    list($key, $value) = explode(':', $option);

                    $settings[$key] = (bool) $value;
                }
            } else {
                $settings['forced_root_block'] = $forced_root_block;
            }
        }

        //$settings['removeformat_selector'] = $wf->getParam('editor.removeformat_selector', 'span,b,strong,em,i,font,u,strike', 'span,b,strong,em,i,font,u,strike');

        // Relative urls
        $settings['relative_urls'] = $wf->getParam('editor.relative_urls', 1, 1, 'boolean');
        
        // set remove_script_host if relative_urls is disabled
        if ($settings['relative_urls'] === false) {
            $settings['remove_script_host'] = false;
        }
    }
}
