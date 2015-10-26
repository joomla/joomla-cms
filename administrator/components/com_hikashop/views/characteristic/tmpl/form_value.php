<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="float:right">
	<?php
		echo $this->popup->display(
			'<img src="'.HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD'),
			'ADD',
			 hikashop_completeLink("characteristic&task=editpopup&characteristic_parent_id=".@$this->element->characteristic_id,true ),
			'value_add_button',
			860, 480, '', '', 'button'
		);
	?>
</div>
<br/>
<table id="hikashop_characteristic_values_listing" class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
	<thead>
		<tr>
			<th class="title titletoggle"><?php
				echo JText::_('HIKA_EDIT');
			?></th>
			<th class="title"><?php echo JText::_('VALUE');
			?></th>
<?php
	if(!empty($this->extrafields)) {
		foreach($this->extrafields as $namekey => $extrafield) {
			echo '<th class="hikashop_characteristic_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
		}
	}
?>
			<th class="title titletoggle"><?php
				echo JText::_('ORDERING');
			?></th>
			<th class="title titletoggle"><?php
				echo JText::_('HIKA_DELETE');
			?></th>
			<th class="title"><?php
				echo JText::_('ID');
			?></th>
		</tr>
	</thead>
	<tbody id="characteristic_listing">
<?php
	if(!empty($this->element->values)){
		$k = 0;
		for($i = 0,$a = count($this->element->values);$i<$a;$i++){
			$row =& $this->element->values[$i];
			$id=rand();
?>
		<tr id="characteristic_<?php echo $row->characteristic_id.'_'.$id;?>">
			<td><?php
				echo $this->popup->display(
					'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT').'"/>',
					'ADD',
					hikashop_completeLink("characteristic&task=editpopup&cid=".$row->characteristic_id.'&characteristic_parent_id='.$this->element->characteristic_id.'&id='.$id,true ),
					'value_'.$row->characteristic_id.'_edit_button',
					860, 480, '', '', 'link'
				);
			?></td>
			<td><?php
				echo $row->characteristic_value;
			?></td>
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
			<td class="order">
				<input type="text" size="3" name="characteristic_ordering[<?php echo $row->characteristic_id;?>]" id="characteristic_ordering[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]" value="<?php echo $row->characteristic_ordering;?>"/>
			</td>
			<td align="center">
				<a href="#" onclick="return deleteRow('characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]','characteristic_<?php echo $row->characteristic_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
			</td>
			<td width="1%" align="center">
				<?php echo $row->characteristic_id; ?>
				<div id="characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>">
					<input type="hidden" name="characteristic[<?php echo $row->characteristic_id;?>]" id="characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]" value="<?php echo $row->characteristic_id;?>"/>
				</div>
			</td>
		</tr>
<?php
			$k = 1-$k;
		}
	}
?>
	</tbody>
</table>
