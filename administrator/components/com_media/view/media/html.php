	<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Media component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaViewMediaHtml extends JViewHtml
{
	protected $_layoutExt = 'php';

	public function render()
	{
		$app = JFactory::getApplication();
		$config = JComponentHelper::getParams('com_media');

		$lang = JFactory::getLanguage();

		$style = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

		$document = JFactory::getDocument();

		MediaHelper::addSubmenu('media');

		JHtml::_('behavior.framework', true);

		JHtml::_('script', 'media/mediamanager.js', true, true);
		/*
		JHtml::_('stylesheet', 'media/mediamanager.css', array(), true);
		if ($lang->isRTL()) :
			JHtml::_('stylesheet', 'media/mediamanager_rtl.css', array(), true);
		endif;
		*/
		JHtml::_('behavior.modal');
		$document->addScriptDeclaration("
		window.addEvent('domready', function()
		{
			document.preview = SqueezeBox;
		});");

		// JHtml::_('script', 'system/mootree.js', true, true, false, false);
		JHtml::_('stylesheet', 'system/mootree.css', array(), true);

		if ($lang->isRTL()) :
			JHtml::_('stylesheet', 'media/mootree_rtl.css', array(), true);
		endif;

		if (DIRECTORY_SEPARATOR == '\\')
		{
			$base = str_replace(DIRECTORY_SEPARATOR, "\\\\", COM_MEDIA_BASE);
		}
		else
		{
			$base = COM_MEDIA_BASE;
		}

		$js = "
			var basepath = '" . $base . "';
			var viewstyle = '" . $style . "';
		";
		$document->addScriptDeclaration($js);

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		$ftp = !JClientHelper::hasCredentials('ftp');

		$session = JFactory::getSession();
		$state = $this->model->getState();
		$this->session = $session;
		$this->config = & $config;
		$this->state = & $state;
		$this->require_ftp = $ftp;
		$this->folders_id = ' id="media-tree"';
		$this->folders = $this->model->getFolderTree();

		// Set the toolbar
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::render();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.2
	 */
	protected function addToolbar()
	{
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$user = JFactory::getUser();

		// Set the titlebar text
		JToolbarHelper::title(JText::_('COM_MEDIA'), 'mediamanager.png');

		// Add a upload button
		if ($user->authorise('core.create', 'com_media'))
		{
			$title = JText::_('JTOOLBAR_UPLOAD');
			$dhtml = "<button data-toggle=\"collapse\" data-target=\"#collapseUpload\" class=\"btn btn-small btn-success\">
						<i class=\"icon-plus icon-white\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
			JToolbarHelper::divider();
		}

		// Add a create folder button
		if ($user->authorise('core.create', 'com_media'))
		{
			$title = JText::_('COM_MEDIA_CREATE_FOLDER');
			$dhtml = "<button data-toggle=\"collapse\" data-target=\"#collapseFolder\" class=\"btn btn-small\">
						<i class=\"icon-folder\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'folder');
			JToolbarHelper::divider();
		}

		// Add a rename button
		if ($user->authorise('core.create', 'com_media') && $user->authorise('core.delete', 'com_media'))
		{
			$title = JText::_('COM_MEDIA_RENAME');
			$dhtml = "<button data-toggle=\"collapse\" data-target=\"#collapseRename\" class=\"btn btn-small\">
						<i class=\"icon-edit\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'rename');
			JToolbarHelper::divider();
		}


		// Add a delete button
		if ($user->authorise('core.delete', 'com_media'))
		{
			$title = JText::_('JTOOLBAR_DELETE');
			$dhtml = "<button href=\"#\" onclick=\"MediaManager.submit('delete')\" class=\"btn btn-small\">
						<i class=\"icon-remove\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'delete');
			JToolbarHelper::divider();
		}

		// Check in button
		if ($user->authorise('core.create', 'com_media') && $user->authorise('core.delete', 'com_media'))
		{
			$title = JText::_('Check in');
			$dhtml = "<button href=\"#\"  onclick=\"MediaManager.submit('edit', 'checkInBulk')\" class=\"btn btn-small\">
						<i class=\"icon-checkin\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'checkIn');
			JToolbarHelper::divider();
		}

		// This button is used to sync medias with database
		if ($user->authorise('core.admin', 'com_media'))
		{
			$title = JText::_('Sync database');
			$dhtml = "<button href=\"#\" onclick=\"location.href='index.php?option=com_media&controller=sync'\" class=\"btn btn-small\">
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'sync');
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.admin', 'com_media'))
		{
			JToolbarHelper::preferences('com_media');
			JToolbarHelper::divider();
		}

		JHtmlSidebar::setAction('index.php?option=com_media');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', JHtml::_('category.options', 'com_media'), 'value', 'text', $this->state->get('filter.category.id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);
	}

	public function loadTemplate($tpl = null)
	{
		// Clear prior output
		$this->_output = null;

		$template = JFactory::getApplication()->getTemplate();
		$layout = $this->getLayout();

		// Create the template file name based on the layout
		$file = isset($tpl) ? $layout . '_' . $tpl : $layout;

		// Clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl = isset($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

		// Load the language file for the template
		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, false)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, false)
		|| $lang->load('tpl_' . $template, JPATH_BASE, $lang->getDefault(), false, false)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", $lang->getDefault(), false, false);

		// Change the template folder if alternative layout is in different template
		/* if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$this->_path['template'] = str_replace($template, $layoutTemplate, $this->_path['template']);
		} */

		// Prevents adding path twise
		if (empty($this->_path['template']))
		{
			// Adding template paths
			$this->paths->top();
			$defaultPath = $this->paths->current();
			$this->paths->next();
			$templatePath = $this->paths->current();
			$this->_path['template'] = array($defaultPath, $templatePath);
		}

		// Load the template script
		jimport('joomla.filesystem.path');
		$filetofind = $this->_createFileName('template', array('name' => $file));
		$this->_template = JPath::find($this->_path['template'], $filetofind);

		// If alternate layout can't be found, fall back to default layout
		if ($this->_template == false)
		{
			$filetofind = $this->_createFileName('', array('name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)));
			$this->_template = JPath::find($this->_path['template'], $filetofind);
		}

		if ($this->_template != false)
		{
			// Unset so as not to introduce into template scope
			unset($tpl);
			unset($file);

			// Never allow a 'this' property
			if (isset($this->this))
			{
				unset($this->this);
			}

			// Start capturing output into a buffer
			ob_start();

			// Include the requested template filename in the local scope
			// (this will execute the view logic).
			include $this->_template;

			// Done with the requested template; get the buffer and
			// clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else
		{
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
		}
	}

	protected function _createFileName($type, $parts = array())
	{
		$filename = '';

		switch ($type)
		{
			case 'template':
				$filename = strtolower($parts['name']) . '.' . $this->_layoutExt;
				break;

			default:
				$filename = strtolower($parts['name']) . '.php';
				break;
		}

		return $filename;
	}

	protected function getSortFields()
	{
		return array(
			'alias' => JText::_('JGLOBAL_NAME'),
			'catid' => JText::_('JCATEGORY'),
			'access' => JText::_('JGRID_HEADING_ACCESS'),
			'created_user_id' => JText::_('JAUTHOR'),
			'created_time' => JText::_('JDATE')
		);
	}

	function getFolderLevel($folder)
	{
		$this->folders_id = null;
		$txt = null;

		if (isset($folder['children']) && count($folder['children']))
		{
			$tmp = $this->folders;
			$this->folders = $folder;
			$txt = $this->loadTemplate('folders');
			$this->folders = $tmp;
		}

		return $txt;
	}
}
