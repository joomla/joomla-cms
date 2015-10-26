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
$onclick='';
if($this->badge=='false'){ ?>
<fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_( 'PLEASE_SELECT_SOMETHING',true ); ?>');}else{submitbutton('add_coupon');}"><img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<?php }else{
	$onclick="window.parent.document.getElementById('discountselectparentlisting').name='none';";
} ?>
<div class="iframedoc" id="iframedoc"></div>
<table width="100%">
	<tr>
		<td style="vertical-align:top;">
			<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=discount" method="post"  name="adminForm" id="adminForm">
				<table>
					<tr>
						<td width="100%">
							<?php echo JText::_( 'FILTER' ); ?>:
							<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
							<button class="btn" onclick="document.adminForm.limitstart.value=0;this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
							<button class="btn" onclick="document.adminForm.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
						</td>
						<td nowrap="nowrap">
							<?php if($this->badge=='false'){
								echo $this->filter_type->display('filter_type',$this->pageInfo->filter->filter_type);
							} ?>
						</td>
					</tr>
				</table>
				<table class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'HIKA_NUM' );?>
							</th>
							<?php if($this->badge=='false'){ ?>
							<th class="title titlebox">
								<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
							</th>
							<?php } ?>
							<th class="title">
								<?php echo JHTML::_('grid.sort', JText::_('DISCOUNT_CODE'), 'a.discount_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'select_coupon'  ); ?>
							</th>
							<th class="title">
								<?php echo JText::_('DISCOUNT_VALUE'); ?>
							</th>
							<?php if(hikashop_level(1)){ ?>
								<th class="title">
									<?php echo JText::_('RESTRICTIONS'); ?>
								</th>
							<?php } ?>
							<th class="title">
								<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.discount_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value,'select_coupon' ); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="5">
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
								$publishedid = 'discount_published-'.$row->discount_id;
						?>
							<tr class="<?php echo "row$k"; ?>">
								<td align="center">
								<?php echo $this->pagination->getRowOffset($i); ?>
								</td>
								<?php if($this->badge=='false'){ ?>
								<td align="center">
									<?php echo JHTML::_('grid.id', $i, $row->discount_id ); ?>
								</td>
								<?php } ?>
								<td>
									<?php if($this->badge!='false'){
									 	echo '<span style="visibility:hidden">'.JHTML::_('grid.id', $i, $row->discount_id ).'</span>';
									} ?>
									<a href="#" onclick="<?php echo $onclick; ?>; document.getElementById('cb<?php echo $i; ?>').checked=true;submitbutton('add_coupon');return false;">
										<?php echo $row->discount_code; ?>
									</a>
								</td>
								<td align="center">
									<?php
										if(isset($row->discount_flat_amount) && $row->discount_flat_amount > 0){
											echo $this->currencyHelper->displayPrices(array($row),'discount_flat_amount','discount_currency_id');
										}
										elseif(isset($row->discount_percent_amount) && $row->discount_percent_amount > 0){
											echo $row->discount_percent_amount. '%';
										}
									?>
								</td>
								<?php if(hikashop_level(1)){ ?>
									<td>
										<?php
											$restrictions=array();
											if(!empty($row->discount_minimum_order)){
												$restrictions[]=JText::_('MINIMUM_ORDER_VALUE').':'.$this->currencyHelper->displayPrices(array($row),'discount_minimum_order','discount_currency_id');
											}
											if(!empty($row->product_name)){
												$restrictions[]=JText::_('PRODUCT').':'.$row->product_name;
											}
											if(!empty($row->category_name)){
												$restriction=JText::_('CATEGORY').':'.$row->category_name;
												if($row->discount_category_childs){
													$restriction.=' '.JText::_('INCLUDING_SUB_CATEGORIES');
												}
												$restrictions[]=$restriction;
											}
											if(!empty($row->zone_name_english)){
												$restrictions[]=JText::_('ZONE').':'.$row->zone_name_english;
											}
											if(!empty($row->username)){
												$restrictions[]=JText::_('HIKA_USER').':'.$row->username;
											}



											if ($row->discount_type == 'coupon') {
												if (!empty($row->discount_coupon_product_only)) {
													 $restrictions[]='Percentage for product only';
												}
												if(!empty($row->discount_coupon_nodoubling)){
													switch($row->discount_coupon_nodoubling) {
														case 1:
															$restrictions[]='Ignore discounted products';
															break;
														case 2:
															$restrictions[]='Override discounted products';
															break;
														default:
															break;
													}
												}
											}



											echo implode('<br/>',$restrictions);
										?>
									</td>
								<?php } ?>
								<td width="1%" align="center">
									<?php echo $row->discount_id; ?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>

				</table>
				<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
				<input type="hidden" name="task" value="<?php echo JRequest::getCmd('task'); ?>" />
				<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
				<input type="hidden" name="control" value="<?php echo JRequest::getCmd('control'); ?>" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="badge" value=<?php echo $this->badge; ?> />
				<input type="hidden" name="tmpl" value="component" />
				<input type="hidden" id="filter_id" name="filter_id" value="<?php echo @$this->pageInfo->filter->filter_id; ?>" />
				<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
		</td>
	</tr>
</table>
