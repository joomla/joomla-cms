<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>		<tr>
			<td class="key">
				<label for="data[order][history][history_reason]">
					<?php echo JText::_( 'MODIFICATION_REASON' ); ?>
				</label>
			</td>
			<td>
				<textarea cols="60" rows="10" name="data[order][history][history_reason]"></textarea>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][history][history_notified]">
					<?php echo JText::_( 'NOTIFY_CUSTOMER' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[order][history][history_notified]" , 'onchange="var display=\'none\'; if(this.value==1)display=\'\';document.getElementById(\'notification_area\').style.display=display;"',0	); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2" id="notification_area" style="display:none">
				<fieldset class="adminform" id="htmlfieldset">
					<legend><?php echo JText::_( 'NOTIFICATION' ); ?></legend>
					<?php $this->setLayout('mailform'); echo $this->loadTemplate();?>
				</fieldset>
			</td>
		</tr>
