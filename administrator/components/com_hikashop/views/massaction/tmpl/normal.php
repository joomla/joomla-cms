<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>					<table class="admintable table"  width="100%">
						<tr>
							<td class="key">
								<label for="data[massaction][massaction_name]">
									<?php echo JText::_( 'HIKA_NAME' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $this->massaction_name_input; ?>" value="<?php echo $this->escape(@$this->element->massaction_name); ?>" />
								<?php if(isset($this->massaction_name_published)){
										$publishedid = 'published-'.$this->massaction_name_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->massaction_name_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[massaction][massaction_description]">
									<?php echo JText::_( 'HIKA_DESCRIPTION' ); ?>
								</label>
							</td>
							<td>
								<textarea cols="71" name="<?php echo $this->massaction_description_input; ?>" ><?php echo $this->escape(@$this->element->massaction_description); ?></textarea>
								<?php if(isset($this->massaction_description_published)){
										$publishedid = 'published-'.$this->massaction_description_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->massaction_description_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
					</table>
