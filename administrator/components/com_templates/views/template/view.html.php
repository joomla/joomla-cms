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
	
	protected $tree;
	
	protected $level = 0;
	
	
	protected $form;
	
	protected $ftp;
	
	protected $source;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->files	= $this->get('Files');
		$this->state	= $this->get('State');
		$this->template	= $this->get('Template');
		$this->tree 	= $this->get('DirectoryTree');
		
		$this->form		= $this->get('Form');
		$this->ftp		= JClientHelper::setCredentialsFromRequest('ftp');
		$this->source	= $this->get('Source');

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
		$canDo = TemplatesHelper::getActions();

		JToolbarHelper::title(JText::_('COM_TEMPLATES_MANAGER_VIEW_TEMPLATE'), 'thememanager');
		
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('template.apply');
			JToolbarHelper::save('template.save');
		}
		
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('JTOOLBAR_UPLOAD');
			$dhtml = "<button data-toggle=\"collapse\" data-target=\"#collapseUpload\" class=\"btn btn-small \">
			<i class=\"icon-plus icon-white\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}

		// Add a new file button
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('New File');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
			<i class=\"icon-file\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}
		
		// Add new overrides button
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('Create Overrides');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
			<i class=\"icon-copy\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
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
		
		//Add a Compile Button
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('Compile LESS');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
			<i class=\"icon-play\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}
		
		JToolbarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT');
	}
	
	function listTree($parent,$children)
	{
		$tmp = null;
		if($this->search('parent',$children))
		{
			$this->level = $children;
			$tmp = $this->loadTemplate('tree');
			$this->level = $parent;
		}
		return $tmp;
	}
	
	function search($key,$value)
	{
		foreach($this->tree as $folder)
		{
			if(isset($folder[$key]) && $folder[$key] == $value)
			{
			return true;
			}
		}
	}
	
	function listTreeFiles($folder)
	{
		$files = $this->files;
		foreach($files[$folder] as $file)
		{
			$treeFiles[] = $file;
		}
		return $treeFiles;
	}
}
