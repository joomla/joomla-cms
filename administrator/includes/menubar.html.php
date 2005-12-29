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
	* @since 1.1
	*/
	function title( $title, $icon='generic.png' ) {
		$image = mosAdminMenus::ImageCheckAdmin( $icon, '/images/', NULL, NULL, $title, $title, 1 );
		?>
		<td class="title">
			<?php echo $image; ?>
			<?php echo $title; ?>
		</td>
		<?php
	}

	/**
	* Writes the start of the button bar table
	* @since 1.0
	*/
	function startTable() {
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="toolbar">
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
	* @since 1.0
	*/
	function custom( $task='', $icon='', $iconOver='', $alt='', $listSelect=true ) {

    	$alt = JText::_( $alt );

		$icon 	= ( $iconOver ? $iconOver : $icon );
		$image 	= mosAdminMenus::ImageCheckAdmin( $icon, '/images/', NULL, NULL, $alt, $task, 1 );

		if ($listSelect) {
			$href = "javascript:if (document.adminForm.boxchecked.value == 0){ alert('". JText::_( 'Please make a selection from the list to', true ) ." ". $alt ."');}else{submitbutton('$task')}";
		} else {
			$href = "javascript:submitbutton('$task')";
		}

		if ($icon || $iconOver) {
			?>
			<td>
				<a class="toolbar" href="<?php echo $href;?>">
					<?php echo $image; ?>
					<br /><?php echo $alt; ?></a>
			</td>
			<?php
		} else {
			?>
			<td>
				<a class="toolbar" href="<?php echo $href;?>">
					<br /><?php echo $alt; ?></a>
			</td>
			<?php
		}
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
	*/
	function customX( $task='', $icon='', $iconOver='', $alt='', $listSelect=true ) {

    	$alt = JText::_( $alt );

		$icon 	= ( $iconOver ? $iconOver : $icon );
		$image 	= mosAdminMenus::ImageCheckAdmin( $icon, '/images/', NULL, NULL, $alt, $task, 1 );

		if ($listSelect) {
			$href = "javascript:if (document.adminForm.boxchecked.value == 0){ alert('". JText::_( 'Please make a selection from the list to', true ) ." ". $alt ."');}else{hideMainMenu();submitbutton('$task')}";
		} else {
			$href = "javascript:hideMainMenu();submitbutton('$task')";
		}
		if ($icon || $iconOver) {
			?>
			<td>
				<a class="toolbar" href="<?php echo $href;?>">
					<?php echo $image; ?>
					<br /><?php echo $alt; ?></a>
			</td>
			<?php
		} else {
			?>
			<td>
				<a class="toolbar" href="<?php echo $href;?>">
					<br /><?php echo $alt; ?></a>
			</td>
			<?php
		}
	}

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function addNew( $task='new', $alt='New' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'new_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:submitbutton('<?php echo $task;?>');">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes the common 'new' icon for the button bar.
	* Extended version of addNew() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function addNewX( $task='new', $alt='New' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'new_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:hideMainMenu();submitbutton('<?php echo $task;?>');">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'publish' button
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function publish( $task='publish', $alt='Publish' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'publish_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:submitbutton('<?php echo $task;?>');">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function publishList( $task='publish', $alt='Publish' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'publish_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
	 	<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please make a selection from the list to', true ); ?> <?php echo $alt; ?>'); } else {submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
	 	<?php
	}

	/**
	* Writes a common 'default' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function makeDefault( $task='default', $alt='Default' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'publish_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please select an item to make', true ); ?> <?php echo $alt; ?>'); } else {submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'assign' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function assign( $task='assign', $alt='Assign' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'publish_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please select an item to', true ); ?> <?php echo $alt; ?>'); } else {submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'unpublish' button
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unpublish( $task='unpublish', $alt='Unpublish' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'unpublish_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:submitbutton('<?php echo $task;?>');">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unpublishList( $task='unpublish', $alt='Unpublish' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'unpublish_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please make a selection from the list to', true ); ?> <?php echo $alt; ?>'); } else {submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'archive' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function archiveList( $task='archive', $alt='Archive' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'archive_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please make a selection from the list to', true ); ?> <?php echo $alt; ?>'); } else {submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes an unarchive button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unarchiveList( $task='unarchive', $alt='Unarchive' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'unarchive_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please select a news story to', true ); ?> <?php echo $alt; ?>'); } else {submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editList( $task='edit', $alt='Edit' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'edit_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please select an item from the list to', true ); ?> <?php echo $alt; ?>'); } else {submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'edit' button for a list of records.
	* Extended version of editList() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editListX( $task='edit', $alt='Edit' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'edit_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please select an item from the list to', true ); ?> <?php echo $alt; ?>'); } else {hideMainMenu();submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'edit' button for a template html
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editHtml( $task='edit_source', $alt='' ) {

    	$alt = JText::_( 'Edit HTML' );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'html_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please select an item from the list to', true ); ?> <?php echo $alt; ?>'); } else {submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'edit' button for a template html.
	* Extended version of editHtml() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editHtmlX( $task='edit_source', $alt='' ) {

    	$alt = JText::_( 'Edit HTML' );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'html_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please select an item from the list to', true ); ?> <?php echo $alt; ?>'); } else {hideMainMenu();submitbutton('<?php echo $task;?>', '');}"">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'edit' button for a template css
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editCss( $task='edit_css', $alt='' ) {

    	$alt = JText::_( 'Edit CSS' );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'css_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please select an item from the list to', true ); ?> <?php echo $alt; ?>'); } else {submitbutton('<?php echo $task;?>', '');}"">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'edit' button for a template css.
	* Extended version of editCss() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editCssX( $task='edit_css', $alt='' ) {

    	$alt = JText::_( 'Edit CSS' );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'css_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please select an item from the list to', true ); ?> <?php echo $alt; ?>'); } else {hideMainMenu();submitbutton('<?php echo $task;?>', '');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function deleteList( $msg='', $task='remove', $alt='Delete' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'delete_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please make a selection from the list to', true ); ?> <?php echo $alt; ?>'); } else if (confirm('Are you sure you want to delete selected items? <?php echo $msg;?>')){ submitbutton('<?php echo $task;?>');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a common 'delete' button for a list of records.
	* Extended version of deleteList() calling hideMainMenu() before submitbutton().
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function deleteListX( $msg='', $task='remove', $alt='Delete' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'delete_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:if (document.adminForm.boxchecked.value == 0){ alert('<?php echo JText::_( 'Please make a selection from the list to', true ); ?> <?php echo $alt; ?>'); } else if (confirm('<?php echo JText::_( 'VALIDDELETEITEMS' ); ?> <?php echo $msg;?>')){ hideMainMenu();submitbutton('<?php echo $task;?>');}">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Write a trash button that will move items to Trash Manager
	* @since 1.0
	*/
	function trash( $task='remove', $alt='Trash', $check=true ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'delete_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );

		if ( $check ) {
			$js = "javascript:if (document.adminForm.boxchecked.value == 0){ alert('". JText::_( 'Please make a selection from the list to', true ) ." ". $alt ."'); } else { submitbutton('$task');}";
		} else {
			$js = "javascript:submitbutton('$task');";
		}

		?>
		 <td>
			<a class="toolbar" href="<?php echo $js; ?>">
				<?php echo $image2; ?>
				<br /><?php echo $alt; ?></a>
		</td>
		<?php
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension)
	* @since 1.0
	*/
	function preview( $url='', $updateEditors=false ) {
		global $database;

		$image2 = mosAdminMenus::ImageCheckAdmin( 'preview_f2.png', '/images/', NULL, NULL, 'Preview', 'preview', 1 );

		?>
		<td>
			<script language="javascript">
			function popup() {
				<?php
				if ($updateEditors) {
					getEditorContents( 'editor1', 'introtext' );
					getEditorContents( 'editor2', 'fulltext' );
				}
				?>
				window.open('<? echo $url."&task=preview"; ?>', 'win1', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
			}
			</script>
		 	<a class="toolbar" href="#" onclick="popup();">
				<?php echo $image2; ?>
				<br /><?php echo JText::_( 'Preview' ); ?></a>
		</td>
		<?php
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension for an xml file)
	* @param boolean Use the help file in the component directory
	* @since 1.0
	*/
	function help( $ref, $com=false ) {
		$image2 	= mosAdminMenus::ImageCheckAdmin( 'help_f2.png', '/images/', NULL, NULL, 'Help', 'help', 1 );

		jimport('joomla.help');
		$url = JHelp::createURL($ref, $com);

		?>
		<td>
			<a class="toolbar" href="#" onclick="window.open('<?php echo $url;?>', 'mambo_help_win', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');">
				<?php echo $image2; ?>
				<br /><?php echo JText::_( 'Help' ); ?></a>
		</td>
		<?php
	}

	/**
	* Writes a save button for a given option
	* Apply operation leads to a save action only (does not leave edit mode)
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function apply( $task='apply', $alt='Apply' ) {

    	$alt = JText::_( $alt );

		$image 	= mosAdminMenus::ImageCheckAdmin( 'apply.png', '/images/', NULL, NULL, $alt, $task );
		$image2 = mosAdminMenus::ImageCheckAdmin( 'apply_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:submitbutton('<?php echo $task;?>');">
				<?php echo $image2; ?>
				<br /><?php echo $alt;?></a>
		</td>
		<?php
	}

	/**
	* Writes a save button for a given option
	* Save operation leads to a save and then close action
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function save( $task='save', $alt='Save' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'save_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:submitbutton('<?php echo $task;?>');">
				<?php echo $image2; ?>
				<br /><?php echo $alt;?></a>
		</td>
		<?php
	}

	/**
	* Writes a save button for a given option (NOTE this is being deprecated)
	* @since 1.0
	*/
	function savenew() {

		$image2 = mosAdminMenus::ImageCheckAdmin( 'save_f2.png', '/images/', NULL, NULL, 'save', 'save', 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:submitbutton('savenew');">
				<?php echo $image2; ?>
				<br /><?php echo JText::_( 'Save' ); ?></a>
		</td>
		<?php
	}

	/**
	* Writes a save button for a given option (NOTE this is being deprecated)
	* @since 1.0
	*/
	function saveedit() {

		$image2 = mosAdminMenus::ImageCheckAdmin( 'save_f2.png', '/images/', NULL, NULL, 'save', 'save', 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:submitbutton('saveedit');">
				<?php echo $image2; ?>
				<br /><?php echo JText::_( 'Save' ); ?></a>
		</td>
		<?php
	}

	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function cancel( $task='cancel', $alt='Cancel' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'cancel_f2.png', '/images/', NULL, NULL, $alt, $task, 1 );
		?>
		<td>
			<a class="toolbar" href="javascript:submitbutton('<?php echo $task;?>');">
				<?php echo $image2; ?>
				<br /><?php echo $alt;?></a>
		</td>
		<?php
	}

	/**
	* Writes a cancel button that will go back to the previous page without doing
	* any other operation
	* @since 1.0
	*/
	function back( $alt='Back', $href='' ) {

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'back_f2.png', '/images/', NULL, NULL, 'back', 'cancel', 1 );
		if ( $href ) {
			$link = $href;
		} else {
			$link = 'javascript:window.history.back();';
		}
		?>
		<td>
			<a class="toolbar" href="<?php echo $link; ?>">
				<?php echo $image2; ?>
				<br /><?php echo $alt;?></a>
		</td>
		<?php
	}

	/**
	* Write a divider between menu buttons
	* @since 1.0
	*/
	function divider() {
		$image = mosAdminMenus::ImageCheckAdmin( 'menu_divider.png', '/images/' );
		?>
		<td>
			<?php echo $image; ?>
		</td>
		<?php
	}

	/**
	* Writes a media_manager button
	* @param string The sub-drectory to upload the media to
	* @since 1.0
	*/
	function media_manager( $directory='', $alt='Upload' ) {
		global $database;

    	$alt = JText::_( $alt );

		$image2 = mosAdminMenus::ImageCheckAdmin( 'upload_f2.png', '/images/', NULL, NULL, 'Upload Image', 'uploadPic', 1 );
		?>
		<td>
			<a class="toolbar" href="#" onclick="popupWindow('index3.php?option=com_media&amp;task=popupUpload&amp;directory=<?php echo $directory; ?>','win1',250,100,'no');">
				<?php echo $image2; ?>
				<br /><?php echo $alt;?></a>
		</td>
		<?php
	}

	/**
	* Writes a spacer cell
	* @param string The width for the cell
	* @since 1.0
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
	* @since 1.0
	*/
	function endTable() {
		?>
		</tr>
		</table>
		<?php
	}
}

/**
 * Legacy class, use JMenuBar instead
 * @deprecated As of version 1.1
 */
class mosMenuBar extends JMenuBar
{

}
?>