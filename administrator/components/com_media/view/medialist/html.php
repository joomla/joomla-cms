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
class MediaViewMediaListHtml extends JViewHtml
{
	protected $_layoutExt = 'php';

	public function render()
	{
		// Do not allow cache
		JResponse::allowCache(false);

		$app = JFactory::getApplication();
		$style = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');
		$this->setLayout($style);

		$lang = JFactory::getLanguage();

		JHtml::_('behavior.framework', true);

		$document = JFactory::getDocument();
		/*
		$document->addStyleSheet('../media/media/css/medialist-'.$style.'.css');
		if ($lang->isRTL()) :
			$document->addStyleSheet('../media/media/css/medialist-'.$style.'_rtl.css');
		endif;
		*/
		$document->addScriptDeclaration("
		window.addEvent('domready', function()
		{
			window.parent.document.updateUploader();
			$$('a.img-preview').each(function(el)
			{
				el.addEvent('click', function(e)
				{
					new Event(e).stop();
					window.top.document.preview.fromElement(el);
				});
			});
		});");


		$state = $this->model->getState();
		$images = $this->model->getImages();
		$documents = $this->model->getDocuments();
		$folders = $this->model->getFolders();

		$this->baseURL = JURI::root();
		$this->images = & $images;
		$this->documents = & $documents;
		$this->folders = & $folders;
		$this->state = & $state;

		return parent::render();
	}

	function setFolder($index = 0)
	{
		if (isset($this->folders[$index]))
		{
			$this->_tmp_folder = & $this->folders[$index];
		}
		else
		{
			$this->_tmp_folder = new JObject;
		}
	}

	function setImage($index = 0)
	{
		if (isset($this->images[$index]))
		{
			$this->_tmp_img = & $this->images[$index];
		}
		else
		{
			$this->_tmp_img = new JObject;
		}
	}

	function setDoc($index = 0)
	{
		if (isset($this->documents[$index]))
		{
			$this->_tmp_doc = & $this->documents[$index];
		}
		else
		{
			$this->_tmp_doc = new JObject;
		}
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
}
