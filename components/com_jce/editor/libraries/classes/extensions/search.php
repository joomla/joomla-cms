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

class WFSearchExtension extends WFExtension
{
    private static $instances = array();

    /**
     * Returns a reference to a plugin object.
     *
     * This method must be invoked as:
     *         <pre>  $advlink =AdvLink::getInstance();</pre>
     *
     * @return JCE The editor object
     *
     * @since    1.5
     */
    public static function getInstance($type, $config = array())
    {
        if (!isset(self::$instances)) {
            self::$instances = array();
        }

        if (empty(self::$instances[$type])) {
            require_once WF_EDITOR . '/extensions/search/' . $type . '.php';

            $classname = 'WF' . ucfirst($type) . 'SearchExtension';

            if (class_exists($classname)) {
                self::$instances[$type] = new $classname($config);
            } else {
                self::$instances[$type] = new self();
            }
        }

        return self::$instances[$type];
    }
}
