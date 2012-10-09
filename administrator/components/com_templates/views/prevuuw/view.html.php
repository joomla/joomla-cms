<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Templates component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesViewPrevuuw extends JViewLegacy
{
	protected $client;
	protected $id;
	protected $option;
	protected $template;
	protected $tp;
	protected $url;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{

		require_once JPATH_COMPONENT.'/helpers/templates.php';

		// Initialise some variables
		$this->client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->id		= JRequest::getVar('id', '', 'method', 'int');
		$this->option	= JRequest::getCmd('option');
		$this->template	= TemplatesHelper::getTemplateName($this->id);
		$this->tp		= true;
		$this->url		= $client->id ? JURI::base() : JURI::root();

		if (!$this->template) {
			return JError::raiseWarning(500, JText::_('COM_TEMPLATES_TEMPLATE_NOT_SPECIFIED'));
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		parent::display($tpl);
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_TEMPLATES_MANAGER'), 'thememanager');
		JToolBarHelper::custom('edit', 'back.png', 'back_f2.png', 'Back', false, false);
	}
}
