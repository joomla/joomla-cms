<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFAdvlistPluginConfig
{
    public static function getConfig(&$settings)
    {
        $bullet = self::getBulletList();
        $settings['advlist_bullet_styles'] = $bullet !== false ? implode(',', $bullet) : false;

        $number = self::getNumberList();
        $settings['advlist_number_styles'] = $number !== false ? implode(',', $number) : false;
    }

    private static function getNumberList()
    {
        $wf = WFApplication::getInstance();
        $number = (array) $wf->getParam('lists.number_styles');

        if (empty($number) || (count($number) === 1 && array_shift($number) === 'default')) {
            return false;
        }

        return $number;
    }

    private static function getBulletList()
    {
        $wf = WFApplication::getInstance();
        $bullet = (array) $wf->getParam('lists.bullet_styles');

        if (empty($bullet) || (count($bullet) === 1 && array_shift($bullet) === 'default')) {
            return false;
        }

        return $bullet;
    }
}
