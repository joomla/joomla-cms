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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=characteristic" method="post"  name="adminForm" id="adminForm">
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	<div class="row-fluid">
		<div class="span4">
			<div class="input-prepend input-append">
				<span class="add-on"><i class="icon-filter"></i></span>
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
				<button class="btn" onclick="document.adminForm.limitstart.value=0;this.form.submit();"><i class="icon-search"></i></button>
				<button class="btn" onclick="document.adminForm.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="span8">
<?php } else { ?>
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_('FILTER'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
				<button class="btn" onclick="document.adminForm.limitstart.value=0;this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button class="btn" onclick="document.adminForm.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
<?php }
	if(!empty($this->extrafilters)) {
		foreach($this->extrafilters as $name => $filterObj) {
			echo $filterObj->displayFilter($name, $this->pageInfo->filter);
		}
	}
	if(HIKASHOP_BACK_RESPONSIVE) { ?>
		</div>
	</div>
<?php } else { ?>
			</td>
		</tr>
	</table>
<?php } ?>
	<table id="hikashop_characteristic_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.characteristic_value', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_ALIAS'), 'a.characteristic_alias', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
<?php
	$count_extrafields = 0;
	if(!empty($this->extrafields)) {
		foreach($this->extrafields as $namekey => $extrafield) {
			echo '<th class="hikashop_characteristic_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
		}
		$count_extrafields = count($this->extrafields);
	}
?>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.characteristic_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo 5 + $count_extrafields; ?>">
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
					<?php echo JHTML::_('grid.id', $i, $row->characteristic_id ); ?>
				</td>
				<td>
					<a href="<?php echo hikashop_completeLink('characteristic&task=edit&cid[]='.$row->characteristic_id); ?>">
						<?php echo $row->characteristic_value; ?>
					</a>
				</td>
				<td>
					<a href="<?php echo hikashop_completeLink('characteristic&task=edit&cid[]='.$row->characteristic_id); ?>">
						<?php echo $row->characteristic_alias; ?>
					</a>
				</td>
<?php
		if(!empty($this->extrafields)) {
			foreach($this->extrafields as $namekey => $extrafield) {
				$value = '';
				if(!empty($extrafield->value)) {
					$n = $extrafield->value;
					$value = $row->$n;
				} else if(!empty($extrafield->obj)) {
					$n = $extrafield->obj;
					$value = $n->showfield($this, $namekey, $row);
				}
				echo '<td class="hikashop_characteristic_'.$namekey.'_value">'.$value.'</td>';
			}
		}
?>
				<td width="1%" align="center">
					<?php echo $row->characteristic_id; ?>
				</td>
			</tr>
<?php
		$k = 1-$k;
	}
?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
