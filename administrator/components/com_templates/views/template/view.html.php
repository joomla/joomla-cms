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

	protected $form;
	
	protected $source;

    protected $id;

    protected $file;

    protected $overridesList;

    protected $less;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->files	= $this->get('Files');
		$this->state	= $this->get('State');
		$this->template	= $this->get('Template');
		
		$this->form		= $this->get('Form');
		$this->source	= $this->get('Source');
		$this->overridesList	= $this->get('OverridesList');
        $this->id       = $this->state->get('extension.id');

        $app            = JFactory::getApplication();
        $this->file     = $app->input->get('file');
        $this->less     = $app->input->get('Less');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
            $app->enqueueMessage(implode("\n", $errors));
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
		$canDo = TemplatesHelper::getActions();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$user  = JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_TEMPLATES_MANAGER_VIEW_TEMPLATE'), 'thememanager');
		
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('template.apply');
			JToolbarHelper::save('template.save');
		}
		
		// Add a copy button
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('JLIB_HTML_BATCH_COPY');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
			<i class=\"icon-copy\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}
		
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('JTOOLBAR_UPLOAD');
			$dhtml = "<button data-toggle=\"collapse\" data-target=\"#\" class=\"btn btn-small \">
			<i class=\"icon-plus icon-white\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}

		// Add a new file button
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('New File');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#\" class=\"btn btn-small\">
			<i class=\"icon-file\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}
		
		// Add new overrides button
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('Delete');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#deleteModal\" class=\"btn btn-small\">
			<i class=\"icon-remove\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}
		
		//Add a Compile Button
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('Compile LESS');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#lessModal\" class=\"btn btn-small\">
			<i class=\"icon-play\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}
		
		JToolbarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT');
	}
	
	
	function listDirectoryTree($array)
	{
		echo "<ul class='nav nav-list directory-tree'>";
		ksort($array, SORT_STRING);
		foreach($array as $key => $value)
		{
			if(is_array($value))
			{		
				echo "<li class='folder'>";
				echo "<a class='folder-url' href=''><i class='icon-folder-close'>&nbsp;";
				echo end(explode('/',$key));
				echo "</i></a>";
				$this->listDirectoryTree($value);
				echo "</li>";
			}
	
			elseif(is_object($value))
			{		
				echo "<li>";
				echo "<a href='" . JRoute::_('index.php?option=com_templates&view=template&id=' . $this->id . '&file=' . $value->id) . "'>";
				echo "<i class='icon-file'>&nbsp;" . $value->name . "</i>";
				echo "</a>";
				echo "</li>";
			}
	
		}
	
		echo "</ul>";
	
	}
}
