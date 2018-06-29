<?php
/**
 * @package    Joomla.Administrator
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;
use Joomla\Cms\Table\Table;

/**
 * Utility class for the button bar.
 *
 * @since  1.5
 */
abstract class ToolbarHelper
{
	/**
	 * Title cell.
	 * For the title and toolbar to be rendered correctly,
	 * this title function must be called before the starttable function and the toolbars icons
	 * this is due to the nature of how the css has been used to position the title in respect to the toolbar.
	 *
	 * @param   string  $title  The title.
	 * @param   string  $icon   The space-separated names of the image.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function title($title, $icon = 'generic.png')
	{
		$layout = new FileLayout('joomla.toolbar.title');
		$html   = $layout->render(array('title' => $title, 'icon' => $icon));

		$app = \JFactory::getApplication();
		$app->JComponentTitle = $html;
		\JFactory::getDocument()->setTitle(strip_tags($title) . ' - ' . $app->get('sitename') . ' - ' . \JText::_('JADMINISTRATION'));
	}

	/**
	 * Writes a spacer cell.
	 *
	 * @param   string  $width  The width for the cell
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function spacer($width = '')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a spacer.
		$bar->appendButton('Separator', 'spacer', $width);
	}

	/**
	 * Writes a divider between menu buttons
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function divider()
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a divider.
		$bar->appendButton('Separator', 'divider');
	}

	/**
	 * Writes a custom option and task button for the button bar.
	 *
	 * @param   string  $task        The task to perform (picked up by the switch($task) blocks).
	 * @param   string  $icon        The image to display.
	 * @param   string  $iconOver    The image to display when moused over.
	 * @param   string  $alt         The alt text for the icon image.
	 * @param   bool    $listSelect  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Strip extension.
		$icon = preg_replace('#\.[^.]*$#', '', $icon);

		// Add a standard button.
		$bar->appendButton('Standard', $icon, $alt, $task, $listSelect);
	}

	/**
	 * Writes a preview button for a given option (opens a popup window).
	 *
	 * @param   string   $url            The name of the popup file (excluding the file extension)
	 * @param   bool     $updateEditors  Unused
	 * @param   string   $icon           The image to display.
	 * @param   integer  $bodyHeight     The body height of the preview popup
	 * @param   integer  $modalWidth     The modal width of the preview popup
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function preview($url = '', $updateEditors = false, $icon = 'preview', $bodyHeight = null, $modalWidth = null)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a preview button.
		$bar->appendButton('Popup', $icon, 'Preview', $url . '&task=preview', 640, 480, $bodyHeight, $modalWidth);
	}

	/**
	 * Writes a preview button for a given option (opens a popup window).
	 *
	 * @param   string  $ref        The name of the popup file (excluding the file extension for an xml file).
	 * @param   bool    $com        Use the help file in the component directory.
	 * @param   string  $override   Use this URL instead of any other
	 * @param   string  $component  Name of component to get Help (null for current component)
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function help($ref, $com = false, $override = null, $component = null)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a help button.
		$bar->appendButton('Help', $ref, $com, $override, $component);
	}

	/**
	 * Writes a cancel button that will go back to the previous page without doing
	 * any other operation.
	 *
	 * @param   string  $alt   Alternative text.
	 * @param   string  $href  URL of the href attribute.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function back($alt = 'JTOOLBAR_BACK', $href = 'javascript:history.back();')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a back button.
		$bar->appendButton('Link', 'back', $alt, $href);
	}

	/**
	 * Creates a button to redirect to a link
	 *
	 * @param   string  $url   The link url
	 * @param   string  $text  Button text
	 * @param   string  $name  Name to be used as apart of the id
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public static function link($url, $text, $name = 'link')
	{
		$bar = Toolbar::getInstance('toolbar');

		$bar->appendButton('Link', $name, $text, $url);
	}

	/**
	 * Writes a media_manager button.
	 *
	 * @param   string  $directory  The subdirectory to upload the media to.
	 * @param   string  $alt        An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function media_manager($directory = '', $alt = 'JTOOLBAR_UPLOAD')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an upload button.
		$bar->appendButton('Popup', 'upload', $alt, 'index.php?option=com_media&tmpl=component&task=popupUpload&folder=' . $directory, 800, 520);
	}

	/**
	 * Writes a common 'default' button for a record.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function makeDefault($task = 'default', $alt = 'JTOOLBAR_DEFAULT')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a default button.
		$bar->appendButton('Standard', 'default', $alt, $task, true);
	}

	/**
	 * Writes a common 'assign' button for a record.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function assign($task = 'assign', $alt = 'JTOOLBAR_ASSIGN')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an assign button.
		$bar->appendButton('Standard', 'assign', $alt, $task, true);
	}

	/**
	 * Writes the common 'new' icon for the button bar.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function addNew($task = 'add', $alt = 'JTOOLBAR_NEW', $check = false)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a new button.
		$bar->appendButton('Standard', 'new', $alt, $task, $check);
	}

	/**
	 * Writes a common 'publish' button.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function publish($task = 'publish', $alt = 'JTOOLBAR_PUBLISH', $check = false)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a publish button.
		$bar->appendButton('Standard', 'publish', $alt, $task, $check);
	}

	/**
	 * Writes a common 'publish' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function publishList($task = 'publish', $alt = 'JTOOLBAR_PUBLISH')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a publish button (list).
		$bar->appendButton('Standard', 'publish', $alt, $task, true);
	}

	/**
	 * Writes a common 'unpublish' button.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function unpublish($task = 'unpublish', $alt = 'JTOOLBAR_UNPUBLISH', $check = false)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an unpublish button
		$bar->appendButton('Standard', 'unpublish', $alt, $task, $check);
	}

	/**
	 * Writes a common 'unpublish' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function unpublishList($task = 'unpublish', $alt = 'JTOOLBAR_UNPUBLISH')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an unpublish button (list).
		$bar->appendButton('Standard', 'unpublish', $alt, $task, true);
	}

	/**
	 * Writes a common 'archive' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function archiveList($task = 'archive', $alt = 'JTOOLBAR_ARCHIVE')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an archive button.
		$bar->appendButton('Standard', 'archive', $alt, $task, true);
	}

	/**
	 * Writes an unarchive button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function unarchiveList($task = 'unarchive', $alt = 'JTOOLBAR_UNARCHIVE')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an unarchive button (list).
		$bar->appendButton('Standard', 'unarchive', $alt, $task, true);
	}

	/**
	 * Writes a common 'edit' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function editList($task = 'edit', $alt = 'JTOOLBAR_EDIT')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an edit button.
		$bar->appendButton('Standard', 'edit', $alt, $task, true);
	}

	/**
	 * Writes a common 'edit' button for a template html.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function editHtml($task = 'edit_source', $alt = 'JTOOLBAR_EDIT_HTML')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an edit html button.
		$bar->appendButton('Standard', 'edithtml', $alt, $task, true);
	}

	/**
	 * Writes a common 'edit' button for a template css.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function editCss($task = 'edit_css', $alt = 'JTOOLBAR_EDIT_CSS')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an edit css button (hide).
		$bar->appendButton('Standard', 'editcss', $alt, $task, true);
	}

	/**
	 * Writes a common 'delete' button for a list of records.
	 *
	 * @param   string  $msg   Postscript for the 'are you sure' message.
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function deleteList($msg = '', $task = 'remove', $alt = 'JTOOLBAR_DELETE')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a delete button.
		if ($msg)
		{
			$bar->appendButton('Confirm', $msg, 'delete', $alt, $task, true);
		}
		else
		{
			$bar->appendButton('Standard', 'delete', $alt, $task, true);
		}
	}

	/**
	 * Writes a common 'trash' button for a list of records.
	 *
	 * @param   string  $task   An override for the task.
	 * @param   string  $alt    An override for the alt text.
	 * @param   bool    $check  True to allow lists.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function trash($task = 'remove', $alt = 'JTOOLBAR_TRASH', $check = true)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a trash button.
		$bar->appendButton('Standard', 'trash', $alt, $task, $check, false);
	}

	/**
	 * Writes a save button for a given option.
	 * Apply operation leads to a save action only (does not leave edit mode).
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function apply($task = 'apply', $alt = 'JTOOLBAR_APPLY')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add an apply button
		$bar->appendButton('Standard', 'apply', $alt, $task, false);
	}

	/**
	 * Writes a save button for a given option.
	 * Save operation leads to a save and then close action.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $group  True or false
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function save($task = 'save', $alt = 'JTOOLBAR_SAVE', $group = false)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a save button.
		$bar->appendButton('Standard', 'save', $alt, $task, false, $group);
	}

	/**
	 * Writes a save and create new button for a given option.
	 * Save and create operation leads to a save and then add action.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $group  True or false
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function save2new($task = 'save2new', $alt = 'JTOOLBAR_SAVE_AND_NEW', $group = false)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a save and create new button.
		$bar->appendButton('Standard', 'save-new', $alt, $task, false, $group);
	}

	/**
	 * Writes a save as copy button for a given option.
	 * Save as copy operation leads to a save after clearing the key,
	 * then returns user to edit mode with new key.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $group  True or false
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function save2copy($task = 'save2copy', $alt = 'JTOOLBAR_SAVE_AS_COPY', $group = false)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a save and create new button.
		$bar->appendButton('Standard', 'save-copy', $alt, $task, false, $group);
	}

	/**
	 * Writes a checkin button for a given option.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.7
	 */
	public static function checkin($task = 'checkin', $alt = 'JTOOLBAR_CHECKIN', $check = true)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a save and create new button.
		$bar->appendButton('Standard', 'checkin', $alt, $task, $check);
	}

	/**
	 * Writes a cancel button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function cancel($task = 'cancel', $alt = 'JTOOLBAR_CANCEL')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a cancel button.
		$bar->appendButton('Standard', 'cancel', $alt, $task, false);
	}

	/**
	 * Writes a configuration button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string   $component  The name of the component, eg, com_content.
	 * @param   integer  $height     The height of the popup. [UNUSED]
	 * @param   integer  $width      The width of the popup. [UNUSED]
	 * @param   string   $alt        The name of the button.
	 * @param   string   $path       An alternative path for the configuation xml relative to JPATH_SITE.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function preferences($component, $height = '550', $width = '875', $alt = 'JToolbar_Options', $path = '')
	{
		$component = urlencode($component);
		$path = urlencode($path);
		$bar = Toolbar::getInstance('toolbar');

		$uri = (string) \JUri::getInstance();
		$return = urlencode(base64_encode($uri));

		// Add a button linking to config for component.
		$bar->appendButton(
			'Link',
			'options',
			$alt,
			'index.php?option=com_config&amp;view=component&amp;component=' . $component . '&amp;path=' . $path . '&amp;return=' . $return
		);
	}

	/**
	 * Writes a version history
	 *
	 * @param   string   $typeAlias  The component and type, for example 'com_content.article'
	 * @param   integer  $itemId     The id of the item, for example the article id.
	 * @param   integer  $height     The height of the popup.
	 * @param   integer  $width      The width of the popup.
	 * @param   string   $alt        The name of the button.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function versions($typeAlias, $itemId, $height = 800, $width = 500, $alt = 'JTOOLBAR_VERSIONS')
	{
		$lang = \JFactory::getLanguage();
		$lang->load('com_contenthistory', JPATH_ADMINISTRATOR, $lang->getTag(), true);

		/** @var \Joomla\CMS\Table\ContentType $contentTypeTable */
		$contentTypeTable = Table::getInstance('Contenttype');
		$typeId           = $contentTypeTable->getTypeId($typeAlias);

		// Options array for JLayout
		$options              = array();
		$options['title']     = \JText::_($alt);
		$options['height']    = $height;
		$options['width']     = $width;
		$options['itemId']    = $itemId;
		$options['typeId']    = $typeId;
		$options['typeAlias'] = $typeAlias;

		$bar    = Toolbar::getInstance('toolbar');
		$layout = new FileLayout('joomla.toolbar.versions');
		$bar->appendButton('Custom', $layout->render($options), 'versions');
	}

	/**
	 * Writes a save button for a given option, with an additional dropdown
	 *
	 * @param   array   $buttons  An array of buttons
	 * @param   string  $class    The button class
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public static function saveGroup($buttons = array(), $class = 'btn-success')
	{
		$validOptions = array(
			'apply'     => 'JTOOLBAR_APPLY',
			'save'      => 'JTOOLBAR_SAVE',
			'save2new'  => 'JTOOLBAR_SAVE_AND_NEW',
			'save2copy' => 'JTOOLBAR_SAVE_AS_COPY'
		);

		$bar = Toolbar::getInstance('toolbar');

		$saveGroup = $bar->dropdownButton('save-group');

		$saveGroup->configure(
			function (Toolbar $childBar) use ($buttons, $validOptions)
			{
				foreach ($buttons as $button)
				{
					if (!array_key_exists($button[0], $validOptions))
					{
						continue;
					}

					$options['group'] = true;
					$altText = $button[2] ?? $validOptions[$button[0]];

					$childBar->{$button[0]}($button[1])
						->text($altText);
				}
			}
		);
	}

	/**
	 * Displays a modal button
	 *
	 * @param   string  $targetModalId  ID of the target modal box
	 * @param   string  $icon           Icon class to show on modal button
	 * @param   string  $alt            Title for the modal button
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function modal($targetModalId, $icon, $alt)
	{
		$title = \JText::_($alt);

		$dhtml = '<button data-toggle="modal" data-target="#' . $targetModalId . '" class="btn btn-primary">
			<span class="' . $icon . '" title="' . $title . '"></span> ' . $title . '</button>';

		$bar = Toolbar::getInstance('toolbar');
		$bar->appendButton('Custom', $dhtml, $alt);
	}
}
