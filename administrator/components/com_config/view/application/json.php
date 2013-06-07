<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once dirname(dirname(__DIR__)) . '/helper/component.php';

/**
 * View for the component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 */
class ConfigViewApplicationJson extends JViewLegacy
{

	public $state;

	public $data;

	/**
	 * Display the view
	 * 
	 * @param   string  $tpl  Layout
	 * 
	 * @return  string
	 */
	public function render($tpl = null)
	{
		$data	= $this->get('Data');
		$user = JFactory::getUser();

		// Check for model errors.
		if ($errors = $this->get('Errors'))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		$this->userIsSuperAdmin = $user->authorise('core.admin');

		echo json_encode($data);
	}

}
