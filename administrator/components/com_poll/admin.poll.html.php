<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
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
* @subpackage Polls
*/
class HTML_poll {
	function showPolls( &$rows, &$pageNav, $option, &$lists ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
		<form action="index2.php?option=com_poll" method="post" name="adminForm">
		
		<table class="adminheading">
		<tr>
			<td align="left" valign="top" nowrap="nowrap">
			</td>
			<td align="right" valign="top" nowrap="nowrap">
				<?php
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="5">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th  class="title">
				<?php mosCommonHTML :: tableOrdering( 'Poll Title', 'm.title', $lists ); ?>
			</th>
			<th width="10%" align="center">
				<?php mosCommonHTML :: tableOrdering( 'Published', 'm.published', $lists ); ?>
			</th>
			<th width="10%" align="center">
				<?php mosCommonHTML :: tableOrdering( 'Votes', 'm.voters', $lists ); ?>
			</th>
			<th width="10%" align="center">
				<?php mosCommonHTML :: tableOrdering( 'Options', 'numoptions', $lists ); ?>
			</th>
			<th width="10%" align="center">
				<?php mosCommonHTML :: tableOrdering( 'Lag', 'm.lag', $lists ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];

			$link 	= ampReplace( 'index2.php?option=com_poll&task=editA&hidemainmenu=1&id='. $row->id );

			$task 	= $row->published ? 'unpublish' : 'publish';
			$img 	= $row->published ? 'publish_g.png' : 'publish_x.png';
			$alt 	= $row->published ? JText::_( 'Published' ) : JText::_( 'Unpublished' );

			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $pageNav->rowNumber( $i ); ?>
				</td>
				<td>
					<?php echo $checked; ?>
				</td>
				<td>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Poll' ); ?>">
						<?php echo $row->title; ?></a>
				</td>
				<td align="center">
					<a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
						<img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" /></a>
				</td>
				<td align="center">
					<?php echo $row->voters; ?>
				</td>
				<td align="center">
					<?php echo $row->numoptions; ?>
				</td>
				<td align="center">
					<?php echo $row->lag; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		
		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}


	function editPoll( &$row, &$options, &$lists ) 
	{
		mosMakeHtmlSafe( $row, ENT_QUOTES );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.title.value == "") {
				alert( "<?php echo JText::_( 'Poll must have a title', true ); ?>" );
			} else if( isNaN( parseInt( form.lag.value ) ) ) {
				alert( "<?php echo JText::_( 'Poll must have a non-zero lag time', true ); ?>" );
			//} else if (form.menu.options.value == ""){
			//	alert( "Poll must have pages." );
			//} else if (form.adminForm.textfieldcheck.value == 0){
			//	alert( "Poll must have options." );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminform">
		<tr>
			<th colspan="4">
			<?php echo JText::_( 'Details' ); ?>
			</th>
		</tr>
		<tr>
			<td width="10%">
			<?php echo JText::_( 'Title' ); ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="title" size="60" value="<?php echo $row->title; ?>" />
			</td>
			<td width="20px">&nbsp;

			</td>
			<td width="100%" rowspan="20" valign="top">
			<?php echo JText::_( 'Show on menu items' ); ?>:
			<br />
			<?php echo $lists['select']; ?>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JText::_( 'Lag' ); ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="lag" size="10" value="<?php echo $row->lag; ?>" /> <?php echo JText::_( '(seconds between votes)' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			<br /><br />
			<?php echo JText::_( 'Options' ); ?>:
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
				<input class="inputbox" type="text" name="polloption[<?php echo $options[$i]->id; ?>]" value="<?php echo stripslashes($options[$i]->text); ?>" size="60" />
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

		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_poll" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="textfieldcheck" value="<?php echo $n; ?>" />
		</form>
		<?php
	}

	function previewPoll($title, $options)
	{
		?>
		<form>
		<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0" >
		<tr>
			<td class="moduleheading" colspan="2"><?php echo $title; ?></td>
		</tr>
		<?php foreach ($options as $text)
		{
			if ($text <> "")
			{?>
			<tr>
				<td valign="top" height="30"><input type="radio" name="poll" value="<?php echo $text; ?>"></td>
				<td class="poll" width="100%" valign="top"><?php echo $text; ?></td>
			</tr>
			<?php }
		} ?>
		<tr>
			<td valign="middle" height="50" colspan="2" align="center"><input type="button" name="submit" value="<?php echo JText::_( 'Vote' ); ?>">&nbsp;&nbsp;<input type="button" name="result" value="<?php echo JText::_( 'Results' ); ?>"></td>
		</tr>
		<tr>
			<td align="center" colspan="2"><a href="#" onClick="window.close()"><?php echo JText::_( 'Close' ); ?></a></td>
		</tr>
		</table>
		</form>
		<?php
	}
}
?>
