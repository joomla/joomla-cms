<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<div>
	<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=discount" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-discount">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
<?php } else { ?>
<div id="page-discount" class="row-fluid">
	<div class="span6">
<?php } ?>
					<table class="admintable table" style="margin:auto">
						<tr>
							<td class="key">
									<?php echo JText::_( 'DISCOUNT_CODE' ); ?>
							</td>
							<td>
								<input type="text" name="data[discount][discount_code]" value="<?php echo $this->escape(@$this->element->discount_code); ?>" />*
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'DISCOUNT_TYPE' ); ?>
							</td>
							<td>
								<?php echo $this->type->display('data[discount][discount_type]',@$this->element->discount_type,true); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'DISCOUNT_FLAT_AMOUNT' ); ?>
							</td>
							<td>
								<input type="text" name="data[discount][discount_flat_amount]" value="<?php echo @$this->element->discount_flat_amount; ?>" /><?php echo $this->currency->display('data[discount][discount_currency_id]',@$this->element->discount_currency_id); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'DISCOUNT_PERCENT_AMOUNT' ); ?>
							</td>
							<td>
								<input type="text" name="data[discount][discount_percent_amount]" value="<?php echo @$this->element->discount_percent_amount; ?>" />
							</td>
						</tr>
						<tr id="hikashop_tax">
							<td class="key">
									<?php echo JText::_( 'TAXATION_CATEGORY' ); ?>
							</td>
							<td>
								<?php echo $this->categoryType->display('data[discount][discount_tax_id]',@$this->element->discount_tax_id);?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'DISCOUNT_USED_TIMES' ); ?>
							</td>
							<td>
								<input type="text" name="data[discount][discount_used_times]" value="<?php echo @$this->element->discount_used_times; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[discount][discount_published]" , '',@$this->element->discount_published	); ?>
							</td>
						</tr>
					</table>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
					<table class="admintable table" style="margin:auto">
						<tr>
							<td class="key">
									<?php echo JText::_( 'DISCOUNT_START_DATE' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('calendar', (@$this->element->discount_start?hikashop_getDate(@$this->element->discount_start,'%Y-%m-%d %H:%M'):''), 'data[discount][discount_start]','discount_start','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'DISCOUNT_END_DATE' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('calendar', (@$this->element->discount_end?hikashop_getDate(@$this->element->discount_end,'%Y-%m-%d %H:%M'):''), 'data[discount][discount_end]','discount_end','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
							</td>
						</tr>
<?php
	if(hikashop_level(1)){
		echo $this->loadTemplate('restrictions');
	} else {
?>
						<tr>
							<td class="key">
									<?php echo JText::_('RESTRICTIONS'); ?>
							</td>
							<td>
								<?php echo hikashop_getUpgradeLink('essential'); ?>
							</td>
						</tr>
<?php
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$html = array();
		$dispatcher->trigger('onDiscountBlocksDisplay', array(&$this->element, &$html));
		if(!empty($html)) {
			echo implode("\r\n", $html);
		}
	}
?>
					</table>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo @$this->element->discount_id; ?>" />
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="discount" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
