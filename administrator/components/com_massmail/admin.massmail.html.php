<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Massmail
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
* @subpackage Massmail
*/
class HTML_massmail {
	function messageForm( &$lists, $option ) {
		?>
		<script language="javascript" type="text/javascript">
			function submitbutton(pressbutton) {
				var form = document.adminForm;
				if (pressbutton == 'cancel') {
					submitform( pressbutton );
					return;
				}
				// do field validation
				if (form.mm_subject.value == ""){
					alert( "<?php echo JText::_( 'Please fill in the subject', true ); ?>" );
				} else if (getSelectedValue('adminForm','mm_group') < 0){
					alert( "<?php echo JText::_( 'Please select a group', true ); ?>" );
				} else if (form.mm_message.value == ""){
					alert( "<?php echo JText::_( 'Please fillin the message', true ); ?>" );
				} else {
					submitform( pressbutton );
				}
			}
		</script>

		<form action="index2.php" name="adminForm" method="post">
		
		<table class="adminform">
		<thead>
		<tr>
			<th colspan="2">
				<?php echo JText::_( 'Details' ); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th colspan="2">
				&nbsp;
			</th>
		</tr>
		</tfoot>
		<tbody>
		<tr>
			<td width="150" valign="top">
				<?php echo JText::_( 'Group' ); ?>:
			</td>
			<td width="85%">
				<?php echo $lists['gid']; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Mail to Child Groups' ); ?>:
			</td>
			<td>
				<input type="checkbox" name="mm_recurse" value="RECURSE" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Send in HTML mode' ); ?>:
			</td>
			<td>
				<input type="checkbox" name="mm_mode" value="1" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Subject' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="mm_subject" value="" size="50" />
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo JText::_( 'Message' ); ?>:
			</td>
			<td>
				<textarea cols="80" rows="25" name="mm_message" class="inputbox"></textarea>
			</td>
		</tr>
		</tbody>
		</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>