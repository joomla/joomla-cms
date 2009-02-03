<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');

/**
 * Remind view class for JXtended Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersViewRemind extends JView
{
	/**
	 * Method to display the view.
	 *
	 * @param	string	$tpl	The template file to include
	 */
	public function display($tpl = null)
	{
		// Get the view data.
		$form = &$this->get('Form');

		// Check for errors.
		if (count($errors = &$this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$form->setAction(JRoute::_('index.php?option=com_members&task=member.remind'));

		// Push the data into the view.
		$this->assignRef('form', $form);

		parent::display($tpl);
	}
}