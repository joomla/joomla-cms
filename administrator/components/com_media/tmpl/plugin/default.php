<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//Get the action from the input
$action = JFactory::getApplication()->input->getWord('action');

/**
 * Close current window
 *
 * This method only works for windows that were opened using window.open(url);
 * It will close such windows on this command.
 * Windows opened by user are not affected.
 */
if ($action == 'close')
{
    \JFactory::getDocument()->addScriptDeclaration('<script>window.close();</script>');
}

