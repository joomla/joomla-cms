<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if( !$this->singleSelection ) { ?>
<fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('PLEASE_SELECT_SOMETHING', true); ?>');}else{submitbutton('useselection');}"><img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<?php } ?>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=<?php echo JRequest::getCmd('ctrl'); ?>" method="post" name="adminForm" id="adminForm">
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	<div class="row-fluid">
		<div class="span12">
			<div class="input-prepend input-append">
				<span class="add-on"><i class="icon-filter"></i></span>
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" onchange="this.form.submit();" />
				<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="icon-search"></i></button>
				<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
	</div>
<?php } else { ?>
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
				<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
		</tr>
	</table>
<?php } ?>
	<table class="adminlist table table-striped table-hover" style="cell-spacing:1px">
		<thead>
			<tr>
				<th class="title titlenum"><?php
				echo JText::_( 'HIKA_NUM' );
				?></th>
<?php if( !$this->singleSelection ) { ?>
				<th class="title titlebox"><input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" /></th>
<?php } ?>
				<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('DISCOUNT_CODE'), 'a.discount_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('DISCOUNT_TYPE'), 'a.discount_type', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('DISCOUNT_START_DATE'), 'a.discount_start', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('DISCOUNT_END_DATE'), 'a.discount_end', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
				echo JText::_('DISCOUNT_VALUE');
				?></th>
<?php if(hikashop_level(1)){ ?>
				<th class="title"><?php
				echo JText::_('DISCOUNT_QUOTA');
				?></th>
				<th class="title"><?php
				echo JText::_('RESTRICTIONS');
				?></th>
<?php } ?>
				<th class="title titletoggle"><?php
				echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.discount_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
				<th class="title"><?php
				echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.user_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="11"><?php
					echo $this->pagination->getListFooter();
					echo $this->pagination->getResultsCounter();
				?></td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$k = 0;
	for($i = 0, $a = count($this->rows); $i < $a; $i++) {
		$row =& $this->rows[$i];

		$lbl1 = ''; $lbl2 = '';
		$extraTr = '';
		if( $this->singleSelection ) {
			$data = '{id:'.$row->discount_id;
			foreach($this->elemStruct as $s) {
				if($s == 'id')
					continue;
				$data .= ','.$s.':\''. str_replace(array('\'','"'),array('\\\'','\\"'),$row->$s).'\'';
			}
			$data .= '}';
			$extraTr = ' style="cursor:pointer" onclick="window.top.hikashop.submitBox('.$data.');"';
		} else {
			$lbl1 = '<label for="cb'.$i.'">';
			$lbl2 = '</label>';
			$extraTr = ' onclick="window.hikashop.checkRow(\'cb'.$i.'\');"';
		}
?>
			<tr class="row<?php echo $k; ?>"<?php echo $extraTr; ?>>
				<td align="center"><?php
					echo $this->pagination->getRowOffset($i);
				?></td>
<?php if( !$this->singleSelection ) { ?>
				<td align="center">
					<input type="checkbox" onclick="this.clicked=true; this.checked=!this.checked" value="<?php echo $row->vendor_id;?>" name="cid[]" id="cb<?php echo $i;?>"/>
				</td>
<?php } ?>
				<td><?php
					echo $lbl1 . $row->discount_code . $lbl2;
				?></td>
				<td><?php
					echo $lbl1 . $row->discount_type . $lbl2;
				?></td>
				<td align="center"><?php
					echo $lbl1 . hikashop_getDate($row->discount_start) . $lbl2;
				?></td>
				<td align="center"><?php
					echo $lbl1 . hikashop_getDate($row->discount_end) . $lbl2;
				?></td>
				<td align="center"><?php
					echo $lbl1;
					if(isset($row->discount_flat_amount) && $row->discount_flat_amount > 0){
						echo $this->currencyHelper->displayPrices(array($row),'discount_flat_amount','discount_currency_id');
					} elseif(isset($row->discount_percent_amount) && $row->discount_percent_amount > 0){
						echo $row->discount_percent_amount. '%';
					}
					echo $lbl2;
				?></td>
<?php if(hikashop_level(1)){ ?>
				<td align="center"><?php
					echo $lbl1;
					if(empty($row->discount_quota)){
						echo JText::_('UNLIMITED');
					}else{
						echo $row->discount_quota. ' ('.JText::sprintf('X_LEFT',$row->discount_quota-$row->discount_used_times).')';
					}
					echo $lbl2;
				?></td>
				<td><?php
					$restrictions=array();
					if(!empty($row->discount_minimum_order) && (float)$row->discount_minimum_order != 0){
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



					echo $lbl1 . implode('<br/>',$restrictions) . $lbl2;
				?></td>
<?php } ?>
				<td align="center"><?php
					echo $lbl1 . $this->toggleClass->display('activate',$row->discount_published) . $lbl2;
				?></td>
				<td width="1%" align="center"><?php
					echo $row->discount_id;
				?></td>
			</tr>
<?php
		$k = 1-$k;
	}
?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="selection" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="single" value="<?php echo $this->singleSelection ? '1' : '0'; ?>" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
