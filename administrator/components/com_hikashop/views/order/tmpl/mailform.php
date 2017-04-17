<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!empty($this->element->mail->dst_email) && is_array($this->element->mail->dst_email))
	$this->element->mail->dst_email = implode(',',$this->element->mail->dst_email);
if(!empty($this->element->mail->cc_email) && is_array($this->element->mail->cc_email))
	$this->element->mail->cc_email = implode(',',$this->element->mail->cc_email);
if(!empty($this->element->mail->bcc_email) && is_array($this->element->mail->bcc_email))
	$this->element->mail->bcc_email = implode(',',$this->element->mail->bcc_email);
?>
					<table class="table">
						<tr>
							<td class="key">
								<label for="data[order][mail][from_email]">
									<?php echo JText::_( 'FROM_ADDRESS' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[order][mail][from_email]" size="120" value="<?php echo $this->escape($this->element->mail->from_email);?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[order][mail][from_name]">
									<?php echo JText::_( 'FROM_NAME' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[order][mail][from_name]" size="120" value="<?php echo $this->escape($this->element->mail->from_name);?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[order][mail][dst_email]">
									<?php echo JText::_( 'TO_ADDRESS' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[order][mail][dst_email]" size="120" value="<?php echo $this->escape($this->element->mail->dst_email);?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[order][mail][dst_name]">
									<?php echo JText::_( 'TO_NAME' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[order][mail][dst_name]" size="120" value="<?php echo $this->escape($this->element->mail->dst_name);?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[order][mail][subject]">
									<?php echo JText::_( 'EMAIL_SUBJECT' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[order][mail][subject]" size="120" value="<?php echo $this->escape($this->element->mail->subject);?>" />
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<label for="hikashop_mail_body">
									<?php echo JText::_( 'HTML_VERSION' ); ?>
								</label>
								<?php echo $this->editor->display(); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[order][mail][altbody]">
									<?php echo JText::_( 'TEXT_VERSION' ); ?>
								</label>
							</td>
							<td>
								<textarea cols="60" rows="10" name="data[order][mail][altbody]"><?php echo $this->escape($this->element->mail->altbody); ?></textarea>
							</td>
						</tr>
					</table>
					<input type="hidden" name="data[order][mail][html]" value="<?php echo $this->element->mail->html;?>" />
					<input type="hidden" name="data[order][mail][mail_name]" value="<?php echo $this->element->mail->mail_name;?>" />
					<input type="hidden" name="data[order][mail][cc_email]" value="<?php echo $this->element->mail->cc_email;?>" />
					<input type="hidden" name="data[order][mail][bcc_email]" value="<?php echo $this->element->mail->bcc_email;?>" />
