<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>					<table class="admintable table" width="280px" style="margin:auto">
						<tr>
							<td class="key">
								<label for="zone_name">
									<?php echo JText::_( 'HIKA_NAME' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[zone][zone_name]" value="<?php echo $this->escape(@$this->element->zone_name); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="zone_name_english">
									<?php echo JText::_( 'ZONE_NAME_ENGLISH' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[zone][zone_name_english]" value="<?php echo $this->escape(@$this->element->zone_name_english); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="zone_code_2">
									<?php echo JText::_( 'ZONE_CODE_2' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[zone][zone_code_2]" value="<?php echo @$this->element->zone_code_2; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="zone_code_3">
									<?php echo JText::_( 'ZONE_CODE_3' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[zone][zone_code_3]" value="<?php echo @$this->element->zone_code_3; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="zone_type">
									<?php echo JText::_( 'ZONE_TYPE' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->type->display('data[zone][zone_type]',@$this->element->zone_type,true); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="zone_published">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[zone][zone_published]" , '',@$this->element->zone_published	); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="zone_published">
									<?php echo JText::_( 'CURRENCY' ); ?>
								</label>
							</td>
							<td>
							<?php
								if(hikashop_level(2)){
									$currencytype = hikashop_get('type.currency');
									$currencytype->displayType='all';
									echo $currencytype->display('data[zone][zone_currency_id]',@$this->element->zone_currency_id);
								}else{
									echo hikashop_getUpgradeLink('business');
								} ?>
							</td>
						</tr>
					</table>
					<input type="hidden" name="data[zone][zone_namekey]" value="<?php echo @$this->element->zone_namekey; ?>" />
