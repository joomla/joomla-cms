<?php
/**
* @version $Id: admin.poll.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Polls
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
 * @subpackage Polls
 */
class pollsScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null) {

		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	* List languages
	* @param array
	*/
	function view( &$lists ) {
		global $mosConfig_lang;

		$tmpl =& pollsScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'view.html' );

		$tmpl->addVar( 'body2', 'search', $lists['search'] );

		// temp lists --- these can be done in pat a lot better
		$tmpl->addVar( 'body2', 'lists_state', $lists['state'] );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}

	function editPoll() {
		global $mosConfig_lang;

		$tmpl =& pollsScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'editPoll.html' );

		//$tmpl->addObject( )
		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Polls
*/
class HTML_poll {

	function showPolls( &$rows, &$pageNav, $option, &$lists ) {
		global $my;
	  	global $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm" id="pollsform" class="adminform">

		<?php
		pollsScreens::view( $lists );
		?>
				<table class="adminlist" id="moslist">
				<thead>
				<tr>
					<th width="5">
						<?php echo $_LANG->_( 'Num' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value=""  />
					</th>
					<th align="left">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Poll Title' ), 'm.title' ); ?>
					</th>
					<th width="10%" align="center" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Published' ), 'm.published' ); ?>
					</th>
					<th width="10%" align="center">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'Options' ), 'numoptions' ); ?>
					</th>
					<th width="10%" align="center">
						<?php echo $_LANG->_( 'Lag' ); ?>
					</th>
					<th width="20" align="center" nowrap="nowrap">
						<?php echo mosCommonHTML::tOrder( $lists, $_LANG->_( 'ID' ), 'm.id' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="7" class="center">
							<?php echo $pageNav->getPagesLinks(); ?>
						</th>
					</tr>
					<tr>
						<td colspan="7" class="center">
							<?php echo $_LANG->_( 'Display Num' ) ?>
							<?php echo  $pageNav->getLimitBox() ?>
							<?php echo $pageNav->getPagesCounter() ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
					$row = &$rows[$i];

					$link 	= 'index2.php?option=com_poll&amp;task=editA&amp;id='. $row->id;

					$task 	= $row->published ? 'unpublish' : 'publish';
					$img 	= $row->published ? 'tick.png' : 'publish_x.png';
					$alt 	= $row->published ? $_LANG->_( 'Published' ) : $_LANG->_( 'Unpublished' );

					$checked 	= mosAdminHTML::checkedOutProcessing( $row, $i );
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php echo $pageNav->rowNumber( $i ); ?>
						</td>
						<td>
							<?php echo $checked; ?>
						</td>
						<td>
							<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Poll' ); ?>" class="editlink">
								<?php echo $row->title; ?>
							</a>
						</td>
						<td align="center">
							<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
								<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" />
							</a>
						</td>
						<td align="center">
							<?php echo $row->numoptions; ?>
						</td>
						<td align="center">
							<?php echo $row->lag; ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
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

		<input type="hidden" name="tOrder" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrder_old" value="<?php echo $lists['tOrder']; ?>" />
		<input type="hidden" name="tOrderDir" value="" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}


