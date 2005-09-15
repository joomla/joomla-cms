<?php
/**
* @version $Id: admin.mambots.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Mambots
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Mambots
 */
class mambotScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $bodyHtml='', $files=null) {
		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		if ($bodyHtml) {
			$tmpl->setAttribute( 'body', 'src', $bodyHtml );
		}

		return $tmpl;
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function installOptions() {
		$tmpl =& mambotScreens::createTemplate( 'installOptions.html', array( 'installer.html' ) );

		$tmpl->addVar( 'body', 'sitepath', $GLOBALS['mosConfig_absolute_path'] );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Finished install
	 */
	function installDone( &$installer ) {
		$tmpl =& mambotScreens::createTemplate( 'installDone.html' );

		$tmpl->addVar( 'body', 'element', $installer->elementName() );
		$tmpl->addVar( 'body', 'errno', $installer->errno() );
		$tmpl->addVar( 'body', 'message', $installer->error() );
		$tmpl->addVar( 'body', 'ilog', $installer->getLog() );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function packageOptions( $row ) {
		$tmpl =& mambotScreens::createTemplate( 'packageOptions.html', array( 'installer.html' ) );

		$fileName = $row->option;
		$tmpl->addVar( 'body', 'filename', $fileName );
		$tmpl->addVar( 'body', 'element', $row->id );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Lists package files
	 * @param array An array of files
	 */
	function listFiles( $files ) {
		$tmpl =& mambotScreens::createTemplate( 'listFiles.html', array( 'files.html' ) );

		$tmpl->addRows( 'file-list-rows', $files );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Show the edit XML form
	 * @param array An array of xml variables
	 * @param object
	 */
  	function editXML( &$vars, $row ) {
		$tmpl =& mambotScreens::createTemplate( 'editXML.html', array( 'xml.html' ) );

		$tmpl->addVar( 'body', 'type', 'mambot' );
		$tmpl->addObject( 'body', $row, 'row_' );
		if (isset( $vars['meta'] )) {
			$tmpl->addVars( 'body', $vars['meta'], 'meta_' );
		}

		if (isset( $vars['siteFiles'] )) {
			$tmpl->addRows( 'site-files-list', $vars['siteFiles'] );
		}
		$tmpl->addVar( 'body', 'params', $vars['params'] );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	* List languages
	* @param array
	*/
	function view( &$rows, &$lists, $search, &$pageNav, &$vars ) {
		global $_LANG;

		$tmpl =& mambotScreens::createTemplate( 'view.html', array( 'adminlists.html', 'adminfilters.html' ) );

		$tmpl->addObject( 'mambot-folders', $lists['folders'] );

		$tmpl->addRows( 'published-list', $lists['published'] );
		$tmpl->addRows( 'access-list', $lists['access'] );

		// preprocess the ordering icons
		$n = count( $rows );
		for ($i = 0; $i < $n; $i++) {
			$row = &$rows[$i];
			$row->orderUpIcon 	= $pageNav->orderUpIcon( $i, ( $row->folder == @$rows[$i-1]->folder && $row->ordering > -10000 && $row->ordering < 10000 ) );
			$row->orderDownIcon = $pageNav->orderDownIcon( $i, $n, ( $row->folder == @$rows[$i+1]->folder && $row->ordering > -10000 && $row->ordering < 10000 ) );
		}

		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );
		$tmpl->addVars( 'body', $vars );

		// setup the page navigation footer
		$pageNav->setTemplateVars( $tmpl, 'list-navigation' );

		$tmpl->displayParsedTemplate( 'form' );
	}
}

/**
* @package Joomla
* @subpackage Mambots
*/
class HTML_modules {
	/**
	* Writes the edit form for new and existing module
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param mosCategory The category object
	* @param array <p>The modules of the left side.  The array elements are in the form
	* <var>$leftorder[<i>order</i>] = <i>label</i></var>
	* where <i>order</i> is the module order from the db table and <i>label</i> is a
	* text label associciated with the order.</p>
	* @param array See notes for leftorder
	* @param array An array of select lists
	* @param object Parameters
	*/
	function editMambot( &$row, &$lists, &$params, $option ) {
		global $mosConfig_live_site;
		global $_LANG;

		$row->nameA = '';
		if ( $row->id ) {
			$row->nameA = $row->element;
		}

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			if ( pressbutton == 'cancel' ) {
				submitform(pressbutton);
				return;
			}
			// validation
			var form = document.adminForm;

			if ( form.name.value == '' ) {
				alert( '<?php echo $_LANG->_( 'Mambot must have a name' ); ?>' );
			} else if ( form.element.value == '' ) {
				alert( '<?php echo $_LANG->_( 'Mambot must have a filename' ); ?>' );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
	<div id="datacellfull">
		<fieldset>
			<legend><?php echo $row->nameA; ?></legend>

			<table width="100%">
			<tr valign="top">
				<td width="60%" valign="top">
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo $_LANG->_( 'Mambot Details' ); ?>
						</th>
					<tr>
					<tr>
						<td width="100" align="left">
							<?php echo $_LANG->_( 'Name' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="name" size="35" value="<?php echo $row->name; ?>" />
						</td>
					</tr>
					<tr>
						<td valign="top" align="left">
							<?php echo $_LANG->_( 'Folder' ); ?>:
						</td>
						<td>
							<?php echo $lists['folder']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" align="left">
							<?php echo $_LANG->_( 'Mambot file' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="element" size="35" value="<?php echo $row->element; ?>" />.php
						</td>
					</tr>
					<tr>
						<td valign="top" align="left">
							<?php echo $_LANG->_( 'Mambot Order' ); ?>:
						</td>
						<td>
							<?php echo $lists['ordering']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" align="left">
						<?php echo $_LANG->_( 'Access Level' ); ?>:
						</td>
						<td>
							<?php echo $lists['access']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<?php echo $_LANG->_( 'Published' ); ?>:
						</td>
						<td>
							<?php echo $lists['published']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" colspan="2">
						</td>
					</tr>
					<tr>
						<td valign="top">
							<?php echo $_LANG->_( 'Description' ); ?>:
						</td>
						<td>
							<?php echo $row->description; ?>
						</td>
					</tr>
					</table>
				</td>
				<td width="40%">
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo $_LANG->_( 'Parameters' ); ?>
						</th>
					<tr>
					<tr>
						<td>
							<?php
							if ( $row->id ) {
								echo $params->render( 'params', 0 );
							} else {
								echo '<i>'. $_LANG->_( 'No Parameters' ) .'</i>';
							}
							?>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
		</fieldset>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="iscore" value="<?php echo $row->iscore; ?>" />
		<input type="hidden" name="client" value="<?php echo $row->client_id; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>
