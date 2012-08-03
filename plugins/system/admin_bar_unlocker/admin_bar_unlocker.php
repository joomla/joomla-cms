<?php
/**
* @version   $Id$
* @package   Admin Bar Unlocker
* @copyright Copyright (C) 2008 - 2011 Edvard Ananyan. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Admin Forever plugin
 *
 */
class  plgSystemAdmin_Bar_Unlocker extends JPlugin {
    /**
     * Constructor
     *
     * @access protected
     * @param  object $subject The object to observe
     * @param  array  $config  An array that holds the plugin configuration
     * @since  1.0
     */
    function __construct(& $subject, $config) {
        // check to see if we are on backend to execute plugin
        if(!JFactory::getApplication()->isAdmin())
            return;

        // check to see if the user is admin
        $user = JFactory::getUser();
        if(!$user->authorise('manage', 'com_banners'))
            return;

        parent::__construct($subject, $config);
    }

    /**
     * Unlock admin bar
     *
     * @access public
     */
    function onAfterDispatch() {
        JRequest::setVar('hidemainmenu', 0);
    }
}