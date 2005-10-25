<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Banners
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Banners
*/
class HTML_banners {

	function showBanners( &$rows, &$pageNav, $option ) {
		global $my, $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th>
			<?php echo $_LANG->_( 'Banner Manager' ); ?>
			</th>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="20">
            <?php echo $_LANG->_( 'Num' ); ?>
			</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th nowrap class="title">
			<?php echo $_LANG->_( 'Banner Name' ); ?>
			</th>
			<th width="10%" nowrap>
			<?php echo $_LANG->_( 'Published' ); ?>
			</th>
			<th width="11%" nowrap>
			<?php echo $_LANG->_( 'Impressions Made' ); ?>
			</th>
			<th width="11%" nowrap>
			<?php echo $_LANG->_( 'Impressions Left' ); ?>
			</th>
			<th width="8%">
			<?php echo $_LANG->_( 'Clicks' ); ?>
			</th>
			<th width="8%" nowrap>
			<?php echo $_LANG->_( '% Clicks' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];

			$row->id 	= $row->bid;
			$link 		= 'index2.php?option=com_banners&task=editA&hidemainmenu=1&id='. $row->id;

			$impleft 	= $row->imptotal - $row->impmade;
			if( $impleft < 0 ) {
				$impleft 	= "unlimited";
			}

			if ( $row->impmade != 0 ) {
				$percentClicks = substr(100 * $row->clicks/$row->impmade, 0, 5);
			} else {
				$percentClicks = 0;
			}

			$task 	= $row->showBanner ? 'unpublish' : 'publish';
			$img 	= $row->showBanner ? 'publish_g.png' : 'publish_x.png';
			$alt 	= $row->showBanner ? $_LANG->_( 'Published' ) : $_LANG->_( 'Unpublished' );

			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center">
				<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<td align="center">
				<?php echo $checked; ?>
				</td>
				<td>
				<?php
				if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
					echo $row->name;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Banner' ); ?>">
					<?php echo $row->name; ?>
					</a>
					<?php
				}
				?>
				</td>
				<td align="center">
				<a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
				<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" />
				</a>
				</td>
				<td align="center">
				<?php echo $row->impmade;?>
				</td>
				<td align="center">
				<?php echo $impleft;?>
				</td>
				<td align="center">
				<?php echo $row->clicks;?>
				</td>
				<td align="center">
				<?php echo $percentClicks;?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="<?php echo $option; ?>">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<input type="hidden" name="hidemainmenu" value="0">
		</form>
		<?php
	}

	function bannerForm( &$_row, &$lists, $_option ) {
		global $_LANG;

		mosMakeHtmlSafe( $_row, ENT_QUOTES, 'custombannercode' );
		?>
		<script language="javascript">
		<!--
		function changeDisplayImage() {
			if (document.adminForm.imageurl.value !='') {
				document.adminForm.imagelib.src='../images/banners/' + document.adminForm.imageurl.value;
			} else {
				document.adminForm.imagelib.src='images/blank.png';
			}
		}
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.name.value == "") {
				alert( "<?php echo $_LANG->_( 'You must provide a banner name.' ); ?>" );
			} else if (getSelectedValue('adminForm','cid') < 1) {
				alert( "<?php echo $_LANG->_( 'Please select a client.' ); ?>" );
			} else if (!getSelectedValue('adminForm','imageurl')) {
				alert( "<?php echo $_LANG->_( 'Please select an image.' ); ?>" );
			} else if (form.clickurl.value == "") {
				alert( "<?php echo $_LANG->_( 'Please fill in the URL for the banner.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th>
			<?php echo $_LANG->_( 'Banner' ); ?>:
			<small>
			<?php echo $_row->cid ? $_LANG->_( 'Edit' ) : $_LANG->_( 'New' );?>
			</small>
			</th>
		</tr>
		</table>

		<table class="adminform">
		<tr>
			<th colspan="2">
			<?php echo $_LANG->_( 'Details' ); ?>
			</th>
		</tr>
		<tr>
			<td width="20%">
			<?php echo $_LANG->_( 'Banner Name' ); ?>:
			</td>
			<td width="80%">
			<input class="inputbox" type="text" name="name" value="<?php echo $_row->name;?>">
			</td>
		</tr>
		<tr>
			<td>
			<?php echo $_LANG->_( 'Client Name' ); ?>:
			</td>
			<td >
			<?php echo $lists['cid']; ?>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo $_LANG->_( 'Impressions Purchased' ); ?>:
			</td>
			<?php
			if ($_row->imptotal == "0") {
				$unlimited="checked";
				$_row->imptotal="";
			} else {
				$unlimited = "";
			}
			?>
			<td>
			<input class="inputbox" type="text" name="imptotal" size="12" maxlength="11" value="<?php echo $_row->imptotal;?>">&nbsp;<?php echo $_LANG->_( 'Unlimited' ); ?> <input type="checkbox" name="unlimited" <?php echo $unlimited;?>>
			</td>
		</tr>
		<tr>
			<td valign="top">
			<?php echo $_LANG->_( 'Banner URL' ); ?>:
			</td>
			<td >
			<?php echo $lists['imageurl']; ?>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo $_LANG->_( 'Show Banner' ); ?>:
			</td>
			<td>
			<?php echo $lists['showBanner']; ?>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo $_LANG->_( 'Click URL' ); ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="clickurl" size="50" maxlength="200" value="<?php echo $_row->clickurl;?>">
			</td>
		</tr>
		<tr>
			<td valign="top">
			<?php echo $_LANG->_( 'Custom banner code' ); ?>:
			</td>
			<td>
			<textarea class="inputbox" cols="70" rows="5" name="custombannercode"><?php echo $_row->custombannercode;?></textarea>
			</td>
		</tr>
		<tr >
			<td valign="top" align="right">
			<?php echo $_LANG->_( 'Clicks' ); ?>
           <br />
			<input name="reset_hits" type="button" class="button" value="<?php echo $_LANG->_( 'Reset Clicks' ); ?>" onClick="submitbutton('resethits');">
			</td>
			<td colspan="2">
			<?php echo $_row->clicks;?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			</td>
		</tr>
		<tr>
			<td valign="top">
			<?php echo $_LANG->_( 'Banner Image' ); ?>:
			</td>
			<td valign="top">
			<?php
			if (eregi("swf", $_row->imageurl)) {
				?>
				<img src="images/blank.png" name="imagelib">
				<?php
			} elseif (eregi("gif|jpg|png", $_row->imageurl)) {
				?>
				<img src="../images/banners/<?php echo $_row->imageurl; ?>" name="imagelib">
				<?php
			} else {
				?>
				<img src="images/blank.png" name="imagelib">
				<?php
			}
			?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $_option; ?>">
		<input type="hidden" name="bid" value="<?php echo $_row->bid; ?>">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="impmade" value="<?php echo $_row->impmade; ?>">
		</form>
		<?php
	}
}