	function editPoll( &$row, &$options, &$lists ) {
 	 	global $_LANG;

		mosMakeHtmlSafe( $row, ENT_QUOTES );
		?>
		<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.title.value == "") {
				alert( "<?php echo $_LANG->_( 'Poll must have a title' ); ?>" );
			} else if( isNaN( parseInt( form.lag.value ) ) ) {
				alert( "<?php echo $_LANG->_( 'Poll must have a non-zero lag time' ); ?>" );
			//} else if (form.menu.options.value == ""){
			//	alert( "Poll must have pages." );
			//} else if (form.adminForm.textfieldcheck.value == 0){
			//	alert( "Poll must have options." );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<?php
		pollsScreens::editPoll();
		?>

		<table class="adminform">
			<thead>
			<tr>
				<th colspan="4">
					<?php echo $_LANG->_( 'Details' ); ?>
				</th>
			</tr>
			</thead>
		<tr>
			<td width="10%">
				<?php echo $_LANG->_( 'Title' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="title" size="60" value="<?php echo $row->title; ?>" />
			</td>
   			<td width="20px">
   			</td>
			<td width="100%" rowspan="20" valign="top">
				<?php echo $_LANG->_( 'Show on menu items' ); ?>:
				<br />
				<?php echo $lists['select']; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo $_LANG->_( 'Lag' ); ?>
			</td>
			<td>
				<input class="inputbox" type="text" name="lag" size="10" value="<?php echo $row->lag; ?>" />
  		   		<?php echo $_LANG->_( '(seconds between votes)' ); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo $_LANG->_( 'Published' ); ?>
			</td>
			<td>
				<?php echo $lists['published']; ?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<br /><br />
				<?php echo $_LANG->_( 'Options' ); ?>:
			</td>
		</tr>
		<?php
		for ($i=0, $n=count( $options ); $i < $n; $i++ ) {
			?>
			<tr>
				<td>
					<?php echo ($i+1); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="polloption[<?php echo $options[$i]->id; ?>]" value="<?php echo htmlspecialchars( $options[$i]->text, ENT_QUOTES ); ?>" size="60" />
				</td>
			</tr>
			<?php
		}
		for (; $i < 12; $i++) {
			?>
			<tr>
				<td>
					<?php echo ($i+1); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="polloption[]" value="" size="60"/>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		</fieldset>
		</div>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_poll" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="textfieldcheck" value="<?php echo $n; ?>" />
		</form>
		<?php
	}

	function popupPreview() {
		global $mosConfig_live_site;
		global $database;
		global $_LANG;

		$pollid = mosGetParam( $_REQUEST, 'pollid', 0 );

		// load site template
		$sql = "SELECT template"
		. "\n FROM #__templates_menu"
		. "\n WHERE client_id = '0'"
		. "\n AND menuid = '0'"
		;
		$database->setQuery( $sql );
		$template = $database->loadResult();

		$query = "SELECT title"
		. "\n FROM #__polls"
		. "\n WHERE id = '$pollid'"
		;
		$database->setQuery( $query );
		$title = $database->loadResult();

		$query = "SELECT text"
		. "\n FROM #__poll_data"
		. "\n WHERE pollid = '$pollid'"
		. "\n order by id"
		;
		$database->setQuery( $query );
		$options = $database->loadResultArray();

		// xml prolog
		echo '<?xml version="1.0" encoding="'. $_LANG->iso() .'"?' .'>';
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_LANG->iso(); ?>" />
		<title><?php echo $_LANG->_( 'Poll Preview' ); ?></title>
		<link rel="stylesheet" href="<?php echo $mosConfig_live_site; ?>/templates/<?php echo $template; ?>/css/template_css<?php echo $_LANG->rtl() ? '_rtl': ''; ?>.css" type="text/css" />
		</head>
		<body>

		<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0" >
		<tr>
		    <td class="moduleheading" colspan="2">
		    	<?php echo $title; ?>
		    </td>
		</tr>
		<?php
		foreach ($options as $text) {
			if ( $text != '' ) {
				?>
				<tr>
			    	<td valign="top" height="30">
			    		<input type="radio" name="poll" value="<?php echo $text; ?>" />
			    	</td>
					<td class="poll" width="100%" valign="top">
						<?php echo $text; ?>
					</td>
				</tr>
				<?php
			}
		}
		?>
		<tr>
		    <td valign="middle" height="50" colspan="2" align="center">
			    <input type="button" name="submit" value="<?php echo $_LANG->_( 'Vote' ); ?>" />&nbsp;&nbsp;
			    <input type="button" name="result" value="<?php echo $_LANG->_( 'Results' ); ?>" />
		    </td>
		</tr>
		<tr>
		    <td align="center" colspan="2">
			    <a href="#" onClick="window.close()" class="editlink">
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