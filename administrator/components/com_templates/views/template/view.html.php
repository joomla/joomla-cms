<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a template style.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       1.6
 */
class TemplatesViewTemplate extends JViewLegacy
{
	protected $files;

	protected $state;

	protected $template;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->files	= $this->get('Files');
		$this->state	= $this->get('State');
		$this->template	= $this->get('Template');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$user  = JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_TEMPLATES_MANAGER_VIEW_TEMPLATE'), 'thememanager');

		JToolbarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');

		// Add a copy button
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('JLIB_HTML_BATCH_COPY');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'upload');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT');
	}
}
