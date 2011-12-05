<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Extended Utility class for the Users component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       2.5
 */
class JHtmlUsers
{
	/**
	 * Display an image.
	 *
	 * @param   string	$src  The source of the image
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	public static function image($src)
	{
		$src = preg_replace('#[^A-Z0-9\-_\.]#i', '', $src);
		$file = JPATH_SITE . '/images/' . $src;

		jimport('joomla.filesystem.path');
		JPath::check($file);

		if (!file_exists($file))
		{
			return '';
		}

		return '<img src="' . JUri::root() . 'images/' . $src . '" alt="Icon" />';
	}

	/**
	 * Displays an icon to add a note for this user.
	 *
	 * @return	string
	 * @since	1.1
	 */
	function addNote($userId)
	{
		$title = JText::_('COM_USERS_ADD_NOTE');

		return '<a href="'.JRoute::_('index.php?option=com_users&task=note.add&u_id='.(int) $userId).'">'.
			JHtml::image('images/note_add_16.png', 'COM_USERS_NOTES', array('title' => $title), true) . '</a>';
	}

	/**
	 * Displays an icon to filter the notes list on this user.
	 *
	 * @return	string
	 * @since	1.1
	 */
	function filterNotes($count, $userId)
	{
		if (empty($count)) {
			return '';
		}

		$title = JText::_('COM_USERS_FITLER_NOTES');

		return '<a href="'.JRoute::_('index.php?option=com_users&view=notes&filter_search=uid:'.(int) $userId).'">'.
			JHtml::image('images/filter_16.png', 'COM_USERS_NOTES', array('title' => $title), true) . '</a>';
	}

	/**
	 * Displays a note icon.
	 *
	 * @return	string
	 * @since	1.1
	 */
	function notes($count, $userId)
	{
		if (empty($count)) {
			return '';
		}

		$title = JText::sprintf('COM_USERS_N_USER_NOTES', $count);

		return
			'<a class="modal" href="'.JRoute::_('index.php?option=com_users&view=notes&tmpl=component&layout=modal&u_id='.(int) $userId).'">'.
			JHtml::image('images/note_16.png', 'COM_USERS_NOTES', array('title' => $title), true) .
			'</a>';
	}
}
