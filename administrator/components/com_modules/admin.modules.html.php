<?php
/**
* @version $Id: admin.modules.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Modules
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
 * @subpackage Modules
 */
class moduleScreens {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml, $files=null) {
		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );
		$tmpl->setAttribute( 'body', 'src', $bodyHtml );

		return $tmpl;
	}

	/**
	 * Create Options
	 */
	function createOptions() {
		global $_LANG;

		$tmpl =& moduleScreens::createTemplate( 0, array( 'forms.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'createOptions.html' );

		$client = array(
			patHTML::makeOption( 0, $_LANG->_( 'Site' ) ),
			patHTML::makeOption( 1, $_LANG->_( 'Administrator' ) ),
		);

		patHTML::radioSet( $tmpl, 'body', 'client_id', 0, $client, 'RADIO_CLIENT' );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Manage
	 * @param int The client identifier
	 */
	function manage( &$rows ) {
		$tmpl =& moduleScreens::createTemplate( 'manage.html', array() );

		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function installOptions() {
		$tmpl =& moduleScreens::createTemplate( 'installOptions.html', array( 'installer.html' ) );

		$tmpl->addVar( 'body', 'sitepath', $GLOBALS['mosConfig_absolute_path'] );

		$tmpl->displayParsedTemplate( 'body' );
	}

	/**
	 * Finished install
	 */
	function installDone( &$installer ) {
		$tmpl =& moduleScreens::createTemplate( 'installDone.html' );

		$tmpl->addVar( 'body', 'element', 	$installer->elementName() );
		$tmpl->addVar( 'body', 'errno', 	$installer->errno() );
		$tmpl->addVar( 'body', 'message', 	$installer->error() );
		$tmpl->addVar( 'body', 'ilog', 		$installer->getLog() );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function packageOptions( $row ) {
		$tmpl =& moduleScreens::createTemplate( 'packageOptions.html', array( 'installer.html' ) );

		$fileName = $row->module;
		$tmpl->addVar( 'body', 'filename', $fileName );
		$tmpl->addVar( 'body', 'element', $row->id );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Lists package files
	 * @param array An array of files
	 */
	function listFiles( $files ) {
		$tmpl =& moduleScreens::createTemplate( 'listFiles.html', array( 'files.html' ) );

		$tmpl->addRows( 'file-list-rows', $files );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Show the edit XML form
	 * @param array An array of xml variables
	 * @param object
	 */
  	function editXML( &$vars, $row ) {
		$tmpl =& moduleScreens::createTemplate( 'editXML.html', array( 'xml.html' ) );

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
	* List modules
	* @param array
	*/
	function view( &$items, &$lists, $search, &$pageNav, &$vars ) {
		global $_LANG;

		$tmpl =& moduleScreens::createTemplate( 'view.html', array( 'adminlists.html', 'adminfilters.html' ) );

		$tmpl->addObject( 'filter-position', 	$lists['positions'] );
		$tmpl->addObject( 'filter-module', 		$lists['modules'] );

		$tmpl->addRows( 'filter-state', $lists['state'] );
		$tmpl->addRows( 'filter-access', 	$lists['access'] );

		// preprocess the ordering icons
		$n = count( $items );
		for ($i = 0; $i < $n; $i++) {
			$item = &$items[$i];
			$item->orderUpIcon   = $pageNav->orderUpIcon( $item->id, ($item->position == @$items[$i-1]->position && $item->ordering > -10000 && $item->ordering < 10000) );
			$item->orderDownIcon = $pageNav->orderDownIcon( $item->id, $n, ($item->position == @$items[$i+1]->position && $item->ordering > -10000 && $item->ordering < 10000) );
		}

		$tmpl->addObject( 'list-items', $items, 'item_' );
		$tmpl->addVars( 'body', $vars );

		// setup the page navigation footer
		$pageNav->setTemplateVars( $tmpl, 'list-navigation' );

		$tmpl->displayParsedTemplate( 'form' );
	}
}

/**
* @package Joomla
* @subpackage Modules
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
	function editModule( &$row, &$orders2, &$lists, &$params, $option ) {
		global $mosConfig_live_site;
  		global $_LANG;

		$row->titleA = $row->module;

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton) {
			if ( ( pressbutton == 'save' ) && ( document.adminForm.title.value == '' ) ) {
				alert("<?php echo $_LANG->_( 'Module must have a title' ); ?>");
				return;
			} else {
				<?php if ( $row->module == '' || $row->module == 'custom' ) {
					getEditorContents( 'editor1', 'content' );
				}?>
				submitform(pressbutton);
			}
			submitform(pressbutton);
		}

		var originalOrder = '<?php echo $row->ordering;?>';
		var originalPos = '<?php echo $row->position;?>';
		var orders = new Array();	// array in the format [key,value,text]
		<?php	$i = 0;
		foreach ( $orders2 as $k=>$items ) {
			foreach ( $items as $v ) {
				echo "\n	orders[".$i++."] = new Array( \"$k\",\"$v->value\",\"$v->text\" );";
			}
		}
		?>
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">
	<div id="datacellfull">

		<fieldset>
			<legend><?php echo $row->titleA; ?></legend>

			<table width="100%">
			<tr valign="top">
				<td width="60%">
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo $_LANG->_( 'Details' ); ?>
						</th>
					<tr>
					<tr>
						<td width="100" align="left">
							<?php echo $_LANG->_( 'Title' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="title" size="35" value="<?php echo $row->title; ?>" />
						</td>
					</tr>
					<!-- START selectable pages -->
					<tr>
						<td width="100" align="left">
							<?php echo $_LANG->_( 'Show title' ); ?>:
						</td>
						<td>
							<?php echo $lists['showtitle']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" align="left">
							<?php echo $_LANG->_( 'Position' ); ?>:
						</td>
						<td>
							<?php echo $lists['position']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top" align="left">
							<?php echo $_LANG->_( 'Module Order' ); ?>:
						</td>
						<td>
							<script language="javascript" type="text/javascript">
							<!--
							writeDynaList( 'class="inputbox" name="ordering" size="1"', orders, originalPos, originalPos, originalOrder );
							//-->
							</script>
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
						<td colspan="2">
						</td>
					</tr>
					<tr>
						<td valign="top">
							<?php echo $_LANG->_( 'ID' ); ?>:
						</td>
						<td>
							<?php echo $row->id; ?>
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

					<table class="adminform">
					<tr>
						<th >
							<?php echo $_LANG->_( 'Parameters' ); ?>
						</th>
					<tr>
					<tr>
						<td>
							<?php echo $params->render( 'params', 0 );?>
						</td>
					</tr>
					</table>
				</td>
				<td width="40%" >
					<table width="100%" class="adminform">
					<tr>
						<th>
							<?php echo $_LANG->_( 'Pages / Items' ); ?>
						</th>
					<tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Menu Item Link(s)' ); ?>:
							<br />
							<?php echo $lists['selections']; ?>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<?php
			if ( $row->module == '' || $row->module == 'custom' ) {
				?>
				<tr>
					<td colspan="2">
						<table width="100%" class="adminform">
						<tr>
							<th colspan="2">
								<?php echo $_LANG->_( 'Custom Output' ); ?>
							</th>
						<tr>
						<tr>
							<td valign="top" align="left">
								<?php echo $_LANG->_( 'Content' ); ?>:
							</td>
							<td>
								<?php
								// parameters : areaname, content, hidden field, width, height, rows, cols
								editorArea( 'editor1',  $row->content , 'content', '700', '350', '95', '30' ) ;
								?>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			?>
			</table>
		</fieldset>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="original" value="<?php echo $row->ordering; ?>" />
		<input type="hidden" name="module" value="<?php echo $row->module; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client_id" value="<?php echo $row->client_id ?>" />
		<?php
		if ( $row->client_id ) {
			echo '<input type="hidden" name="client" value="admin" />';
		}
		?>
		</form>
		<?php
	}

	/**
	* Displays a selection list for menu item types
	*/
	function addModule( &$modules, $client ) {
  		global $_LANG;

		mosCommonHTML::loadOverlib();

		if ( $client == 'admin' ) {
			$type = $_LANG->_( 'Administrator' );
		} else {
			$type = $_LANG->_( 'Site' );
		}
		?>
		<form action="index2.php" method="post" name="adminForm">

		<div id="treecell">
			<fieldset>
				<legend>
					<mos:Translate>Quick Tip</mos:Translate>
				</legend>
				<mos:Translate>NewModulesQuickTip</mos:Translate>
			</fieldset>
		</div>

		<div id="datacell" align="center">
			<fieldset>
				<legend><?php echo $_LANG->_( 'Modules' ); ?></legend>

				<table class="adminform" id="moslist">
				<thead>
				<tr>
					<th width="10">
					</th>
					<th>
					<?php echo $_LANG->_( 'Module Type' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="2">
					</th>
				</tr>
				</tfoot>

				<tbody>
				<?php
				$k 		= 0;
				$count 	= count( $modules );
					for ( $i=0; $i < $count; $i++ ) {
					$row = &$modules[$i];

					$link = 'index2.php?option=com_modules&amp;task=edit&amp;module='. $row->module .'&amp;created=1&amp;client='. $client;
					?>
					<tr class="<?php echo "row$k"; ?>" valign="top">
						<td width="10">
							<input type="radio" id="cb<?php echo $i; ?>" name="module" value="<?php echo $row->module; ?>"  />
						</td>
						<td align="left" nowrap="nowrap">
							<?php
							echo mosToolTip( stripslashes( $row->descrip ), stripslashes( $row->name ), 300, '', stripslashes( $row->name ), $link, 'LEFT' );
							?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</tbody>
				</table>
			</fieldset>
		</div>

		<input type="hidden" name="option" value="com_modules" />
		<input type="hidden" name="client" value="<?php echo $client; ?>" />
		<input type="hidden" name="created" value="1" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function popupPreview() {
		global $mosConfig_live_site;
		global $database;
		global $_LANG;

		$title 	= mosGetParam( $_REQUEST, 'title', 0 );

		// load site template
		$sql = "SELECT template"
		. "\n FROM #__templates_menu"
		. "\n WHERE client_id = '0'"
		. "\n AND menuid = '0'";
		$database->setQuery( $sql );
		$template = $database->loadResult();

		$row = null;
		$query 	= "SELECT *"
		. "\n FROM #__modules"
		. "\n WHERE title = '$title'";
		$database->setQuery( $query );
		$database->loadObject( $row );

		$pat		= "src=images";
		$replace	= "src=../../images";
		$pat2		= "\\\\'";
		$replace2	= "'";

		$content	= eregi_replace( $pat, $replace, $row->content );
		$content	= eregi_replace( $pat2, $replace2, $row->content );
		$title		= eregi_replace( $pat2, $replace2, $row->title );

		// xml prolog
		echo '<?xml version="1.0" encoding="'. $_LANG->iso() .'"?' .'>';
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_LANG->iso(); ?>" />
		<title><?php echo $_LANG->_( 'Module Preview' ); ?></title>
		<link rel="stylesheet" href="<?php echo $mosConfig_live_site; ?>/templates/<?php echo $template; ?>/css/template_css<?php echo $_LANG->rtl() ? '_rtl': ''; ?>.css" type="text/css" />
		<script language="javascript" type="text/javascript">
		<!--
		var form = window.opener.document.adminForm
		var content = form.content.value;
		var title = form.title.value;

		content = content.replace('#', '');
		title 	= title.replace('#', '');
		content = content.replace('src=images', 'src=<?php echo $mosConfig_live_site; ?>/images');
		content = content.replace('src=images', 'src=<?php echo $mosConfig_live_site; ?>/images');
		title 	= title.replace('src=images', 'src=<?php echo $mosConfig_live_site; ?>/images');
		content = content.replace('src=images', 'src=<?php echo $mosConfig_live_site; ?>/images');
		title 	= title.replace('src=\"images', 'src=\"<?php echo $mosConfig_live_site; ?>/images');
		content = content.replace('src=\"images', 'src=\"<?php echo $mosConfig_live_site; ?>/images');
		title 	= title.replace('src=\"images', 'src=\"<?php echo $mosConfig_live_site; ?>/images');
		content = content.replace('src=\"images', 'src=\"<?php echo $mosConfig_live_site; ?>/images');
		//-->
		</script>
		</head>
		<body style="background-color:#FFFFFF">

		<table align="center" cellspacing="2" cellpadding="2" border="0" width="100%">
		<tr>
		    <td class="moduleheading">
				<script language="javascript" type="text/javascript">
				<!--
			    document.write(title);
			    //-->
			    </script>
		    </td>
		</tr>
		<tr>
		    <td valign="top" height="90%">
				<script language="javascript" type="text/javascript">
				<!--
			    document.write(content);
			    //-->
			    </script>
		    </td>
		</tr>
		<tr>
		    <td align="center">
			    <a href="#" onClick="window.close()">
			   		<?php echo $_LANG->_( 'Close' ); ?></a>
		    </td>
		</tr>
		</table>

		</body>
		</html>
		<?php
	}
}
?>