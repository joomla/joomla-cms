<?php
/**
* @package     Joomla.Administrator
* @subpackage  com_templates
*
* @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

/**
* View to edit a template style.
*
* @since  1.6
*/
class TemplatesViewTemplate extends JViewLegacy
{
	/**
	 * For loading extension state
	 */
	protected $state;

	/**
	 * For loading template details
	 */
	protected $template;

	/**
	 * For loading the source form
	 */
	protected $form;

	/**
	 * For loading source file contents
	 */
	protected $source;

	/**
	 * Extension id
	 */
	protected $id;

	/**
	 * Encrypted file path
	 */
	protected $file;

	/**
	 * List of available overrides
	 */
	protected $overridesList;

	/**
	 * Name of the present file
	 */
	protected $fileName;

	/**
	 * Type of the file - image, source, font
	 */
	protected $type;

	/**
	 * For loading image information
	 */
	protected $image;

	/**
	 * Template id for showing preview button
	 */
	protected $preview;

	/**
	 * For loading font information
	 */
	protected $font;

	/**
	 * A nested array containing lst of files and folders
	 */
	protected $files;

	/**
	 * An array containing a list of compressed files
	 */
	protected $archive;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$app            = JFactory::getApplication();
		$this->file     = $app->input->get('file');
		$this->fileName = JFilterInput::getInstance()->clean(base64_decode($this->file), 'string');
		$explodeArray   = explode('.', $this->fileName);
		$ext            = end($explodeArray);
		$this->files    = $this->get('Files');
		$this->state    = $this->get('State');
		$this->template = $this->get('Template');
		$this->preview  = $this->get('Preview');

		$params       = JComponentHelper::getParams('com_templates');
		$imageTypes   = explode(',', $params->get('image_formats'));
		$sourceTypes  = explode(',', $params->get('source_formats'));
		$fontTypes    = explode(',', $params->get('font_formats'));
		$archiveTypes = explode(',', $params->get('compressed_formats'));

		if (in_array($ext, $sourceTypes))
		{
			$this->form   = $this->get('Form');
			$this->form->setFieldAttribute('source', 'syntax', $ext);
			$this->source = $this->get('Source');
			$this->type   = 'file';
		}
		elseif (in_array($ext, $imageTypes))
		{
			$this->image = $this->get('Image');
			$this->type  = 'image';
		}
		elseif (in_array($ext, $fontTypes))
		{
			$this->font = $this->get('Font');
			$this->type = 'font';
		}
		elseif (in_array($ext, $archiveTypes))
		{
			$this->archive = $this->get('Archive');
			$this->type    = 'archive';
		}
		else
		{
			$this->type = 'home';
		}

		$this->overridesList = $this->get('OverridesList');
		$this->id            = $this->state->get('extension.id');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors));

			return false;
		}

		$this->addToolbar();

		if (!JFactory::getUser()->authorise('core.admin'))
		{
			$this->setLayout('readonly');
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$app->input->set('hidemainmenu', true);

		// User is global SuperUser
		$isSuperUser = $user->authorise('core.admin');

		// Get the toolbar object instance
		$bar = JToolbar::getInstance('toolbar');
		$explodeArray = explode('.', $this->fileName);
		$ext = end($explodeArray);

		JToolbarHelper::title(JText::sprintf('COM_TEMPLATES_MANAGER_VIEW_TEMPLATE', ucfirst($this->template->name)), 'eye thememanager');

		// Only show file edit buttons for global SuperUser
		if ($isSuperUser)
		{
			// Add an Apply and save button
			if ($this->type == 'file')
			{
				JToolbarHelper::apply('template.apply');
				JToolbarHelper::save('template.save');
			}
			// Add a Crop and Resize button
			elseif ($this->type == 'image')
			{
				JToolbarHelper::custom('template.cropImage', 'move', 'move', 'COM_TEMPLATES_BUTTON_CROP', false);
				JToolbarHelper::modal('resizeModal', 'icon-refresh', 'COM_TEMPLATES_BUTTON_RESIZE');
			}
			// Add an extract button
			elseif ($this->type == 'archive')
			{
				JToolbarHelper::custom('template.extractArchive', 'arrow-down', 'arrow-down', 'COM_TEMPLATES_BUTTON_EXTRACT_ARCHIVE', false);
			}

			// Add a copy template button (Hathor override doesn't need the button)
			if ($app->getTemplate() != 'hathor')
			{
				JToolbarHelper::modal('copyModal', 'icon-copy', 'COM_TEMPLATES_BUTTON_COPY_TEMPLATE');
			}
		}

		// Add a Template preview button
		if ($this->preview->client_id == 0)
		{
			$bar->appendButton('Popup', 'picture', 'COM_TEMPLATES_BUTTON_PREVIEW', JUri::root() . 'index.php?tp=1&templateStyle=' . $this->preview->id, 800, 520);
		}

		// Only show file manage buttons for global SuperUser
		if ($isSuperUser)
		{
			// Add Manage folders button
			JToolbarHelper::modal('folderModal', 'icon-folder icon white', 'COM_TEMPLATES_BUTTON_FOLDERS');

			// Add a new file button
			JToolbarHelper::modal('fileModal', 'icon-file', 'COM_TEMPLATES_BUTTON_FILE');

			// Add a Rename file Button (Hathor override doesn't need the button)
			if ($app->getTemplate() != 'hathor' && $this->type != 'home')
			{
				JToolbarHelper::modal('renameModal', 'icon-refresh', 'COM_TEMPLATES_BUTTON_RENAME_FILE');
			}

			// Add a Delete file Button
			if ($this->type != 'home')
			{
				JToolbarHelper::modal('deleteModal', 'icon-remove', 'COM_TEMPLATES_BUTTON_DELETE_FILE');
			}

			// Add a Compile Button
			if ($ext == 'less')
			{
				JToolbarHelper::custom('template.less', 'play', 'play', 'COM_TEMPLATES_BUTTON_LESS', false);
			}
		}

		if ($this->type == 'home')
		{
			JToolbarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			JToolbarHelper::cancel('template.close', 'COM_TEMPLATES_BUTTON_CLOSE_FILE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT');
	}

	/**
	 * Method for creating the collapsible tree.
	 *
	 * @param   array  $array  The value of the present node for recursion
	 *
	 * @return  string
	 *
	 * @note    Uses recursion
	 * @since   3.2
	 */
	protected function directoryTree($array)
	{
		$temp        = $this->files;
		$this->files = $array;
		$txt         = $this->loadTemplate('tree');
		$this->files = $temp;

		return $txt;
	}

	/**
	 * Method for listing the folder tree in modals.
	 *
	 * @param   array  $array  The value of the present node for recursion
	 *
	 * @return  string
	 *
	 * @note    Uses recursion
	 * @since   3.2
	 */
	protected function folderTree($array)
	{
		$temp        = $this->files;
		$this->files = $array;
		$txt         = $this->loadTemplate('folders');
		$this->files = $temp;

		return $txt;
	}
}
