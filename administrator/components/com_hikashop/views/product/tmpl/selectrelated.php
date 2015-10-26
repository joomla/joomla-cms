<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_( 'PLEASE_SELECT_SOMETHING',true ); ?>');}else{submitbutton('addrelated');}"><img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<?php if($this->config->get('category_explorer')){?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-product">
	<table style="width:100%">
		<tr>
			<td style="vertical-align:top;border:1px solid #CCC;background-color: #F3F3F3" width="200px">
				<?php echo hikashop_setExplorer('product&task=selectrelated&select_type='.$this->type.'&control='.$this->control,$this->pageInfo->filter->filter_id,true,'product'); ?>
			</td>
			<td style="vertical-align:top;">
<?php } else { ?>
<div id="page-product" class="row-fluid">
	<div class="span4">
		<?php echo hikashop_setExplorer('product&task=selectrelated&select_type='.$this->type.'&control='.$this->control,$this->pageInfo->filter->filter_id,true,'product'); ?>
	</div>
	<div class="span8">
<?php } ?>
<?php } ?>
			<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=product" method="post"  name="adminForm" id="adminForm">
				<table>
					<tr>
						<td width="100%">
							<a href="<?php echo hikashop_completeLink('product&task=selectrelated&filter_id=0&select_type='.$this->type.'&control='.$this->control,true); ?>"><?php echo JText::_( 'ROOT' ); ?>/</a>
							<?php echo $this->breadCrumb.' '.JText::_( 'FILTER' ); ?>:
							<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
							<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
							<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
						</td>
						<td nowrap="nowrap">
							<?php echo $this->childDisplay; ?>
						</td>
					</tr>
				</table>
				<table class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'HIKA_NUM' );?>
							</th>
							<th class="title titlebox">
								<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'b.product_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectrelated' ); ?>
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort', JText::_('PRODUCT_CODE'), 'b.product_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectrelated' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_('PRODUCT_PRICE'); ?>
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'b.product_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value,'selectrelated' ); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="9">
								<?php echo $this->pagination->getListFooter(); ?>
								<?php echo $this->pagination->getResultsCounter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<?php
							$k = 0;
							for($i = 0,$a = count($this->rows);$i<$a;$i++){
								$row =& $this->rows[$i];
						?>
							<tr class="<?php echo "row$k"; ?>">
								<td align="center">
								<?php echo $this->pagination->getRowOffset($i); ?>
								</td>
								<td align="center">
									<?php echo JHTML::_('grid.id', $i, $row->product_id ); ?>
								</td>
								<td align="center">
									<?php if(in_array($this->type,array('discount','import'))){ ?>
										<a href="<?php echo hikashop_completeLink('product&task=addrelated&cid='.$row->product_id.'&select_type='.$this->type.'&control='.$this->control,true); ?>">
									<?php }?>
										<?php echo $row->product_name; ?>
									<?php if(in_array($this->type,array('discount','import'))){ ?>
										</a>
									<?php }?>
								</td>
								<td>
									<?php echo $row->product_code; ?>
								</td>
								<td>
									<?php echo $this->currencyHelper->displayPrices(@$row->prices); ?>
								</td>
								<td width="1%" align="center">
									<?php echo $row->product_id; ?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>
				</table>
				<input type="hidden" name="control" value="<?php echo $this->control; ?>" />
				<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
				<input type="hidden" name="tmpl" value="component" />
				<input type="hidden" name="task" value="selectrelated" />
				<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
				<input type="hidden" name="select_type" value="<?php echo $this->type;?>" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" id="filter_id" name="filter_id" value="<?php echo $this->pageInfo->filter->filter_id; ?>" />
				<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
<?php if($this->config->get('category_explorer')){?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
<?php } ?>
