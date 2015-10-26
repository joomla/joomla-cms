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
		<button class="btn" type="button" onclick="hikashop_setId(this);"><img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<script type="text/javascript">
function hikashop_setId(el) {
	if(document.adminForm.boxchecked.value==0){
		alert('<?php echo JText::_('PLEASE_SELECT_SOMETHING', true); ?>');
	}else{
		el.form.ctrl.value = '<?php echo $this->ctrl ?>';
		hikamarket.submitform("<?php echo $this->task; ?>",el.form);
	}
}
</script>
<?php } else { ?>
<script type="text/javascript">
function hikashop_setId(id) {
	var form = document.getElementById("adminForm");
	form.cid.value = id;
	form.ctrl.value = '<?php echo $this->ctrl ?>';
	hikashop.submitform("<?php echo $this->task; ?>",form);
}
</script>
<?php } ?>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=<?php echo JRequest::getCmd('ctrl'); ?>" method="post" name="adminForm" id="adminForm">
	<table class="hika_filter">
		<tr>
			<td width="100%">
				<?php echo JText::_('FILTER'); ?>:
				<input type="text" name="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="this.form.submit();" />
				<button class="btn" onclick="this.form.submit();"><?php echo JText::_('GO'); ?></button>
				<button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('RESET'); ?></button>
			</td>
		</tr>
	</table>
	<table class="adminlist hika_listing <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover':'hikam_table'; ?>" style="cell-spacing:1px">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
<?php if( !$this->singleSelection ) { ?>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(this);" />
				</th>
<?php } ?>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_LOGIN'), 'a.user_login', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ,'selection');
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.user_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value,'selection' );
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_EMAIL'), 'a.user_email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ,'selection');
				?></th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('ID'), 'a.user_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value,'selection' );
				?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$k = 0;
	foreach($this->rows as $i => $row) {

		$lbl1 = ''; $lbl2 = '';
		$extraTr = '';
		if( $this->singleSelection ) {
			if($this->confirm) {
				$data = '{id:'.$row->user_id;
				foreach($this->elemStruct as $s) {
					if($s == 'id')
						continue;
					$data .= ','.$s.':\''. str_replace(array('\'','"'),array('\\\'','\\\''),$row->$s).'\'';
				}
				$data .= '}';
				$extraTr = ' style="cursor:pointer" onclick="window.top.hikashop.submitBox('.$data.');"';
			} else {
				$extraTr = ' style="cursor:pointer" onclick="hikashop_setId(\''.$row->user_id.'\');"';
			}
		} else {
			$lbl1 = '<label for="cb'.$i.'">';
			$lbl2 = '</label>';
			$extraTr = ' onclick="hikashop.checkRow(\'cb'.$i.'\');"';
		}

		if(!empty($this->pageInfo->search)) {
			$row = hikashop_search($this->pageInfo->search, $row, 'user_id');
		}
?>
			<tr class="row<?php echo $k; ?>"<?php echo $extraTr; ?>>
				<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
<?php if( !$this->singleSelection ) { ?>
				<td align="center">
					<?php echo JHTML::_('grid.id', $i, $row->user_id ); ?>
				</td>
<?php } ?>
				<td>
					<?php echo $lbl1 . $row->username . $lbl2; ?>
				</td>
				<td>
					<?php echo $lbl1 . $row->name . $lbl2; ?>
				</td>
				<td>
					<?php echo $lbl1 . $row->user_email . $lbl2; ?>
				</td>
				<td width="1%" align="center">
					<?php echo $row->user_id; ?>
				</td>
			</tr>
<?php
		$k = 1-$k;
	}
?>
		</tbody>
	</table>
<?php if( $this->singleSelection ) { ?>
	<input type="hidden" name="cid" value="0" />
<?php } ?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="selection" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="after" value="<?php echo JRequest::getVar('after', ''); ?>" />
	<input type="hidden" name="afterParams" value="<?php echo JRequest::getVar('afterParams', ''); ?>" />
	<input type="hidden" name="confirm" value="<?php echo $this->confirm ? '1' : '0'; ?>" />
	<input type="hidden" name="single" value="<?php echo $this->singleSelection ? '1' : '0'; ?>" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
<?php
	if(!empty($this->afterParams)) {
		foreach($this->afterParams as $p) {
			if(empty($p[0]) || !isset($p[1]))
				continue;
			echo '<input type="hidden" name="'.$this->escape($p[0]).'" value="'.$this->escape($p[1]).'"/>' . "\r\n";
		}
	}
?>
	<?php echo JHTML::_('form.token'); ?>
</form>
<script type="text/javascript">
document.adminForm = document.getElementById("hikashop_form");
</script>
