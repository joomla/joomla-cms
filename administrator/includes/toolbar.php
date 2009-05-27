<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.html.toolbar');

/**
* Utility class for the button bar
*
 * @package		Joomla.Administrator
 */
class JToolBarHelper
{

	/**
	* Title cell
	* For the title and toolbar to be rendered correctly,
	* this title fucntion must be called before the starttable function and the toolbars icons
	* this is due to the nature of how the css has been used to postion the title in respect to the toolbar
	* @param string The title
	* @param string The name of the image
	* @since 1.5
	*/
	function title($title, $icon = 'generic.png')
	{
		global $mainframe;

		//strip the extension
		$icon	= preg_replace('#\.[^.]*$#', '', $icon);

		$html  = "<div class=\"header icon-48-$icon\">\n";
		$html .= "$title\n";
		$html .= "</div>\n";

		$mainframe->set('JComponentTitle', $html);
	}

	/**
	* Writes a spacer cell
	* @param string The width for the cell
	* @since 1.0
	*/
	function spacer($width = '')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a spacer
		$bar->appendButton('Separator', 'spacer', $width);
	}

	/**
	* Write a divider between menu buttons
	* @since 1.0
	*/
	function divider()
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a divider
		$bar->appendButton('Separator', 'divider');
	}

	/**
	* Writes a custom option and task button for the button bar
	*
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	* @since 1.0
	*/
	function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar = & JToolBar::getInstance('toolbar');

		//strip extension
		$icon	= preg_replace('#\.[^.]*$#', '', $icon);

		// Add a standard button
		$bar->appendButton('Standard', $icon, $alt, $task, $listSelect);
	}

	/**
	* Writes a custom option and task button for the button bar.
	*
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	* @since 1.0
	*/
	function customX($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar = & JToolBar::getInstance('toolbar');

		//strip extension
		$icon	= preg_replace('#\.[^.]*$#', '', $icon);

		// Add a standard button
		$bar->appendButton('Standard', $icon, $alt, $task, $listSelect);
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension)
	* @since 1.0
	*/
	function preview($url = '', $updateEditors = false)
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a preview button
		$bar->appendButton('Popup', 'preview', 'Preview', "$url&task=preview");
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension for an xml file)
	* @param boolean Use the help file in the component directory
	* @since 1.0
	*/
	function help($ref, $com = false)
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a help button
		$bar->appendButton('Help', $ref, $com);
	}

	/**
	* Writes a cancel button that will go back to the previous page without doing
	* any other operation
	* @since 1.0
	*/
	function back($alt = 'Back', $href = 'javascript:history.back();')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a back button
		$bar->appendButton('Link', 'back', $alt, $href);
	}

	/**
	* Writes a media_manager button
	* @param string The sub-drectory to upload the media to
	* @since 1.0
	*/
	function media_manager($directory = '', $alt = 'Upload')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an upload button
		$bar->appendButton('Popup', 'upload', $alt, "index.php?option=com_media&tmpl=component&task=popupUpload&directory=$directory", 640, 520);
	}

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function addNew($task = 'add', $alt = 'New')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a new button
		$bar->appendButton('Standard', 'new', $alt, $task, false, false);
	}

	/**
	* Writes the common 'new' icon for the button bar.
	* Extended version of addNew() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function addNewX($task = 'add', $alt = 'New')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a new button (hide menu)
		$bar->appendButton('Standard', 'new', $alt, $task, false, true);
	}

	/**
	* Writes a common 'publish' button
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function publish($task = 'publish', $alt = 'Publish')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a publish button
		//$bar->appendButton('Publish', false, $alt, $task);
		$bar->appendButton('Standard', 'publish', $alt, $task, false, false);
	}

	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function publishList($task = 'publish', $alt = 'Publish')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a publish button (list)
		$bar->appendButton('Standard', 'publish', $alt, $task, true, false);
	}

	/**
	* Writes a common 'default' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function makeDefault($task = 'default', $alt = 'Default')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a default button
		$bar->appendButton('Standard', 'default', $alt, $task, true, false);
	}

	/**
	* Writes a common 'assign' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function assign($task = 'assign', $alt = 'Assign')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an assign button
		$bar->appendButton('Standard', 'assign', $alt, $task, true, false);
	}

	/**
	* Writes a common 'unpublish' button
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unpublish($task = 'unpublish', $alt = 'Unpublish')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an unpublish button
		$bar->appendButton('Standard', 'unpublish', $alt, $task, false, false);
	}

	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unpublishList($task = 'unpublish', $alt = 'Unpublish')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an unpublish button (list)

		$bar->appendButton('Standard', 'unpublish', $alt, $task, true, false);
	}

	/**
	* Writes a common 'archive' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function archiveList($task = 'archive', $alt = 'Archive')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an archive button
		$bar->appendButton('Standard', 'archive', $alt, $task, true, false);
	}

	/**
	* Writes an unarchive button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unarchiveList($task = 'unarchive', $alt = 'Unarchive')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an unarchive button (list)
		$bar->appendButton('Standard', 'unarchive', $alt, $task, true, false);
	}

	/**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editList($task = 'edit', $alt = 'Edit')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit button
		$bar->appendButton('Standard', 'edit', $alt, $task, true, false);
	}

	/**
	* Writes a common 'edit' button for a list of records.
	* Extended version of editList() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editListX($task = 'edit', $alt = 'Edit')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit button (hide)
		$bar->appendButton('Standard', 'edit', $alt, $task, true, true);
	}

	/**
	* Writes a common 'edit' button for a template html
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editHtml($task = 'edit_source', $alt = 'Edit HTML')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit html button
		$bar->appendButton('Standard', 'edithtml', $alt, $task, true, false);
	}

	/**
	* Writes a common 'edit' button for a template html.
	* Extended version of editHtml() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editHtmlX($task = 'edit_source', $alt = 'Edit HTML')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit html button (hide)
		$bar->appendButton('Standard', 'edithtml', $alt, $task, true, true);
	}

	/**
	* Writes a common 'edit' button for a template css
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editCss($task = 'edit_css', $alt = 'Edit CSS')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit css button (hide)
		$bar->appendButton('Standard', 'editcss', $alt, $task, true, false);
	}

	/**
	* Writes a common 'edit' button for a template css.
	* Extended version of editCss() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editCssX($task = 'edit_css', $alt = 'Edit CSS')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit css button (hide)
		$bar->appendButton('Standard', 'editcss', $alt, $task, true, true);
	}

	/**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function deleteList($msg = '', $task = 'remove', $alt = 'Delete')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a delete button
		if ($msg) {
			$bar->appendButton('Confirm', $msg, 'delete', $alt, $task, true, false);
		} else {
			$bar->appendButton('Standard', 'delete', $alt, $task, true, false);
		}
	}

	/**
	* Writes a common 'delete' button for a list of records.
	* Extended version of deleteList() calling hideMainMenu() before submitbutton().
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function deleteListX($msg = '', $task = 'remove', $alt = 'Delete')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a delete button (hide)
		if ($msg) {
			$bar->appendButton('Confirm', $msg, 'delete', $alt, $task, true, true);
		} else {
			$bar->appendButton('Standard', 'delete', $alt, $task, true, true);
		}
	}

	/**
	* Write a trash button that will move items to Trash Manager
	* @since 1.0
	*/
	function trash($task = 'remove', $alt = 'Trash', $check = true)
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a trash button
		$bar->appendButton('Standard', 'trash', $alt, $task, $check, false);
	}

	/**
	* Writes a save button for a given option
	* Apply operation leads to a save action only (does not leave edit mode)
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function apply($task = 'apply', $alt = 'Apply')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an apply button
		$bar->appendButton('Standard', 'apply', $alt, $task, false, false);
	}

	/**
	* Writes a save button for a given option
	* Save operation leads to a save and then close action
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function save($task = 'save', $alt = 'Save')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a save button
		$bar->appendButton('Standard', 'save', $alt, $task, false, false);
	}

	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function cancel($task = 'cancel', $alt = 'Cancel')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a cancel button
		$bar->appendButton('Standard', 'cancel', $alt, $task, false, false);
	}

	/**
	* Writes a configuration button and invokes a cancel operation (eg a checkin)
	* @param	string	The name of the component, eg, com_content
	* @param	int		The height of the popup
	* @param	int		The width of the popup
	* @param	string	The name of the button
	* @param	string	An alternative path for the configuation xml relative to JPATH_SITE
	* @since 1.0
	*/
	function preferences($component, $height='150', $width='570', $alt = 'Preferences', $path = '')
	{
		$user = &JFactory::getUser();
		if (!$user->authorise('core.config.manage')) {
			return;
		}

		$component	= urlencode($component);
		$path		= urlencode($path);
		$bar = & JToolBar::getInstance('toolbar');
		// Add a configuration button
		$bar->appendButton('Popup', 'config', $alt, 'index.php?option=com_config&amp;controller=component&amp;component='.$component.'&amp;path='.$path, $width, $height);
	}
}

/**
* Utility class for the submenu
*
 * @package		Joomla.Administrator
 */
class JSubMenuHelper
{
	function addEntry($name, $link = '', $active = false)
	{
		$menu = &JToolBar::getInstance('submenu');
		$menu->appendButton($name, $link, $active);
	}
}
?>
