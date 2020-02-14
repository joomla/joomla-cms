<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 3 - http://www.gnu.org/copyleft/gpl.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

require_once JPATH_SITE . '/components/com_jce/editor/libraries/classes/application.php';

class JceControllerEditor extends JControllerLegacy
{
    public function execute($task)
    {
        $app = WFApplication::getInstance();

        // check for session token
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        // check a valid profile exists
        $app->getProfile() or jexit();

        $editor = new WFEditor();

        if (strpos($task, '.') !== false) {
            list($name, $task) = explode('.', $task);
        }

        if (method_exists($editor, $task)) {
            $editor->$task();
        }

        jexit();
    }
}
