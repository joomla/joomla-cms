<?php
/**
* @version $Id: menubar.html.php 137 2005-09-12 10:21:17Z eddieajau $
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
class mosMenuBar {

	/**
	 * Title cell
	 * For the title and toolbar to be rendered correctly,
	 * this title fucntion must be called before the starttable function and the toolbars icons
	 * this is due to the nature of how the css has been used to postion the title in respect to the toolbar
	 * @param string The title
	 * @param string The image.  If starting with / it using the component image directory
	 * @param string
	 */
	function title( $title, $icon='asterisk.png', $href='#' ) {
		global $option;
		if (substr( $icon, 0, 1) == '/') {
			$iconPath = '/administrator/components/' . $option . '/images/';
		} else {
			$iconPath = '/administrator/images';
		}
		?>
		<div class="title">
			<a href="<?php echo $href; ?>">
				<?php echo mosAdminHTML::imageCheck( $icon, $iconPath ); ?></a>
			<a href="<?php echo $href; ?>">
				<?php echo $title; ?></a>
		</div>
		<?php
	}

	/**
	* Writes the start of the button bar table
	*/
	function startTable() {
		?>
		<div class="admintoolbar" id="mostoolbar">
		<?php
	}

	/**
	* Writes a spacer cell
	* @param string The width for the cell
	*/
	function spacer( $width='' ) {
		if ( $width ) {
			?>
			<div style="width: <?php echo $width; ?>">
				&nbsp;
			</div>
			<?php
		} else {
			?>
			<div class="spacer">
				&nbsp;
			</div>
			<?php
		}
		?>
		<?php
	}

	/**
	* Writes the end of the menu bar table
	*/
	function endTable() {
		?>
		</div>
		<?php
	}

	/**
	* Write a divider between menu buttons
	*/
	function divider() {
		$image = mosAdminHTML::imageCheck( 'spacer.png', '/administrator/images/', NULL, NULL, NULL, NULL, 1, 'middle', 3 );
		?>
		<div class="divider" >
			<?php echo $image; ?>
		</div>
		<?php
	}

	/**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over  (DEPRECEATED)
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
	function custom( $task='', $icon='', $iconOver='', $alt='', $listSelect=true ) {
    	global $mainframe, $_LANG;

    	$alt = $_LANG->_( $alt );
		$image 	= mosAdminHTML::imageCheck( $icon, '/administrator/images/', NULL, NULL, $alt, $task );

		$type   = $listSelect ? 'toggle' : '';
		$doTask = "goDoTask(this, 'submit-$task', 'task=$task')";
		?>
		<div class="button" id="submit-<?php echo strtolower($task); ?>" >
			<a href="#<?php echo $task ?>" onclick="<?php echo $doTask ?>" title="<?php echo strtolower($alt) ?>" type="<?php echo $type ?>">
				<?php echo $image . '<br />' . $alt; ?></a>
		</div>
		<?php
	}

	/**
	* Writes a custom option and task button for the button bar (opens in a popup)
	* @param string The page to load in the popup window
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	* @param integer The width of the popup window
	* @param integer The height of the popup window
	* @param integer The top position of the window
	* @param integer The left position of the window
	* @since 4.5.3
	*/
	function popup( $url = '', $task='', $icon='', $alt='', $listSelect=false, $width=640, $height=480, $top=0, $left=0  ) {
    	global $_LANG;
    	$alt = $_LANG->_( $alt );

    	$image 	= mosAdminHTML::imageCheck( $icon, '/administrator/images/', NULL, NULL, $alt, $task );

		$type   = $listSelect ? 'toggle' : '';
		$doTask = "goDoTask(this, 'popup-$task', 'task=$task,url=$url,width=$width,height=$height,top=$top,left=$left')";

		?>
		<div class="button" id="popup-<?php echo strtolower($task); ?>" >
		 	<a href="#<?php echo $task ?>" onclick="<?php echo $doTask ?>" title="<?php echo $alt; ?>" type="<?php echo $type; ?>">
				<?php echo $image . '<br />' . $alt ; ?></a>
		</div>
		<?php
	}


	/**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function deleteList( $msg='', $task='remove', $alt='Delete' ) {
		global $_LANG;
		$alt 	= $_LANG->_( $alt );

		$image 	= mosAdminHTML::imageCheck( 'delete.png', '/administrator/images/', NULL, NULL, $alt, $task );

		$text   = $_LANG->_( 'validDeleteItems' );
		$doTask	= "goDoTask(this, 'submit-$task', 'task=$task,msg=$text $msg')";
		?>
		<div class="button" id="submit-<?php echo strtolower($task); ?>" >
			<a href="#<?php echo $task ?>" onclick="<?php echo $doTask ?>" title="<?php echo strtolower($alt) ?>" type="toggle">
				<?php echo $image . '<br />' . $alt; ?></a>
		</div>
		<?php
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension)
	*/
	function preview( $popup='', $updateEditors=false ) {
    	global $_LANG;
    	$alt = $_LANG->_( 'Preview' );

		$image 	= mosAdminHTML::imageCheck( 'preview.png', '/administrator/images/', NULL, NULL, $alt, 'preview' );

		$update = $updateEditors ? 'update=true' : 'update=false';
		$doTask	= "goDoTask(this, 'popup-preview', '$update')";

		?>
		<div class="button" id="popup-preview" >
		 	<a href="#preview" onclick="<?php echo $doTask ?>" title="<?php echo $alt; ?>" type="">
				<?php echo $image . '<br />' . $_LANG->_( 'Preview' );?></a>
		</div>
		<?php
	}

	/**
	 * Writes a link button
	 * @param string The link
	 * @param string The task to perform (picked up by the switch($task) blocks
	 * @param string The image to display
	 * @param string The alt text for the icon image
	 * @param boolean True if required to check that a standard list item is checked
	 * @since 4.5.3
	 */
	function link( $href, $task='link', $icon='back.png', $alt='Back', $listSelect=false ) {
		global $_LANG;
		$alt 	= $_LANG->_( $alt );

		$image 	= mosAdminHTML::imageCheck( $icon, '/administrator/images/', NULL, NULL, $alt, $alt );

		$href = ( $href != '' ? "'href=$href'" : "''" );
		$doTask	= "goDoTask(this, 'submit-$task', $href)";

		?>
		<div class="button" id="submit-<?php echo strtolower($task); ?>" >
			<a href="#<?php echo $task ?>" onclick="<?php echo $doTask ?>" title="<?php echo $alt; ?>">
				<?php echo $image . '<br />' . $alt; ?></a>
		</div>
		<?php
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension for an xml file)
	* @param boolean Use the help file in the component directory
	*/
	function help( $ref, $com=false ) {
		global $mosConfig_live_site;
		global $_LANG;
		$alt 	    = $_LANG->_( 'Help' );

		$image 		= mosAdminHTML::imageCheck( 'help.png', '/administrator/images/', NULL, NULL, 'Help', 'help' );

		$helpUrl 	= mosGetParam( $GLOBALS, 'mosConfig_helpurl', '' );

		if ($com) {
	   // help file for 3PD Components
			$url = $mosConfig_live_site . '/administrator/components/' . $GLOBALS['option'] . '/help/';
			if (!eregi( '\.html$', $ref )) {
				$ref = $ref . '.xml';
			}
			$url .= $ref;
		} else if ( $helpUrl ) {
	   // Online help site as defined in GC
    		$ref .= '.' . $GLOBALS['_VERSION']->getHelpVersion();
			$url = $helpUrl . '/index2.php?option=com_content&amp;task=findkey&amp;pop=1&amp;keyref=' . urlencode( $ref );
		} else {
	   // Included html help files
			$url = $mosConfig_live_site . '/help/';
			$ref = $ref . '.html';
			$url .= $ref;
		}

		mosMenuBar::popup( $url, 'help', 'help.png', $alt, false);
	}

	/**
	 * Writes a back button, uses the browsers history.
	 * @param string An override for the alt text
	 * @param string A link to be used instead (DEPRECEATED, use link instead)
	 */
	function back( $alt='Back', $href='' ) {
		mosMenuBar::link( $href, 'back', 'back.png', $alt );
	}

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function addNew( $task='new', $alt='New' ) {
		mosMenuBar::custom( $task, 'new.png', 'new_f2.png', $alt, false );
	}

	/**
	* Writes a common 'publish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publish( $task='publish', $alt='Publish' ) {
		mosMenuBar::custom( $task, 'publish.png', 'publish_f2.png', $alt, false );
	}

	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function publishList( $task='publish', $alt='Publish' ) {
		mosMenuBar::custom( $task, 'publish.png', 'publish_f2.png', $alt, true );
	}

	/**
	* Writes a common 'unpublish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublish( $task='unpublish', $alt='Unpublish' ) {
		mosMenuBar::custom( $task, 'unpublish.png', 'unpublish_f2.png', $alt, false );
	}

	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unpublishList( $task='unpublish', $alt='Unpublish' ) {
		mosMenuBar::custom( $task, 'unpublish.png', 'unpublish_f2.png', $alt, true );
	}

	/**
	* Writes a common 'archive' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function archiveList( $task='archive', $alt='Archive' ) {
		mosMenuBar::custom( $task, 'archive.png', 'archive_f2.png', $alt, true );
	}

	/**
	* Writes an unarchive button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function unarchiveList( $task='unarchive', $alt='Unarchive' ) {
		mosMenuBar::custom( $task, 'unarchive.png', 'unarchive_f2.png', $alt, true );
	}

	/**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function editList( $task='edit', $alt='Edit' ) {
		mosMenuBar::custom( $task, 'edit.png', 'edit_f2.png', $alt, true );
	}

	/**
	* Write a trash button that will move items to Trash Manager
	*/
	function trash( $task='remove', $alt='Trash' ) {
		mosMenuBar::custom( $task, 'delete.png', 'delete_f2.png', $alt, true );
	}
	/**
	* Writes a save button for a given option
	* Apply operation leads to a save action only (does not leave edit mode)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function apply( $task='apply', $alt='Apply' ) {
		mosMenuBar::custom( $task, 'apply.png', 'apply_f2.png', $alt, false );
	}

	/**
	* Writes a save button for a given option
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function save( $task='save', $alt='Save' ) {
		mosMenuBar::custom( $task, 'save.png', 'save_f2.png', $alt, false );
	}

	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function cancel( $task='cancel', $alt='Cancel' ) {
		mosMenuBar::custom( $task, 'cancel.png', 'cancel_f2.png', $alt, false );
	}

	/**
	 * @deprecated
	 * @since 4.5.3
	 */
	function customX( $task='', $icon='', $iconOver='', $alt='', $listSelect=true ) {
    	mosMenuBar::custom( $task, $icon, $iconOver, $alt, $listSelect );
	}

	/**
	 * @deprecated
	 * @since 4.5.3
	 */
	function deleteListX( $msg='', $task='remove', $alt='Delete' ) {
		mosMenuBar::deleteList( $msg, $task, $alt);
	}

	/**
	 * @deprecated
	 * @since 4.5.3
	 */
	function addNewX( $task='new', $alt='New' ) {
		mosMenuBar::addNew( $task, $alt );
	}

	/**
	 * @deprecated
	 * @since 4.5.3
	 */
	function editListX( $task='edit', $alt='Edit' ) {
		mosMenuBar::editList( $task, $alt );
	}

	/**
	 * @deprecated
	 * @since 4.5.3
	 */
	function savenew( $task='savenew', $alt='Save' ) {
		mosMenuBar::custom( $task, 'save.png', 'save_f2.png', $alt, false );
	}

	/**
	 * @deprecated
	 * @since 4.5.3
	 */
	function saveedit( $task='saveedit', $alt='Save' ) {
		mosMenuBar::custom( $task, 'save.png', 'save_f2.png', $alt, false );
	}

	/**
	 * @deprecated
	 * @since 4.5.3
	 */
  	function createDir( ) {
		global $_LANG;
		$url = 'index3.php?option=com_media&task=popupDirectory';
		mosMenuBar::popup( $url, 'new', 'new.png', 'Create Directory', false, '500', '170', '120' , '150' );
	}

	/**
	 * @deprecated
	 * @since 4.5.3
	 */
  	function media_manager( $directory='', $alt='Upload' ) {
		$url = 'index3.php?option=com_media&task=popupUpload';
		mosMenuBar::popup( $url, 'upload', 'upload.png', $alt, false, '500', '170', '120', '150' );
	}
}
?>