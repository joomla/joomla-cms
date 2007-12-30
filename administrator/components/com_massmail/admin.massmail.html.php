<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Massmail
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Massmail
*/
class HTML_massmail
{
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

		<form action="index.php" name="adminForm" method="post">

		<div class="col width-30">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Details' ); ?></legend>

				<table class="admintable">
				<tr>
					<td class="key">
						<label for="mm_recurse">
							<?php echo JText::_( 'Mail to Child Groups' ); ?>:
						</label>
					</td>
					<td>
						<input type="checkbox" name="mm_recurse" id="mm_recurse" value="RECURSE" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="mm_mode">
							<?php echo JText::_( 'Send in HTML mode' ); ?>:
						</label>
					</td>
					<td>
						<input type="checkbox" name="mm_mode" id="mm_mode" value="1" />
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<label for="mm_group">
							<?php echo JText::_( 'Group' ); ?>:
						</label>
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td colspan="2" valign="top">
						<?php echo $lists['gid']; ?>
					</td>
				</tr>
				</table>
			</fieldset>
		</div>

		<div class="col width-70">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Message' ); ?></legend>

				<table class="admintable">
				<tr>
					<td class="key">
						<label for="mm_subject">
							<?php echo JText::_( 'Subject' ); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="mm_subject" id="mm_subject" value="" size="150" />
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<label for="mm_message">
							<?php echo JText::_( 'Message' ); ?>:
						</label>
					</td>
					<td id="mm_pane" >
						<textarea rows="20" cols="150" name="mm_message" id="mm_message" class="inputbox"></textarea>
					</td>
				</tr>
				</table>
			</fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}