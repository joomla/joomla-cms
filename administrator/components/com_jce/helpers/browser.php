<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

abstract class WfBrowserHelper
{
    public static function getBrowserLink($element = null, $filter = '', $callback = '')
    {
        $app = JFactory::getApplication();
        
        // set $url as empty string
        $url = '';

        // load editor class
        require_once JPATH_SITE . '/components/com_jce/editor/libraries/classes/application.php';

        // get editor instance
        $wf = WFApplication::getInstance();

        // check the current user is in a profile
        if ($wf->getProfile('browser')) {
            $token = JFactory::getSession()->getFormToken();

            $url = 'index.php?option=com_jce&task=plugin.display&plugin=browser&standalone=1&' . $token . '=1&client=' . $app->getClientId();

            if ($element) {
                $url .= '&element='.$element;
            }

            if ($filter) {
                $url .= '&filter='.$filter;
            }

            if ($callback) {
                $url .= '&callback=' . $callback;
            }
        }

        return $url;
    }

    public static function getMediaFieldLink($element = null, $filter = 'images', $callback = '')
    {
        return self::getBrowserLink($element, $filter, $callback);
    }
}
