<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.presentation.toolbar.toolbar');

/**
* Utility class for the button bar
*
* @package Joomla
*/
class JMenuBar
{

	/**
	* Title cell
	* For the title and toolbar to be rendered correctly,
	* this title fucntion must be called before the starttable function and the toolbars icons
	* this is due to the nature of how the css has been used to postion the title in respect to the toolbar
	* @param string The title
	* @param string The image.  If starting with / it using the component image directory
	* @param string
	* @since 1.5
	*/
	function title($title, $icon = 'generic.png')
	{
		global $mainframe;
		$mainframe->set('JComponentTitle', mosHTML::Header( JText::_($title), $icon ));
	}

	/**
	* @deprecated As of Version 1.1
	*/
	function startTable()
	{
		return;
	}

	/**
	* @deprecated As of Version 1.1
	*/
	function endTable()
	{
		return;
	}

	/**
	* Writes a spacer cell
	* @param string The width for the cell
	* @since 1.0
	*/
	function spacer($width = '')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a spacer
		$bar->appendButton( 'Separator', 'spacer', $width );
	}

	/**
	* Write a divider between menu buttons
	* @since 1.0
	*/
	function divider()
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a divider
		$bar->appendButton( 'Separator', 'divider' );
	}

	/**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	* @param boolean True if required to include callinh hideMainMenu()
	* @since 1.0
	*/
	function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true, $x = false)
	{
		$bar = & JToolBar::getInstance('JComponent');
		
		//strip extension
		$icon	= preg_replace('#\.[^.]*$#', '', $icon);
		
		// Add a standard button
		$bar->appendButton( 'Standard', $icon, $alt, $task, $listSelect, $x );
	}

	/**
	* Writes a custom option and task button for the button bar.
	* Extended version of custom() calling hideMainMenu() before submitbutton().
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	* @since 1.0
		* (NOTE this is being deprecated)
	*/
	function customX($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar = & JToolBar::getInstance('JComponent');
		
		//strip extension
		$icon	= preg_replace('#\.[^.]*$#', '', $icon);
		
		// Add a standard button
		$bar->appendButton( 'Standard', $icon, $alt, $task, $listSelect, true );
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension)
	* @since 1.0
	*/
	function preview($url = '', $updateEditors = false)
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a preview button
		$bar->appendButton( 'Popup', 'preview', 'Preview', "$url&task=preview" );
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension for an xml file)
	* @param boolean Use the help file in the component directory
	* @since 1.0
	*/
	function help($ref, $com = false)
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a help button
		$bar->appendButton( 'Help', $ref, $com );
	}

	/**
	* Writes a cancel button that will go back to the previous page without doing
	* any other operation
	* @since 1.0
	*/
	function back($alt = 'Back', $href = 'javascript:history.back();')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a back button
		$bar->appendButton( 'Link', 'back', $alt, $href );
	}

	/**
	* Writes a media_manager button
	* @param string The sub-drectory to upload the media to
	* @since 1.0
	*/
	function media_manager($directory = '', $alt = 'Upload')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add an upload button
		$bar->appendButton( 'Popup', 'upload', $alt, "index3.php?option=com_media&task=popupUpload&directory=$directory", 550, 200 );
	}

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function addNew($task = 'new', $alt = 'New')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a new button
		$bar->appendButton( 'Standard', 'new', $alt, $task, false, false );
	}

	/**
	* Writes the common 'new' icon for the button bar.
	* Extended version of addNew() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function addNewX($task = 'new', $alt = 'New')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a new button (hide menu)
		$bar->appendButton( 'Standard', 'new', $alt, $task, false, true );
	}

	/**
	* Writes a common 'publish' button
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function publish($task = 'publish', $alt = 'Publish')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a publish button
		$bar->appendButton( 'Publish', false, $alt, $task );
		$bar->appendButton( 'Standard', 'publish', $alt, $task, false, false );
	}

	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function publishList($task = 'publish', $alt = 'Publish')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a publish button (list)
		$bar->appendButton( 'Standard', 'publish', $alt, $task, true, false );
	}

	/**
	* Writes a common 'default' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function makeDefault($task = 'default', $alt = 'Default')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a default button
		$bar->appendButton( 'Standard', 'default', $alt, $task, true, false );
	}

	/**
	* Writes a common 'assign' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function assign($task = 'assign', $alt = 'Assign')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add an assign button
		$bar->appendButton( 'Standard', 'assign', $alt, $task, true, false );
	}

	/**
	* Writes a common 'unpublish' button
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unpublish($task = 'unpublish', $alt = 'Unpublish')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add an unpublish button
		$bar->appendButton( 'Standard', 'unpublish', $alt, $task, false, false );
	}

	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unpublishList($task = 'unpublish', $alt = 'Unpublish')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add an unpublish button (list)
		$bar->appendButton( 'Standard', 'unpublish', $alt, $task, true, false );
	}

	/**
	* Writes a common 'archive' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function archiveList($task = 'archive', $alt = 'Archive')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add an archive button
		$bar->appendButton( 'Standard', 'archive', $alt, $task, true, false );
	}

	/**
	* Writes an unarchive button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unarchiveList($task = 'unarchive', $alt = 'Unarchive')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add an unarchive button (list)
		$bar->appendButton( 'Standard', 'unarchive', $alt, $task, true, false );
	}

	/**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editList($task = 'edit', $alt = 'Edit')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add an edit button
		$bar->appendButton( 'Standard', 'edit', $alt, $task, true, false );
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
		$bar = & JToolBar::getInstance('JComponent');
		// Add an edit button (hide)
		$bar->appendButton( 'Standard', 'edit', $alt, $task, true, true );
	}

	/**
	* Writes a common 'edit' button for a template html
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editHtml($task = 'edit_source', $alt = 'Edit HTML')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add an edit html button
		$bar->appendButton( 'Standard', 'edithtml', $alt, $task, true, false );
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
		$bar = & JToolBar::getInstance('JComponent');
		// Add an edit html button (hide)
		$bar->appendButton( 'Standard', 'edithtml', $alt, $task, true, true );
	}

	/**
	* Writes a common 'edit' button for a template css
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editCss($task = 'edit_css', $alt = 'Edit CSS')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add an edit css button (hide)
		$bar->appendButton( 'Standard', 'editcss', $alt, $task, true, false );
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
		$bar = & JToolBar::getInstance('JComponent');
		// Add an edit css button (hide)
		$bar->appendButton( 'Standard', 'editcss', $alt, $task, true, true );
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
		$bar = & JToolBar::getInstance('JComponent');
		// Add a delete button
		$bar->appendButton( 'Standard', 'delete', $alt, $task, true, false );
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
		$bar = & JToolBar::getInstance('JComponent');
		// Add a delete button (hide)
		$bar->appendButton( 'Standard', 'delete', $alt, $task, true, true );
	}

	/**
	* Write a trash button that will move items to Trash Manager
	* @since 1.0
	*/
	function trash($task = 'remove', $alt = 'Trash', $check = true)
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a trash button
		$bar->appendButton( 'Standard', 'trash', $alt, $task, $check, false );
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
		$bar = & JToolBar::getInstance('JComponent');
		// Add an apply button
		$bar->appendButton( 'Standard', 'apply', $alt, $task, false, false );
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
		$bar = & JToolBar::getInstance('JComponent');
		// Add a save button
		$bar->appendButton( 'Standard', 'save', $alt, $task, false, false );
	}

	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function cancel($task = 'cancel', $alt = 'Cancel')
	{
		$bar = & JToolBar::getInstance('JComponent');
		// Add a cancel button
		$bar->appendButton( 'Standard', 'cancel', $alt, $task, false, false );
	}
}

/**
 * Legacy class, use JMenuBar instead
 * @deprecated As of version 1.1
 */
class mosMenuBar extends JMenuBar { }
?>