<?php
/**
* @version $Id: HTML_toolbar.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
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
		?>
		<table cellpadding="3" cellspacing="0" border="0">
		<tr>
		<?php
	}

	/**
	* Writes a spacer cell
	* @param string The width for the cell
	*/
	function spacer( $width='' ) {
		if ($width != '') {
			?>
			<td width="<?php echo $width;?>">
				&nbsp;
			</td>
			<?php
		} else {
			?>
			<td>
				&nbsp;
			</td>
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

	/**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
	function custom( $task='', $icon='', $iconOver='', $alt='', $listSelect=true ) {
   		global $_LANG;

		$image 	= mosAdminMenus::ImageCheck( $icon, '/images/', NULL, NULL, $alt, $task );
		$image2 = mosAdminMenus::ImageCheck( $iconOver, '/images/', NULL, NULL, $alt, $task, 0 );
		if ($listSelect) {
			$href = "javascript:if (document.adminForm.boxchecked.value == 0){ alert('". $_LANG->_( 'Please make a selection from the list to' ) ." ". $alt ."');}else{submitbutton('$task')}";
		} else {
			$href = "javascript:submitbutton('$task')";
		}
		if ($icon && $iconOver) {
			?>
			<td width="25" align="center">
				<a class="toolbar" href="#" onclick="<?php echo $href;?>" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('<?php echo $task;?>','','<?php echo $image2;?>',1);">
					<?php echo $image . ' ' . $_LANG->_( $alt ); ?></a>
			</td>
			<?php
		} else {
			?>
			<td width="25" align="center">
				<a class="toolbar" href="<?php echo $href;?>">
					<?php echo $_LANG->_( $alt ); ?></a>
			</td>
			<?php
		}
	}

	/**
	* Writes a cancel button that will go back to the previous page without doing
	* any other operation
	*/
	function back() {
		$image = mosAdminMenus::ImageCheck( 'back.png', '/images/', NULL, NULL, 'back', 'cancel' );
		$image2 = mosAdminMenus::ImageCheck( 'back_f2.png', '/images/', NULL, NULL, 'back', 'cancel', 0 );
		?>
		<td width="25" align="center">
			<a href="javascript:window.history.back();" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('cancel','','images/<?php echo $image2;?>',1);">
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
		<td width="25" align="center">
			<?php echo $image; ?>
		</td>
		<?php
	}

	/**
	* Writes a media_manager button
	* @param string The sub-drectory to upload the media to
	*/
	function media_manager( $directory = '' ) {
		$image = mosAdminMenus::ImageCheck( 'upload.png', '/images/', NULL, NULL, 'Upload Image', 'uploadPic' );
		$image2 = mosAdminMenus::ImageCheck( 'upload_f2.png', '/images/', NULL, NULL, 'Upload Image', 'uploadPic', 0 );
		?>
		<td width="25" align="center">
			<a href="#" onclick="popupWindow('popups/uploadimage.php?directory=<?php echo $directory; ?>','win1',250,100,'no');" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('uploadPic','','<?php echo $image2; ?>',1);">
				<?php echo $image; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function deleteList( $msg='', $task='remove', $alt='Delete' ) {
		// trick trawler $_LANG->_( 'Delete' );
		global $_LANG;
		$image = mosAdminMenus::ImageCheck( 'delete.png', '/images/', NULL, NULL, $alt, $task );
		$image2 = mosAdminMenus::ImageCheck( 'delete_f2.png', '/images/', NULL, NULL, $alt, $task, 0 );
		?>
		<td width="25" align="center">
			<a class="toolbar" href="#" onclick="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo $_LANG->_( 'Please make a selection from the list to' ) ." ". $alt; ?>'); } else if (confirm('<?php echo $_LANG->_( 'validDeleteItems' ); ?> <?php echo $msg;?>')){ submitbutton('<?php echo $task;?>');}" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('<?php echo $task;?>','','<?php echo $image2; ?>',1);">
				<?php echo $image . ' ' . $_LANG->_( $alt ); ?></a>
		</td>
		<?php
	}

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function addNew( $task='new', $alt='New' ) {
		// trick trawler $_LANG->_( 'New' );
		mosToolBar::custom( $task, 'new.png', 'new_f2.png', $alt, false );
	}

	/**
	* Writes a common 'publish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publish( $task='publish', $alt='Publish' ) {
		// trick trawler $_LANG->_( 'Publish' );
		mosToolBar::custom( $task, 'publish.png', 'publish_f2.png', $alt, false );
	}

	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publishList( $task='publish', $alt='Published' ) {
		mosToolBar::custom( $task, 'publish.png', 'publish_f2.png', $alt, true );
	}

	/**
	* Writes a common 'unpublish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublish( $task='unpublish', $alt='Unpublish' ) {
		// trick trawler $_LANG->_( 'Unpublish' );
		mosToolBar::custom( $task, 'unpublish.png', 'unpublish_f2.png', $alt, false );
	}

	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublishList( $task='unpublish', $alt='Unpublish' ) {
		// trick trawler $_LANG->_( 'Unpublish' );
		mosToolBar::custom( $task, 'unpublish.png', 'unpublish_f2.png', $alt, true );
	}

	/**
	* Writes a common 'archive' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function archiveList( $task='archive', $alt='Archive' ) {
		// trick trawler $_LANG->_( 'archive' );
		mosToolBar::custom( $task, 'archive.png', 'archive_f2.png', $alt, true );
	}

	/**
	* Writes an unarchive button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unarchiveList( $task='unarchive', $alt='Unarchive' ) {
		// trick trawler $_LANG->_( 'Unarchive' );
		mosToolBar::custom( $task, 'unarchive.png', 'unarchive_f2.png', $alt, true );
	}

	/**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function editList( $task='edit', $alt='_E_EDIT' ) {
		// trick trawler $_LANG->_( 'Edit' );
		mosToolBar::custom( $task, 'edit.png', 'edit_f2.png', $alt, true );
	}

	/**
	* Writes a save button for a given option
	* Apply operation leads to a save action only (does not leave edit mode)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function apply( $task='apply', $alt='Apply' ) {
		// trick trawler $_LANG->_( 'Apply' );
		mosToolBar::custom( $task, 'apply.png', 'apply_f2.png', $alt, false );
	}

	/**
	* Writes a save button for a given option
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function save( $task='save', $alt='Save' ) {
		// trick trawler $_LANG->_( 'Save' );
		mosToolBar::custom( $task, 'save.png', 'save_f2.png', $alt, false );
	}

	/**
	 * @deprecated
	 */
	function savenew( $task='savenew', $alt='Save' ) {
		mosToolBar::custom( $task, 'save.png', 'save_f2.png', $alt, false );
	}

	/**
	 * @deprecated
	 */
	function saveedit( $task='saveedit', $alt='Save' ) {
		mosToolBar::custom( $task, 'save.png', 'save_f2.png', $alt, false );
	}

	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function cancel( $task='cancel', $alt='Cancel' ) {
		// trick trawler $_LANG->_( 'Cancel' );
		mosToolBar::custom( $task, 'cancel.png', 'cancel_f2.png', $alt, false );
	}
}

/**
* Utility class for the button bar
* @package Joomla
*/
class mosToolBar_return {
	/**
	* Writes the start of the button bar table
	*/
	function startTable() {
		$output = '
		<div class="fronttoolbar">'
		;

		return $output;
	}

	/**
	* Writes a spacer cell
	* @param string The width for the cell
	*/
	function spacer( $width='' ) {
		if ( $width ) {
			$output = '
			<div style="width: '. $width .'">
				&nbsp;
			</div>'
			;
		} else {
			$output = '
			<div class="spacer">
				&nbsp;
			</div>'
			;
		}

		return $output;
	}

	/**
	* Writes the end of the menu bar table
	*/
	function endTable() {
		$output = '
		</div>'
		;

		return $output;
	}

	/**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
	function custom( $task='', $icon='', $iconOver='', $alt='', $listSelect=true ) {
   		global $_LANG;

		$image = mosAdminMenus::ImageCheck( $iconOver, '/images/', NULL, NULL, $alt, $task );
		if ($listSelect) {
			$js = "javascript:if (document.adminForm.boxchecked.value == 0){ alert('". $_LANG->_( 'Please make a selection from the list to' ) ." ". $alt ."');}else{submitbutton('$task')}";
		} else {
			$js = "javascript:submitbutton('$task')";
		}

		$output = '
		<div class="iconbutton">
			<a href="#'. $task .'" onclick="'. $js .'" title="'. strtolower($alt) .'">
				'.  $image . '<br />' . $alt .'</a>
		</div>'
		;

		return $output;
	}

	/**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function deleteList( $msg='', $task='remove', $alt='Delete' ) {
		// trick trawler $_LANG->_( 'Delete' );
		global $_LANG;

		$image = mosAdminMenus::ImageCheck( 'delete_f2.png', '/images/', NULL, NULL, $alt, $task, 0 );
		$js 	= 'javascript:if (document.adminForm.boxchecked.value == 0){ alert(\''. $_LANG->_( 'Please make a selection from the list to' ) .' '. $alt.'\'); } else if (confirm(\''. $_LANG->_( 'validDeleteItems' ).' '. $msg .'\')){ submitbutton(\''. $task .'\');}';

		$output = '
		<div class="iconbutton">
			<a class="toolbar" href="#" onclick="'. $js .'">
				'. $image . ' ' . $_LANG->_( $alt ).'</a>
		</div>'
		;

		return $output;
	}

	/**
	* Writes a cancel button that will go back to the previous page without doing
	* any other operation
	*/
	function back() {
		$image = mosAdminMenus::ImageCheck( 'back_f2.png', '/images/', NULL, NULL, 'back', 'cancel' );

		$output = '
		<div class="iconbutton">
			<a href="javascript:window.history.back();">
			'. $image .'
			</a>
		</div>'
		;

		return $output;
	}

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function addNew( $task='new', $alt='New' ) {
		// trick trawler $_LANG->_( 'New' );
		$output = mosToolBar_return::custom( $task, 'new.png', 'new_f2.png', $alt, false );

		return $output;
	}

	/**
	* Writes a common 'publish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publish( $task='publish', $alt='Publish' ) {
		// trick trawler $_LANG->_( 'Publish' );
		$output = mosToolBar_return::custom( $task, 'publish.png', 'publish_f2.png', $alt, false );

		return $output;
	}

	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publishList( $task='publish', $alt='Published' ) {
		// trick trawler $_LANG->_( 'Publish' );
		$output = mosToolBar_return::custom( $task, 'publish.png', 'publish_f2.png', $alt, true );

		return $output;
	}

	/**
	* Writes a common 'unpublish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublish( $task='unpublish', $alt='Unpublish' ) {
		// trick trawler $_LANG->_( 'Unpublish' );
		$output = mosToolBar_return::custom( $task, 'unpublish.png', 'unpublish_f2.png', $alt, false );

		return $output;
	}

	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublishList( $task='unpublish', $alt='Unpublish' ) {
		// trick trawler $_LANG->_( 'Unpublish' );
		$output = mosToolBar_return::custom( $task, 'unpublish.png', 'unpublish_f2.png', $alt, true );

		return $output;
	}

	/**
	* Writes a common 'archive' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function archiveList( $task='archive', $alt='Archive' ) {
		// trick trawler $_LANG->_( 'archive' );
		$output = mosToolBar_return::custom( $task, 'archive.png', 'archive_f2.png', $alt, true );

		return $output;
	}

	/**
	* Writes an unarchive button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unarchiveList( $task='unarchive', $alt='Unarchive' ) {
		// trick trawler $_LANG->_( 'Unarchive' );
		$output = mosToolBar_return::custom( $task, 'unarchive.png', 'unarchive_f2.png', $alt, true );

		return $output;
	}

	/**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function editList( $task='edit', $alt='_E_EDIT' ) {
		// trick trawler $_LANG->_( 'Edit' );
		$output = mosToolBar_return::custom( $task, 'edit.png', 'edit_f2.png', $alt, true );

		return $output;
	}

	/**
	* Writes a save button for a given option
	* Apply operation leads to a save action only (does not leave edit mode)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function apply( $task='apply', $alt='Apply' ) {
		// trick trawler $_LANG->_( 'Apply' );
		$output = mosToolBar_return::custom( $task, 'apply.png', 'apply_f2.png', $alt, false );

		return $output;
	}

	/**
	* Writes a save button for a given option
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function save( $task='save', $alt='Save' ) {
		// trick trawler $_LANG->_( 'Save' );
		$output = mosToolBar_return::custom( $task, 'save.png', 'save_f2.png', $alt, false );

		return $output;
	}

	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function cancel( $task='cancel', $alt='Cancel' ) {
		// trick trawler $_LANG->_( 'Cancel' );
		$output = mosToolBar_return::custom( $task, 'cancel.png', 'cancel_f2.png', $alt, false );

		return $output;
	}
}
?>