<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('WF_EDITOR') or die('RESTRICTED');

require_once WF_EDITOR_LIBRARIES . '/classes/plugin.php';

class WFClipboardPlugin extends WFEditorPlugin
{
    public function display()
    {
        parent::display();

        $document = WFDocument::getInstance();

        // get command, eg: mcePaste, mcePasteText
        $cmd = JFactory::getApplication()->input->getCmd('cmd', 'paste');

        // remove mce prefix
        $cmd = str_replace('mce', '', $cmd);

        // set title
        $document->setTitle(JText::_('WF_' . strtoupper($cmd) . '_TITLE'));

        $document->addScript(array('clipboard'), 'plugins');
        $document->addStyleSheet(array('clipboard'), 'plugins');
    }
}
