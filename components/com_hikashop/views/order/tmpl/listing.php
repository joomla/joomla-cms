<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_order_listing">
<fieldset>
	<div class="header hikashop_header_title"><h1><?php echo JText::_('ORDERS');?></h1></div>
	<div class="toolbar hikashop_header_buttons" id="toolbar" style="float: right;">
		<table class="hikashop_no_border">
			<tr>
				<td>
					<a onclick="javascript:submitbutton('cancel'); return false;" href="#" >
						<span class="icon-32-back" title="<?php echo JText::_('HIKA_BACK'); ?>">
						</span>
						<?php echo JText::_('HIKA_BACK'); ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order'); ?>" method="post"  name="adminForm" id="adminForm">
	<table class="hikashop_no_border">
		<tr>
			<td width="100%">
				<?php echo JText::_( 'FILTER' ); ?>:
				<input type="text" name="search" id="hikashop_search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="inputbox" onchange="document.adminForm.submit();" />
				<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button class="btn" onclick="document.getElementById('hikashop_search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
		</tr>
	</table>
	<?php global $Itemid; ?>
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
	<table id="hikashop_order_listing" class="hikashop_orders adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="hikashop_order_num_title title titlenum" align="center">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="hikashop_order_number_title title" align="center">
					<?php echo JText::_('ORDER_NUMBER'); ?>
				</th>
				<th class="hikashop_order_date_title title" align="center">
					<?php echo JHTML::_('grid.sort', JText::_('DATE'), 'a.order_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_status_title title" align="center">
					<?php echo JHTML::_('grid.sort',   JText::_('ORDER_STATUS'), 'a.order_status', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_total_title title" align="center">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKASHOP_TOTAL'), 'a.order_full_price', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<div class="pagination">
						<form action="<?php echo hikashop_completeLink('order'); ?>" method="post" name="adminForm_bottom">
							<?php $this->pagination->form = '_bottom'; echo $this->pagination->getListFooter(); ?>
							<?php echo $this->pagination->getResultsCounter(); ?>
							<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
							<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
							<input type="hidden" name="task" value="" />
							<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
							<input type="hidden" name="boxchecked" value="0" />
							<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
							<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
							<?php echo JHTML::_( 'form.token' ); ?>
						</form>
					</div>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				global $Itemid;
				$url_itemid = '';
				if(!empty($Itemid)){
					$url_itemid='&Itemid='.$Itemid;
				}

				$orderUrl = hikashop_completeLink('order'.$url_itemid);
				$config =& hikashop_config();
				if($config->get('force_ssl',0) && strpos('https://',$orderUrl) === false) {
					$orderUrl = str_replace('http://','https://',HIKASHOP_LIVE) . 'index.php?option=com_hikashop&ctrl=order';
				}


				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="hikashop_order_num_value">
					<?php echo $this->pagination->getRowOffset($i);
					?>
					</td>
					<td class="hikashop_order_number_value">
						<a href="<?php echo hikashop_completeLink('order&task=show&cid='.$row->order_id.$url_itemid); ?>">
							<?php echo $row->order_number; ?>
						</a>
						<?php if($row->order_payment_method == 'paypalrecurring'){ ?>
						<span style="float:right; padding-right: 10px;"><?php echo JText::_('RECURRING_ORDER'); ?></span>
						<?php } ?>
					</td>
					<td class="hikashop_order_date_value">
						<?php echo hikashop_getDate($row->order_created,'%Y-%m-%d %H:%M');?>
					</td>
					<td class="hikashop_order_status_value">
						<span class="hikashop_order_listing_status hikashop_order_status_<?php echo str_replace(' ', '_', $this->escape($row->order_status)); ?>">
						<?php
							echo $this->order_statuses->trans($row->order_status); ?></span>
						<?php if(!empty($row->show_payment_button) && bccomp($row->order_full_price,0,5)>0){ ?>
							<form action="<?php echo $orderUrl; ?>" method="post" name="adminForm_<?php echo $row->order_id; ?>_pay">
								<?php
								if($this->payment_change){
									$text = JText::_('PAY_NOW');
									$this->payment->order = $row;
									$this->payment->preload(false);
									echo $this->payment->display('new_payment_method',$row->order_payment_method,$row->order_payment_id,false);
								}else{
									$text = JText::sprintf('PAY_WITH_X',$this->payment->getName($row->order_payment_method,$row->order_payment_id));
								}
								$url = hikashop_completeLink('order&task=pay&order_id='.$row->order_id.$url_itemid);
								if($config->get('force_ssl',0) && strpos('https://',$url) === false) {
									$url = str_replace('http://','https://',HIKASHOP_LIVE) . 'index.php?option=com_hikashop&ctrl=order&task=pay&order_id='.$row->order_id;
								}
								echo $this->cart->displayButton($text,'pay',$this->params,$url,'document.adminForm_'.$row->order_id.'_pay.submit();return false;','class="hikashop_order_pay_button"');

								?>
								<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
								<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
								<input type="hidden" name="task" value="pay" />
								<input type="hidden" name="order_id" value="<?php echo $row->order_id; ?>" />
								<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
								<?php echo JHTML::_( 'form.token' ); ?>
							</form>
						<?php }
						if( isset($row->show_cancel_button) && $row->show_cancel_button ) {?>
							<form action="<?php echo hikashop_completeLink('order'.$url_itemid); ?>" method="post" name="adminForm_<?php echo $row->order_id; ?>_cancel">
								<?php
								$text = JText::_('CANCEL_ORDER');
								echo $this->cart->displayButton($text,'cancel_order',$this->params,hikashop_completeLink('order&task=cancel_order&email=1&order_id='.$row->order_id.$url_itemid),'document.adminForm_'.$row->order_id.'_cancel.submit();return false;','class="hikashop_order_cancel_button"'); ?>
								<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
								<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
								<input type="hidden" name="task" value="cancel_order" />
								<input type="hidden" name="email" value="1" />
								<input type="hidden" name="order_id" value="<?php echo $row->order_id; ?>" />
								<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
								<input type="hidden" name="redirect_url" value="<?php echo hikashop_currentURL(); ?>" />
								<?php echo JHTML::_( 'form.token' ); ?>
							</form>
						<?php }

							if($this->config->get('allow_reorder',0)){ ?>
							<form action="<?php echo $orderUrl; ?>" method="post" name="adminForm_<?php echo $row->order_id; ?>_reorder">
								<?php
								$url = hikashop_completeLink('order&task=reorder&order_id='.$row->order_id.$url_itemid);
								if($config->get('force_ssl',0) && strpos('https://',$url) === false) {
									$url = str_replace('http://','https://',HIKASHOP_LIVE) . 'index.php?option=com_hikashop&ctrl=order&task=reorder&order_id='.$row->order_id;
								}
								echo $this->cart->displayButton(JText::_('REORDER'),'reorder',$this->params,$url,'document.adminForm_'.$row->order_id.'_reorder.submit();return false;','class="hikashop_order_reorder_button"');

								?>
								<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
								<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
								<input type="hidden" name="task" value="reorder" />
								<input type="hidden" name="order_id" value="<?php echo $row->order_id; ?>" />
								<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
								<?php echo JHTML::_( 'form.token' ); ?>
							</form>
						<?php } ?>
					</td>
					<td class="hikashop_order_total_value">
						<?php echo $this->currencyHelper->format($row->order_full_price,$row->order_currency_id);?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</div>
<div class="clear_both"></div>
