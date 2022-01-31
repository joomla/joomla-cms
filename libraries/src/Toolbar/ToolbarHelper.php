<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

\defined('_JEXEC') or die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Throwable;

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

		$app = Factory::getApplication();
		$app->JComponentTitle = $html;
		$title = strip_tags($title) . ' - ' . $app->get('sitename');

		if ($app->isClient('administrator'))
		{
			$title .= ' - ' . Text::_('JADMINISTRATION');
		}

		Factory::getDocument()->setTitle($title);
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
	 * @param   string  $iconOver    @deprecated 5.0
	 * @param   string  $alt         The alt text for the icon image.
	 * @param   bool    $listSelect  True if required to check that a standard list item is checked.
	 * @param   string  $formId      The id of action form.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true, $formId = null)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Strip extension.
		$icon = preg_replace('#\.[^.]*$#', '', $icon);

		// Add a standard button.
		$bar->appendButton('Standard', $icon, $alt, $task, $listSelect, $formId);
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
	 * Writes a jooa11y accessibility checker button for a given option (opens a popup window).
	 *
	 * @param   string   $url            The url to open
	 * @param   bool     $updateEditors  Unused
	 * @param   string   $icon           The image to display.
	 * @param   integer  $bodyHeight     The body height of the preview popup
	 * @param   integer  $modalWidth     The modal width of the preview popup
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	public static function jooa11y($url = '', $updateEditors = false, $icon = 'icon-universal-access', $bodyHeight = null, $modalWidth = null)
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a button.
		$bar->appendButton('Popup', $icon, 'Preview', $url . '&task=preview', 640, 480, $bodyHeight, $modalWidth);
	}

	/**
	 * Writes a help button for a given option (opens a popup window).
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
		// Don't show a help button if neither $ref nor $override is given
		if (!$ref && !$override)
		{
			return;
		}

		$bar = Toolbar::getInstance('toolbar');

		// Add a help button.
		$bar->appendButton('Help', $ref, $com, $override, $component);
	}

	/**
	 * Writes a help button for showing/hiding the inline help of a form
	 *
	 * @param   string  $class   The class used by the inline help items.
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	public static function inlinehelp(string $class = "hide-aware-inline-help")
	{
		/** @var HtmlDocument $doc */
		try
		{
			$doc = Factory::getApplication()->getDocument();

			if (!($doc instanceof HtmlDocument))
			{
				return;
			}

			$doc->getWebAssetManager()->useScript('inlinehelp');
		}
		catch (Throwable $e)
		{
			return;
		}

		$bar = Toolbar::getInstance('toolbar');

		// Add a help button.
		$bar->inlinehelpButton('inlinehelp')
			->targetclass($class)
			->icon('fa fa-question-circle');
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
		$arrow  = Factory::getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';
		$bar->appendButton('Link', $arrow, $alt, $href);
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
		$bar->apply($task, $alt);
	}

	/**
	 * Writes a save button for a given option.
	 * Save operation leads to a save and then close action.
	 *
	 * @param   string   $task  An override for the task.
	 * @param   string   $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function save($task = 'save', $alt = 'JTOOLBAR_SAVE')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a save button.
		$bar->save($task, $alt);
	}

	/**
	 * Writes a save and create new button for a given option.
	 * Save and create operation leads to a save and then add action.
	 *
	 * @param   string   $task  An override for the task.
	 * @param   string   $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function save2new($task = 'save2new', $alt = 'JTOOLBAR_SAVE_AND_NEW')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a save and create new button.
		$bar->save2new($task, $alt);
	}

	/**
	 * Writes a save as copy button for a given option.
	 * Save as copy operation leads to a save after clearing the key,
	 * then returns user to edit mode with new key.
	 *
	 * @param   string   $task  An override for the task.
	 * @param   string   $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function save2copy($task = 'save2copy', $alt = 'JTOOLBAR_SAVE_AS_COPY')
	{
		$bar = Toolbar::getInstance('toolbar');

		// Add a save and create new button.
		$bar->save2copy($task, $alt);
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
	 * @param   string   $path       An alternative path for the configuration xml relative to JPATH_SITE.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function preferences($component, $height = 550, $width = 875, $alt = 'JTOOLBAR_OPTIONS', $path = '')
	{
		$component = urlencode($component);
		$path = urlencode($path);
		$bar = Toolbar::getInstance('toolbar');

		$uri = (string) Uri::getInstance();
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
		$lang = Factory::getLanguage();
		$lang->load('com_contenthistory', JPATH_ADMINISTRATOR, $lang->getTag(), true);

		/** @var \Joomla\CMS\Table\ContentType $contentTypeTable */
		$contentTypeTable = Table::getInstance('Contenttype');
		$typeId           = $contentTypeTable->getTypeId($typeAlias);

		// Options array for Layout
		$options              = array();
		$options['title']     = Text::_($alt);
		$options['height']    = $height;
		$options['width']     = $width;
		$options['itemId']    = $typeAlias . '.' . $itemId;

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
					if (!\array_key_exists($button[0], $validOptions))
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
	 * @param   string  $class          The button class
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function modal($targetModalId, $icon, $alt, $class = 'btn-primary')
	{
		$title = Text::_($alt);

		$dhtml = '<joomla-toolbar-button><button data-bs-toggle="modal" data-bs-target="#' . $targetModalId . '" class="btn ' . $class . '">
			<span class="' . $icon . ' icon-fw" title="' . $title . '"></span> ' . $title . '</button></joomla-toolbar-button>';

		$bar = Toolbar::getInstance('toolbar');
		$bar->appendButton('Custom', $dhtml, $alt);
	}
}