/**
* Banner clients
* @package Joomla
*/
class HTML_bannerClient {

	function showClients( &$rows, &$pageNav, $option ) {
		global $my, $_LANG;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th>
			<?php echo $_LANG->_( 'Banner Client Manager' ); ?>
			</th>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="20">
            <?php echo $_LANG->_( 'Num' ); ?>
			</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th nowrap class="title">
			<?php echo $_LANG->_( 'Client Name' ); ?>
			</th>
			<th nowrap class="title">
			<?php echo $_LANG->_( 'Contact' ); ?>
			</th>
			<th align="center" nowrap>
			<?php echo $_LANG->_( 'No. of Active Banners' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];

			$row->id 	= $row->cid;
			$link 		= 'index2.php?option=com_banners&task=editclientA&hidemainmenu=1&id='. $row->id;

			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td width="20" align="center">
				<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<td width="20">
				<?php echo $checked; ?>
				</td>
				<td width="40%">
				<?php
				if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
					echo $row->name;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo $_LANG->_( 'Edit Banner Client' ); ?>">
					<?php echo $row->name; ?>
					</a>
					<?php
				}
				?>
				</td>
				<td width="40%">
				<?php echo $row->contact;?>
				</td>
				<td width="20%" align="center">
				<?php echo $row->bid;?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		<?php echo $pageNav->getListFooter(); ?>
		<input type="hidden" name="option" value="<?php echo $option; ?>">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<input type="hidden" name="hidemainmenu" value="0">
		</form>
		<?php
	}

	function bannerClientForm( &$row, $option ) {
		global $_LANG;

		mosMakeHtmlSafe( $row, ENT_QUOTES, 'extrainfo' );
		?>
		<script language="javascript">
		<!--
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancelclient') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.name.value == "") {
				alert( "<?php echo $_LANG->_( 'Please fill in the Client Name.' ); ?>" );
			} else if (form.contact.value == "") {
				alert( "<?php echo $_LANG->_( 'Please fill in the Contact Name.' ); ?>" );
			} else if (form.email.value == "") {
				alert( "<?php echo $_LANG->_( 'Please fill in the Contact Email.' ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<table class="adminheading">
		<tr>
			<th>
			<?php echo $_LANG->_( 'Banner Client' ); ?>:
			<small>
			<?php echo $row->cid ? $_LANG->_( 'Edit' ) : $_LANG->_( 'New' );?>
			</small>
			</th>
		</tr>
		</table>

		<form action="index2.php" method="post" name="adminForm">
		<table class="adminform">
		<tr>
			<th colspan="2">
			<?php echo $_LANG->_( 'Details' ); ?>
			</th>
		</tr>
		<tr>
			<td width="10%">
			<?php echo $_LANG->_( 'Client Name' ); ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="name" size="30" maxlength="60" valign="top" value="<?php echo $row->name; ?>">
			</td>
		</tr>
		<tr>
			<td width="10%">
			<?php echo $_LANG->_( 'Contact Name' ); ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="contact" size="30" maxlength="60" value="<?php echo $row->contact; ?>">
			</td>
		</tr>
		<tr>
			<td width="10%">
			<?php echo $_LANG->_( 'Contact Email' ); ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="email" size="30" maxlength="60" value="<?php echo $row->email; ?>">
			</td>
		</tr>
		<tr>
			<td valign="top">
			<?php echo $_LANG->_( 'Extra Info' ); ?>:
			</td>
			<td>
			<textarea class="inputbox" name="extrainfo" cols="60" rows="10"><?php echo str_replace('&','&amp;',$row->extrainfo);?></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>">
		<input type="hidden" name="cid" value="<?php echo $row->cid; ?>">
		<input type="hidden" name="task" value="">
		</form>
		<?php
	}
}
?>
