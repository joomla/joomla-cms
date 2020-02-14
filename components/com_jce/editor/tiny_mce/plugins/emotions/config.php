<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFEmotionsPluginConfig
{
    public static function getConfig(&$settings)
    {
        // Get JContentEditor instance
        $wf = WFApplication::getInstance();

        $settings['emotions_smilies'] = $wf->getParam('emotions.smilies');
        $settings['emotions_url'] = $wf->getParam('emotions.url');
    }
}
