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

    protected $fileName;

    protected $type;

    protected $image;

    protected $preview;

    protected $font;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
        $app            = JFactory::getApplication();
        $this->file     = $app->input->get('file');
        $this->fileName = base64_decode($this->file);
        $explodeArray   = explode('.',$this->fileName);
        $ext            = end($explodeArray);
		$this->files	= $this->get('Files');
		$this->state	= $this->get('State');
		$this->template	= $this->get('Template');
		$this->preview	= $this->get('Preview');

        if(in_array($ext, array('css','js','php','xml','ini','less')))
        {
            $this->form		= $this->get('Form');
            $this->source	= $this->get('Source');
            $this->type 	= 'file';
        }
        elseif(in_array($ext, array('jpg','jpeg','png','gif')))
        {
            $this->image    = $this->get('Image');
            $this->type 	= 'image';
        }
        elseif(in_array($ext, array('woff','otf','ttf')))
        {
            $this->font     = $this->get('Font');
            $this->type 	= 'font';
        }
		$this->overridesList	= $this->get('OverridesList');
        $this->id       = $this->state->get('extension.id');



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
        $explodeArray = explode('.',$this->fileName);
        $ext = end($explodeArray);

		JToolbarHelper::title(JText::_('COM_TEMPLATES_MANAGER_VIEW_TEMPLATE'), 'thememanager');

        if($this->type == 'file')
        {
            if ($canDo->get('core.edit'))
            {
                JToolbarHelper::apply('template.apply');
                JToolbarHelper::save('template.save');
            }
        }

        elseif($this->type == 'image')
        {
            if ($canDo->get('core.edit'))
            {
                $bar->appendButton('Standard', 'archive', 'COM_TEMPLATES_BUTTON_CROP', 'template.cropImage', false);
            }

            if ($canDo->get('core.edit'))
            {
                $title = JText::_('COM_TEMPLATES_BUTTON_RESIZE');
                $dhtml = "<button data-toggle=\"modal\" data-target=\"#resizeModal\" class=\"btn btn-small\">
			    <i class=\"icon-move\" title=\"$title\"></i>
			    $title</button>";
                $bar->appendButton('Custom', $dhtml, 'upload');
            }
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

        if ($this->preview->client_id == 0)
        {
            $title = JText::_('COM_TEMPLATES_BUTTON_PREVIEW');
            $route = JUri::root() . 'index.php?tp=1&templateStyle=' . $this->preview->id;
            $dhtml = "<a class=\"btn btn-small \" href=\" $route \" target=\" _blank \">
			<i class=\"icon-picture icon-white\" title=\"$title\"></i>
			$title</a>";
            $bar->appendButton('Custom', $dhtml, 'upload');
        }

		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('COM_TEMPLATES_BUTTON_FOLDERS');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#folderModal\" class=\"btn btn-small \">
			<i class=\"icon-folder icon-white\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}

		// Add a new file button
		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('COM_TEMPLATES_BUTTON_FILE');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#fileModal\" class=\"btn btn-small\">
			<i class=\"icon-file\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}

        if ($user->authorise('core.create', 'com_templates'))
        {
            $title = JText::_('COM_TEMPLATES_BUTTON_RENAME');
            $dhtml = "<button data-toggle=\"modal\" data-target=\"#renameModal\" class=\"btn btn-small\">
			<i class=\"icon-refresh\" title=\"$title\"></i>
			$title</button>";
            $bar->appendButton('Custom', $dhtml, 'upload');
        }

		if ($user->authorise('core.create', 'com_templates'))
		{
			$title = JText::_('COM_TEMPLATES_BUTTON_DELETE');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#deleteModal\" class=\"btn btn-small\">
			<i class=\"icon-remove\" title=\"$title\"></i>
			$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
		}
		
		//Add a Compile Button
		if ($user->authorise('core.create', 'com_templates'))
		{
			if($ext == 'less')
            {
                $title = JText::_('COM_TEMPLATES_BUTTON_LESS');
                $dhtml = "<button onclick=\"Joomla.submitbutton('template.less')\" class=\"btn btn-small\">
                <i class=\"icon-play\" title=\"$title\"></i>
                $title</button>";
                $bar->appendButton('Custom', $dhtml, 'upload');
            }
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
                if(stristr($this->fileName,$key) != false)
                {
                    $class = "folder show";
                }
                else
                {
                    $class = "folder";
                }
				echo "<li class='" . $class . "'>";
				echo "<a class='folder-url' href=''><i class='icon-folder-close'>&nbsp;";
                $explodeArray = explode('/',$key);
				echo end($explodeArray);
				echo "</i></a>";
				$this->listDirectoryTree($value);
				echo "</li>";
			}
	
			elseif(is_object($value))
			{		
				echo "<li>";
				echo "<a class='file' href='" . JRoute::_('index.php?option=com_templates&view=template&id=' . $this->id . '&file=' . $value->id) . "'>";
				echo "<i class='icon-file'>&nbsp;" . $value->name . "</i>";
				echo "</a>";
				echo "</li>";
			}
	
		}
	
		echo "</ul>";
	
	}

    function listFolderTree($array)
    {
        echo "<ul class='nav nav-list folder-tree'>";
        ksort($array, SORT_STRING);
        foreach($array as $key => $value)
        {
            if(is_array($value))
            {
                echo "<li class='folder-select'>";
                $encodedKey = base64_encode($key);
                echo "<a class='folder-url' data-id='$encodedKey' href=''><i class='icon-folder-close'>&nbsp;";
                $explodeArray = explode('/',$key);
                echo end($explodeArray);
                echo "</i></a>";
                $this->listFolderTree($value);
                echo "</li>";
            }

        }

        echo "</ul>";

    }

}
