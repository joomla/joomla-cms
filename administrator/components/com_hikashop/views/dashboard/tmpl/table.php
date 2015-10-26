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
	$id=$this->widget->widget_id;
	$row_id=0;
	$popupHelper = hikashop_get('helper.popup');
?>
<div id="table_<?php echo $id; ?>" style="margin:auto; margin-b" align="center">
	<?php

	echo '<table class="widget_table" style="margin-bottom:10px;">';
	foreach($this->widget->widget_params->table as $key => $row){
		$name = str_replace(' ','_',strtoupper($row->row_name));?>
			<tr>
				<td class="key">
					<?php
					if(JText::_($name)==$name) echo $row->row_name;
					else echo JText::_($name); ?>
					<br/>
				</td>
				<td class="data">
					<?php
					if(empty($row->elements)){
						echo JText::_('NO_DATA');
					}
					if(is_numeric($row->elements)){	echo round($row->elements,2); }
					else{ echo $row->elements; }

					if(isset($this->edit)){ ?>
						<td style="float:right; padding:10px; width:50px;">
							<a onclick="document.getElementById('delete_row').value = '<?php echo $key; ?>';submitbutton('apply_table');" href="#">
								<img src="<?php echo HIKASHOP_IMAGES.'delete.png'; ?>" alt="delete"/>
							</a>
						<?php
							echo $popupHelper->display(
								'<img src="'.HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT').'" />',
								'HIKA_EDIT',
								hikashop_completeLink('report&task=tableform&widget_id='.$id.'&row_id='.$key,true, true ),
								'hikashop_edit_popup_'.$key,
								900, 480, '', '', 'link'
							);
						?>
						</td>
					<?php } ?>
				</td>
			</tr>
	<?php }
	echo '</table>';
	foreach($this->widget->widget_params->table as $key => $row){
		$row_id=$key+1;
	}

	if(isset($this->edit)){
		echo $popupHelper->display(
			'<img src="'.HIKASHOP_IMAGES.'add.png" alt="'.JText::_('ADD').'" />'.JText::_('ADD'),
			'ADD',
			hikashop_completeLink('report&task=tableform&widget_id='.$id.'&row_id='.$row_id,true, true ),
			'hikashop_edit_popup',
			900, 480, '', '', 'button'
		);
	 }
	 ?>
</div>
<br/>
