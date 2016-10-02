<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jtestreport
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Update's Default View
 *
 * @since  __DEPLOY_VERSION__
 */
class JtestreportViewDefault extends JViewLegacy
{
	/**
	 * Renders the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$this->extensions = $this->get('Extensions');

		$this->unenabledExtensions = $this->get('UnenabledExtensions');
		$this->enabledExtensions   = $this->get('EnabledExtensions');

		$this->form = $this->get('Form');

		JToolbarHelper::title(JText::_('COM_JTEXTREPORT_DEFAULT'), 'loop install');
		JToolbarHelper::custom('default.send', 'refresh', 'refresh', 'Send', false);

		// Render the view.
		parent::display($tpl);
	}

	/**
	 * Render the result view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function displayResult($tpl = null)
	{
		JToolbarHelper::title(JText::_('COM_JTEXTREPORT_DEFAULT'), 'loop install');

		parent::display($tpl);
	}
}
