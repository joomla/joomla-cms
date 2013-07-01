<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


/**
 * View for the global configuration
 *
 * @package     Joomla.Site
 * @subpackage  com_services
 * @since       3.2
 */
class ServicesViewConfigHtml extends JViewLegacy
{
	public $state;

	public $form;

	public $data;

	/**
	 * Method to display the view.
	 * 
	 * @param   string  $tpl          Layout
	 * 
	 * @return  void
	 *
	 */
	public function render($tpl = null)
	{

		$user = JFactory::getUser();

		// Check for model errors.
		if ($errors = $this->get('Errors'))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		$this->userIsSuperAdmin = $user->authorise('core.admin');

		parent::display($tpl);
	}

}
