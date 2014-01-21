<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Cpanel component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 * @since       1.0
 */
class CpanelViewCpanel extends JViewLegacy
{
	/**
	 * Array of cpanel modules
	 *
	 * @var  array
	 */
	protected $modules = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		// Set toolbar items for the page
		JToolbarHelper::title(JText::_('COM_CPANEL'), 'home-2 cpanel');
		JToolbarHelper::help('screen.cpanel');

		$input = JFactory::getApplication()->input;

		/*
		 * Set the template - this will display cpanel.php
		 * from the selected admin template.
		 */
		$input->set('tmpl', 'cpanel');

		// Display the cpanel modules
		$this->modules = JModuleHelper::getModules('cpanel');

		// Load the RAD layer and count the number of post-installation messages
		if (!defined('FOF_INCLUDED'))
		{
			require_once JPATH_LIBRARIES . '/fof/include.php';
		}

		$messages_model = FOFModel::getTmpInstance('Messages', 'PostinstallModel', array('input' => array('eid' => 700)));
		// In case of an Upgrade, the #__postinstall_messages is not present and therefore throw an Exception
		// Here we intercept any error to allow displaying a cPanel without any "post messages".
		try
		{
		   $messages = $messages_model->getItemList();
		}
		catch( RuntimeException $e) {
		   $messages = array();
		}


		$this->postinstall_message_count = count($messages);

		parent::display($tpl);
	}
}
