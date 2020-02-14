<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFFontcolorPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $settings['fontcolor_foreground_color'] = $wf->getParam('fontcolor.foreground_color', '');
        $settings['fontcolor_background_color'] = $wf->getParam('fontcolor.background_color', '');

        $settings['fontcolor_foreground_colors'] = $wf->getParam('fontcolor.foreground_colors', '');
        $settings['fontcolor_background_colors'] = $wf->getParam('fontcolor.background_colors', '');
    }
}
