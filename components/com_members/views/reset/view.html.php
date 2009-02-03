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
 * Reset view class for JXtended Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersViewReset extends JView
{
	/**
	 * Method to display the view.
	 *
	 * @param	string	$tpl	The template file to include
	 */
	public function display($tpl = null)
	{
		// Get the appropriate form.
		if ($this->_layout === 'confirm') {
			$form = &$this->get('ConfirmForm');
		}
		elseif ($this->_layout === 'complete') {
			$form = &$this->get('CompleteForm');
		}
		else {
			$form = &$this->get('RequestForm');
		}

		// Check for errors.
		if (count($errors = &$this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Push the data into the view.
		$this->assignRef('form', $form);

		parent::display($tpl);
	}
}