<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Link component
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentViewLink extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$config = JComponentHelper::getParams('com_content');

		$this->session     = JFactory::getSession();
		$this->config      = $config;
		$this->state       = $this->get('state');
		$this->folderList  = $this->get('folderList');

		parent::display($tpl);
	}
}
