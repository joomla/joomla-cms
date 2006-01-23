<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Contact
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Contact
*/
class HTML_contact {

	function showContacts( &$rows, &$pageNav, $search, $option, &$lists ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<td>
			<?php echo JText::_( 'Filter' ); ?>:
			<input type="text" name="search" value="<?php echo $search;?>" class="inputbox" onchange="document.adminForm.submit();" />
			</td>
			<td align="right">
			<?php 
			echo $lists['catid'];
			echo $lists['state'];
			?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="20">
			<?php echo JText::_( 'Num' ); ?>
			</th>
			<th width="20" class="title">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
			</th>
			<th class="title">
			<?php echo JText::_( 'Name' ); ?>
			</th>
			<th width="5%" class="title" nowrap="true">
			<?php echo JText::_( 'Published' ); ?>
			</th>
			<th colspan="2" nowrap="nowrap" width="5%">
			<?php echo JText::_( 'Reorder' ); ?>
			</th>
			<th width="15%" class="title">
			<?php echo JText::_( 'Category' ); ?>
			</th>
			<th class="title" nowrap="nowrap" width="15%">
			<?php echo JText::_( 'Linked to User' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count($rows); $i < $n; $i++) {
			$row = $rows[$i];

			$link 	= ampReplace( 'index2.php?option=com_contact&task=editA&hidemainmenu=1&id='. $row->id );

			$img 	= $row->published ? 'tick.png' : 'publish_x.png';
			$task 	= $row->published ? 'unpublish' : 'publish';
			$alt 	= $row->published ? JText::_( 'Published' ) : JText::_( 'Unpublished' );

			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );

			$row->cat_link 	= ampReplace( 'index2.php?option=com_categories&section=com_contact_details&task=editA&hidemainmenu=1&id='. $row->catid );
			$row->user_link	= ampReplace( 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->user_id );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
				<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<td>
				<?php echo $checked; ?>
				</td>
				<td>
				<?php
				if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
					echo $row->name;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Contact' ); ?>">
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
				<td>
				<?php echo $pageNav->orderUpIcon( $i, ( $row->catid == @$rows[$i-1]->catid ) ); ?>
				</td>
				<td>
				<?php echo $pageNav->orderDownIcon( $i, $n, ( $row->catid == @$rows[$i+1]->catid ) ); ?>
				</td>
				<td>
				<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
				<?php echo $row->category; ?>
				</a>
				</td>
				<td>
				<a href="<?php echo $row->user_link; ?>" title="<?php echo JText::_( 'Edit User' ); ?>">
				<?php echo $row->user; ?>
				</a>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}


	function editContact( &$row, &$lists, $option, &$params ) 
	{
		if ($row->image == '') {
			$row->image = 'blank.png';
		}
		
		mosCommonHTML::loadOverlib();

		$tabs = new mosTabs(0);

		mosMakeHtmlSafe( $row, ENT_QUOTES, 'misc' );
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
			if ( form.name.value == "" ) {
				alert( "<?php echo JText::_( 'You must provide a name.', true ); ?>" );
			} else if ( form.catid.value == 0 ) {
				alert( "<?php echo JText::_( 'Please select a Category.', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		//-->
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
		<tr>
			<td width="60%" valign="top">
				<table width="100%" class="adminform">
				<tr>
					<th colspan="2">
					<?php echo JText::_( 'Contact Details' ); ?>
					</th>
				<tr>
				<tr>
					<td width="20%" align="right">
					<?php echo JText::_( 'Category' ); ?>:
					</td>
					<td width="40%">
					<?php echo $lists['catid'];?>
					</td>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo JText::_( 'Linked to User' ); ?>:
					</td>
					<td >
					<?php echo $lists['user_id'];?>
					</td>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo JText::_( 'Name' ); ?>:
					</td>
					<td >
					<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo $row->name; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_( 'Contact\'s Position' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="con_position" size="50" maxlength="50" value="<?php echo $row->con_position; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_( 'E-mail' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="email_to" size="50" maxlength="100" value="<?php echo $row->email_to; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_( 'Street Address' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="address" size="50" value="<?php echo $row->address; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_( 'Town/Suburb' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="suburb" size="50" maxlength="50" value="<?php echo $row->suburb;?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_( 'State/County' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="state" size="50" maxlength="20" value="<?php echo $row->state;?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_( 'Country' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="country" size="50" maxlength="50" value="<?php echo $row->country;?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_( 'Postal Code/ZIP' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="postcode" size="25" maxlength="10" value="<?php echo $row->postcode; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_( 'Telephone' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="telephone" size="25" maxlength="25" value="<?php echo $row->telephone; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_( 'Fax' ); ?>:
					</td>
					<td>
					<input class="inputbox" type="text" name="fax" size="25" maxlength="25" value="<?php echo $row->fax; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
					<?php echo JText::_( 'Miscellaneous Info' ); ?>:
					</td>
					<td>
					<textarea name="misc" rows="5" cols="50" class="inputbox"><?php echo $row->misc; ?></textarea>
					</td>
				</tr>
				<tr>
				</table>
			</td>
			<td width="40%" valign="top">
				<?php
				$title = JText::_( 'Publishing' );
				$tabs->startPane("content-pane");
				$tabs->startTab( $title, "publish-page" );
				?>
				<table width="100%" class="adminform">
				<tr>
					<th colspan="2">
					<?php echo JText::_( 'Publishing Info' ); ?>
					</th>
				<tr>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_( 'Published' ); ?>:
					</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_( 'Ordering' ); ?>:
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_( 'Access' ); ?>:
					</td>
					<td>
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;
					</td>
				</tr>
				</table>
				<?php
				$title = JText::_( 'Images' );
				$tabs->endTab();
				$tabs->startTab( $title, "images-page" );
				?>
				<table width="100%" class="adminform">
				<tr>
					<th colspan="2">
					<?php echo JText::_( 'Image Info' ); ?>
					</th>
				<tr>
				<tr>
					<td  width="20%">
					<?php echo JText::_( 'Image' ); ?>:
					</td>
					<td >
					<?php echo $lists['image']; ?>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
					<script language="javascript" type="text/javascript">
					if (document.forms[0].image.options.value!=''){
						jsimg='../images/stories/' + getSelectedValue( 'adminForm', 'image' );
					} else {
						jsimg='../images/M_images/blank.png';
					}
					document.write('<img src=' + jsimg + ' name="imagelib" width="100" height="100" border="2" alt="<?php echo JText::_( 'Preview' ); ?>" />');
					</script>
					</td>
				</tr>
				</table>
				<?php
				$title = JText::_( 'Parameters' );
				$tabs->endTab();
				$tabs->startTab( $title, "params-page" );
				?>
				<table class="adminform">
				<tr>
					<th>
					<?php echo JText::_( 'Parameters' ); ?>
					</th>
				</tr>
				<tr>
					<td>
					<?php echo JText::_( 'DESCPARAMWHENCLICKCONTAC' ); ?>
					<br /><br />
					</td>
				</tr>
				<tr>
					<td>
					<?php echo $params->render();?>
					</td>
				</tr>
				</table>
				<?php
				$tabs->endTab();
				$tabs->endPane();
				?>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>
