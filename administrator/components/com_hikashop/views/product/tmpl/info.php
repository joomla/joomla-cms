<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>			<table class="admintable table" width="100%">
				<tr class="hikashop_product_code_row">
					<td class="key">
							<?php echo JText::_( 'PRODUCT_CODE' ); ?>
					</td>
					<td>
<?php if($this->element->product_type == 'main') { ?>
						<input type="text" name="data[product][product_code]" value="<?php echo $this->escape(@$this->element->product_code); ?>" />
<?php } else { ?>
						<input type="hidden" name="data[product][product_code]" value="<?php echo $this->escape(@$this->element->product_code); ?>" />
						<?php echo @$this->element->product_code; ?>
<?php } ?>
					</td>
				</tr>
				<tr class="hikashop_product_tax_id_row">
					<td class="key">
							<?php echo JText::_( 'TAXATION_CATEGORY' ); ?>
					</td>
					<td>
						<?php echo $this->categoryType->display('data[product][product_tax_id]',@$this->element->product_tax_id);?>
					</td>
				</tr>
				<tr class="hikashop_product_manufacturer_id_row">
					<td class="key">
							<?php echo JText::_( 'MANUFACTURER' ); ?>
					</td>
					<td>
						<?php echo $this->manufacturerType->display('data[product][product_manufacturer_id]',@$this->element->product_manufacturer_id);?>
					</td>
				</tr>

				<?php
				if(hikashop_level(1) && $this->config->get('product_contact',0)==1){ ?>
				<tr class="hikashop_product_contact_row">
					<td class="key">
							<?php echo JText::_('DISPLAY_CONTACT_BUTTON'); ?>
					</td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', "data[product][product_contact]" , '',@$this->element->product_contact ); ?>
					</td>
				</tr>
				<?php }
				if(hikashop_level(1) && $this->config->get('product_waitlist',0)==1){ ?>
				<tr class="hikashop_product_waitlist_row">
					<td class="key">
							<?php echo JText::_('DISPLAY_WAITLIST_BUTTON'); ?>
					</td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', "data[product][product_waitlist]" , '',@$this->element->product_waitlist ); ?>
					</td>
				</tr>
				<?php } ?>
				<tr class="hikashop_product_layout_row">
					<td class="key">
							<?php echo JText::_( 'LAYOUT_ON_PRODUCT_PAGE' ); ?>
					</td>
					<td>
						<?php echo $this->productDisplayType->display('data[product][product_layout]' , @$this->element->product_layout); ?>
					</td>
				</tr>
				<tr class="hikashop_product_quantity_layout_row">
					<td class="key">
							<?php echo JText::_( 'QUANTITY_LAYOUT_ON_PRODUCT_PAGE' ); ?>
					</td>
					<td>
						<?php echo $this->quantityDisplayType->display('data[product][product_quantity_layout]' , @$this->element->product_quantity_layout); ?>
					</td>
				</tr>
				<?php
				$this->setLayout('common');
				echo $this->loadTemplate();
				?>
			</table>
