<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if ($this->item->id !== 0)
{
    // $user_id = $jinput->get('id');
    JHtml::script('com_users/sessionend.js', false, true);
    JFactory::getDocument()->addScriptDeclaration('
        var endsession_url = "' . addslashes(JUri::base()) . 'index.php?option=com_users&task=user.endSession&format=json&' . JSession::getFormToken() . '=1";
        var user_id = ' . JFactory::getApplication()->input->get('id', 0, 'int') . ';
     ');

    echo '<button type="button" class="btn btn-small" id="end_session">
            <span>' . JText::_('COM_USERS_END_SESSION_ACTION_BUTTON') . '</span>
        </button>';
}