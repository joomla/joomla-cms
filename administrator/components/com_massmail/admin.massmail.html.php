<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Massmail
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Massmail
 */
class HTML_massmail
{
	function messageForm(&$lists, $option) {
		?>
		<script language="javascript" type="text/javascript">
			function submitbutton(pressbutton) {
				var form = document.adminForm;
				if (pressbutton == 'cancel') {
					submitform(pressbutton);
					return;
				}
				// do field validation
				if (form.mm_subject.value == ""){
					alert("<?php echo JText::_('Please fill in the subject', true); ?>");
				} else if (getSelectedValue('adminForm','mm_group') < 0){
					alert("<?php echo JText::_('Please select a group', true); ?>");
				} else if (form.mm_message.value == ""){
					alert("<?php echo JText::_('Please fillin the message', true); ?>");
				} else {
					submitform(pressbutton);
				}
			}
		</script>

		<form action="index.php" name="adminForm" method="post">

		<div class="width-70 fltlft">
			<fieldset class="adminform">
				<legend><?php echo JText::_('Message'); ?></legend>
			
				<label for="mm_subject"><?php echo JText::_('Subject'); ?>:</label>
					
				<input class="inputbox" type="text" name="mm_subject" id="mm_subject" value="" size="150" />
					
				<label for="mm_message"><?php echo JText::_('Message'); ?>:</label>

				<div id="mm_pane" >
					<textarea rows="20" cols="150" name="mm_message" id="mm_message" class="inputbox"></textarea>
				</div>

			</fieldset>
		</div>
		
		<div class="width-30 fltrt">
			<fieldset class="adminform">
				<legend><?php echo JText::_('Details'); ?></legend>

				<label for="mm_recurse"><?php echo JText::_('Mail to Child Groups'); ?>:</label>
				
				<input type="checkbox" name="mm_recurse" id="mm_recurse" value="RECURSE" />
				
				<label for="mm_mode"><?php echo JText::_('Send in HTML mode'); ?>:</label>
					
				<input type="checkbox" name="mm_mode" id="mm_mode" value="1" />
			
				<label for="mm_group"><?php echo JText::_('Group'); ?>:</label>
					
				<?php echo $lists['group']; ?>
				
				<label for="mm_bcc" title="<?php echo JText::_('Send as Blind Carbon Copy'); ?>">
						<?php echo JText::_('Recipients as BCC'); ?>:
				</label>
					
				<input type="checkbox" name="mm_bcc" id="mm_bcc" value="1" checked="checked" />
			</fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
		<?php
	}
}
