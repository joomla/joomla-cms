<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Utility class for the button bar
* @package Joomla
*/
class mosToolBar {

	/**
	* Writes the start of the button bar table
	*/
	function startTable() {
		global $mainframe;
		
		/*
		* Initialize some variables
		*/
		$document = & $mainframe->getDocument();
		
		/*
		* load toolbar css
		*/
		$document->addStyleSheet( 'templates/_system/css/toolbar.css' );		
		?>
		<table cellpadding="0" cellspacing="3" border="0" id="toolbar">
		<tr valign="middle" align="center">
		<?php
	}

	/**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
	function custom( $task='', $icon=NULL, $iconOver='', $alt='', $listSelect=true ) {
		
		$icon 	= ( $iconOver ? $iconOver : $icon );
		$image 	= mosAdminMenus::ImageCheck( $icon, '/images/', NULL, NULL, $alt, $task, 1 );
		
		$href 	= explode('index.php?', $_SERVER['REQUEST_URI'] );
		$href 	= 'index.php?'. ampReplace( $href[1] ) .'#';
		
		if ($listSelect) {
			$onclick = "javascript:if (document.adminForm.boxchecked.value == 0){ alert('Please make a selection from the list to $alt');}else{submitbutton('$task')}";
		} else {
			$onclick = "javascript:submitbutton('$task')";
		}
		?>
		<td>
			<a class="toolbar" href="<?php echo $href;?>" onclick="<?php echo $onclick ;?>">
				<?php echo $image; ?></a>
		</td>
		<?php
	}

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function addNew( $task='new', $alt='New' ) {
		$alt= JText::_( $alt );

		mosToolBar::custom( $task, 'new_f2.png', '', $alt, false );
	}

	/**
	* Writes a common 'publish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publish( $task='publish', $alt='Published' ) {
 		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'publish_f2.png', '', $alt, false );
	}

	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publishList( $task='publish', $alt='Published' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'publish_f2.png', '', $alt, true );
	}

	/**
	* Writes a common 'unpublish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublish( $task='unpublish', $alt='Unpublished' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'unpublish_f2.png', '', $alt, false );
	}

	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublishList( $task='unpublish', $alt='Unpublished' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'unpublish_f2.png', '', $alt, true );
	}

	/**
	* Writes a common 'archive' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function archiveList( $task='archive', $alt='Archived' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'archive_f2.png', '', $alt, true );
	}

	/**
	* Writes an unarchive button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unarchiveList( $task='unarchive', $alt='Unarchive' ) {
        $alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'unarchive_f2.png', '', $alt, true );
	}

	/**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function editList( $task='edit', $alt='Edit' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'edit_f2.png', '', $alt, true );
	}

	/**
	* Writes a common 'edit' button for a template html
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function editHtml( $task='edit_source', $alt='Edit HTML' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'edit_f2.png', '', $alt, true );
	}

	/**
	* Writes a common 'edit' button for a template css
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function editCss( $task='edit_css', $alt='Edit CSS' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'css_f2.png', '', $alt, true );
	}

	/**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function deleteList( $msg='', $task='remove', $alt='Delete' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'delete_f2.png', '', $alt, true );
	}

	/**
	* Writes a save button for a given option
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function save( $task='save', $alt='Save' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'save_f2.png', '', $alt, false );
	}

	/**
	* Writes a save button for a given option
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function apply( $task='apply', $alt='Apply' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'apply_f2.png', '', $alt, false );
	}

	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function cancel( $task='cancel', $alt='Cancel' ) {
		$alt= JText::_( $alt );
		
		mosToolBar::custom( $task, 'cancel_f2.png', '', $alt, false );
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension)
	*/
	function preview( $popup='' ) {
		global $database;
		
		$sql = "SELECT template"
		. "\n FROM #__templates_menu"
		. "\n WHERE client_id = 0"
		. "\n AND menuid = 0";
		$database->setQuery( $sql );
		$cur_template = $database->loadResult();
		
		$alt	= JText::_( 'Preview' );
		$image 	= mosAdminMenus::ImageCheck( 'preview_f2.png', 'images/', NULL, NULL, $alt, 'preview', 1 );
		?>
		<td>
			<a class="toolbar" href="#" onclick="window.open('popups/<?php echo $popup;?>.php?t=<?php echo $cur_template; ?>', 'win1', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');" >
				<?php echo $image; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a cancel button that will go back to the previous page without doing
	* any other operation
	*/
	function back() {
        $alt= JText::_( 'back' );
		$image = mosAdminMenus::ImageCheck( 'back_f2.png', '/images/', NULL, NULL, $alt, 'cancel', 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:window.history.back();" >
				<?php echo $image;?></a>
		</td>
		<?php
	}

	/**
	* Write a divider between menu buttons
	*/
	function divider() {
		$image = mosAdminMenus::ImageCheck( 'menu_divider.png', '/images/' );
		?>
		<td>
			<?php echo $image; ?>
		</td>
		<?php
	}

	/**
	* Writes a media_manager button
	* @param string The sub-drectory to upload the media to
	*/
	function media_manager( $directory = '' ) {
        $alt= JText::_( 'Upload Image' );
		$image = mosAdminMenus::ImageCheck( 'upload_f2.png', '/images/', NULL, NULL, $alt, 'uploadPic', 1 );
		?>
		<td>
			<a class="toolbar" onclick="popupWindow('popups/uploadimage.php?directory=<?php echo $directory; ?>','win1',250,100,'no');">
				<?php echo $image; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a spacer cell
	* @param string The width for the cell
	*/
	function spacer( $width='' ) {
		if ($width != '') {
			?>
			<td width="<?php echo $width;?>">&nbsp;</td>
			<?php
		} else {
			?>
			<td>&nbsp;</td>
			<?php
		}
	}

	/**
	* Writes the end of the menu bar table
	*/
	function endTable() {
		?>
		</tr>
		</table>
		<?php
	}
}
?>