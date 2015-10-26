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
$id_td = '';
if(isset($this->ids)){
	$id_td = $this->tab_id.'_'.$this->data_id.'_'.$this->table.'_'.$this->column;
	echo '<div class="massaction-edition">
			<a onclick="var target = document.getElementById(\''.$id_td.'\'); if(target.rel == \'edit\'){onEditSquare(\'save\',target,\''.$this->table.'_'.$this->column.'\',\''.$this->data_id.'\',\''.$this->table.'\',\''.$this->tab_id.'\');}">
				<img src="../media/com_hikashop/images/ok.png" alt>
				<span>Save</span>
			</a>
			<a onclick="var target = document.getElementById(\''.$id_td.'\'); if(target.rel == \'edit\'){onEditSquare(\'cancel\',target,\''.$this->table.'_'.$this->column.'\',\''.$this->data_id.'\',\''.$this->table.'\',\''.$this->tab_id.'\');}">
				<img src="../media/com_hikashop/images/cancel.png" alt>
				<span>Cancel</span>
			</a>
		</div>';
}else if(!isset($this->ids)){
	$id_td = $this->tab_id.'_'.$this->table.'_'.$this->column;
	echo '<div>
			<a onclick="var target = document.getElementById(\''.$id_td.'\'); if(target.rel == \'edit\'){onEditColumn(\'save\',target,\''.$this->table.'\',\''.$this->column.'\',\''.$this->tab_id.'\');}">
				<img src="../media/com_hikashop/images/ok.png" alt>
				<span>Save</span>
			</a>
			<a ="var target = document.getElementById(\''.$id_td.'\'); if(target.rel == \'edit\'){onEditColumn(\'cancel\',target,\''.$this->table.'\',\''.$this->column.'\',\''.$this->tab_id.'\');}">
				<img src="../media/com_hikashop/images/cancel.png" alt>
				<span>Cancel</span>
			</a>
		</div>';
}

switch($this->type){
	case 'text':
		if(isset($this->ids)){
			$column = $this->column;
			$column_id = $this->table.'_id';
			foreach($this->rows as $row){
				$name = 'hikashop[values]['.$row->$column_id.']';
				$params = array('name'=>$name,'value'=>$row->$column);
				echo $this->displayField('textarea',$params);
			}

		}else if(!isset($this->ids)){
			$name = 'hikashop[values][column]';
			$params = array('name'=>$name,'value'=>'');
			echo $this->displayField('textarea',$params);
		}

		break;

	case 'custom_text' :
	case 'custom_singledropdown':
	case 'custom_textarea':
	case 'custom_radio':
	case 'custom_checkbox':
	case 'custom_multipledropdown':
	case 'custom_file':
	case 'custom_image':
		$column = $this->column;
		$column_id = $this->table.'_id';
		$cpt = 0;
		if(isset($this->ids)){
			foreach($this->rows as $row){
				$cpt++;
				foreach($this->elements as $element){
					if($element->$column_id == $row->$column_id){
						foreach($this->allFields as $allfields){
							if($allfields['id'] == $element->$column_id){
								$name = 'hikashop[values]['.$row->$column_id.']';
								$params = array(&$allfields[$column],$row->$column,$name);
								$this->fields->prefix = $cpt;
								$this->fields->suffix = $id_td;
								$params = array('class'=>$this->fields,'params'=>$params);
								echo $this->displayField('custom',$params);
							}
						}
					}
				}
			}
		}else if(!isset($this->ids)){
		}
		break;
	case 'custom_zone':
		$column = $this->column;
		$column_id = $this->table.'_id';
		$cpt = 0;
		if(isset($this->ids)){
			foreach($this->rows as $row){
				$cpt++;
				foreach($this->elements as $element){
					if($element->$column_id == $row->$column_id){
						foreach($this->allFields as $allfields){
							if($allfields['id'] == $element->$column_id){
								$name = 'hikashop[values]['.$row->$column_id.']';
								$params = array(&$allfields[$column],$row->$column,$name,false,'',false,$allfields,$element);
								$this->fields->prefix = $cpt;
								$this->fields->suffix = $id_td;
								$params = array('class'=>$this->fields,'params'=>$params);
								echo str_replace(array('field_namekey=address_state','field_type=address'),array('field_namekey='.$row->$column_id,'field_type=values'),$this->displayField('custom',$params));
							}
						}
					}
				}
			}
		}else if(!isset($this->ids)){
		}
		break;


	case 'joomla_users':
		if(isset($this->ids)){
			$column = $this->column;
			$column_id = $this->table.'_id';
			foreach($this->rows as $row){
				$name = 'hikashop[values]['.$row->$column_id.']';
				$params = array('name'=>$name,'value'=>$row->$column);
				echo $this->displayField('textarea',$params);
			}
		}else if(!isset($this->ids)){
			$name = 'hikashop[values][column]';
			$params = array('name'=>$name,'value'=>'');
			echo $this->displayField('textarea',$params);
		}
		break;

	case 'weight':
	case 'dimension':
	case 'price':
		if(isset($this->ids)){
			$column = $this->column;
			$column_id = $this->table.'_id';
			foreach($this->rows as $row){
				$name = 'hikashop[values]['.$row->$column_id.']';
				$params = array('name'=>$name,'value'=>$row->$column);
				echo $this->displayField('input',$params);
			}
		}else if(!isset($this->ids)){
			$name = 'hikashop[values][column]';
			$params = array('name'=>$name,'value'=>'');
			echo $this->displayField('input',$params);
		}
		break;

	case 'dimension_unit':
		if(isset($this->ids)){
			$column = $this->column;
			$column_id = $this->table.'_id';
			$cpt = 0;
			foreach($this->rows as $row){
				$cpt++;
				$name = 'hikashop[values]['.$row->$column_id.']';
				$params = array($name,$row->product_dimension_unit,'dimension',$cpt.$id_td);
				$params = array('class'=>$this->volume,'params'=>$params);
				echo $this->displayField('custom',$params);
			}
		}else if(!isset($this->ids)){
			$name = 'hikashop[values][column]';
			$params = array($name,0,'dimension',$id_td);
			$params = array('class'=>$this->volume,'params'=>$params);
			echo $this->displayField('custom',$params);
		}
		break;

	case 'weight_unit':
		if(isset($this->ids)){
			$column = $this->column;
			$column_id = $this->table.'_id';
			$cpt = 0;
			foreach($this->rows as $row){
				$cpt++;
				$name = 'hikashop[values]['.$row->$column_id.']';
				$params = array($name,$row->product_weight_unit,$cpt.$id_td);
				$params = array('class'=>$this->weight,'params'=>$params);
				echo $this->displayField('custom',$params);
			}
		}else if(!isset($this->ids)){
			$name = 'hikashop[values][column]';
			$params = array($name,0,$id_td);
			$params = array('class'=>$this->weight,'params'=>$params);
			echo $this->displayField('custom',$params);
		}
		break;

	case 'currency':
		$column = $this->column;
		$column_id = $this->table.'_id';
		$cpt = 0;
		if(isset($this->ids)){
			foreach($this->rows as $row){
				$cpt++;
				$name = 'hikashop[values]['.$row->$column_id.']';
				$params = array($name,$row->$column,'size="1"',$cpt.$id_td);
				$params = array('class'=>$this->types,'params'=>$params);
				echo $this->displayField('custom',$params);
			}
		}else if(!isset($this->ids)){
			$name = 'hikashop[values][column]';
			$params = array($name,0,'size="1"',$id_td);
			$params = array('class'=>$this->types,'params'=>$params);
			echo $this->displayField('custom',$params);
		}
		break;

	case 'layout':
		if(isset($this->ids)){
			$column = $this->column;
			$column_id = $this->table.'_id';
			$cpt=0;
			foreach($this->rows as $row){
				$cpt++;
				$js = '';
				if(empty($row->$column)){
					$row->$column = 'inherit';
				}
				$name = 'hikashop[values]['.$row->$column_id.']';
				$params = array($name,$row->$column,&$js,false,$cpt.$id_td);
				$params = array('class'=>$this->layout,'params'=>$params);
				echo $this->displayField('custom',$params);
			}
		}else if(!isset($this->ids)){
		}
		break;

	case 'status':
		if(isset($this->ids)){
			$column = $this->column;
			$column_id = $this->table.'_id';
			$cpt=0;
			foreach($this->rows as $row){
				$cpt++;
				$name = 'hikashop[values]['.$row->$column_id.']';
				$params = array($name,$row->$column,true,true,$cpt.$id_td);
				$params = array('class'=>$this->status,'params'=>$params);
				echo $this->displayField('custom',$params);
				echo '<INPUT type="checkbox" name="checkbox" value="notification">'.JText::_( 'NOTIFICATION' );
				echo '<br/>';
			}
		}else if(!isset($this->ids)){
			$name = 'hikashop[values][column]';
			$params = array($name,0,true,true,$id_td);
			$params = array('class'=>$this->status,'params'=>$params);
			echo $this->displayField('custom',$params);
		}
		break;

	case 'yesno':
		if(isset($this->ids)){
			$column = $this->column;
			$column_id = $this->table.'_id';
			$cpt=0;
			foreach($this->rows as $row){
				$cpt++;
				echo JHTML::_('hikaselect.booleanlist', 'hikashop[values]['.$row->$column_id.']', '',@$row->$column);
				if(HIKASHOP_J30){
					echo '<script type="text/javascript">
(function($){
	$(".radio.btn-group label").addClass("btn");
	$(".btn-group label:not(.active)").click(function() {
			var label = $(this);
			var input = $("#" + label.attr(\'for\'));

			if (!input.prop(\'checked\')) {
				label.closest(\'.btn-group\').find("label").removeClass(\'active btn-success btn-danger btn-primary\');
				if (input.val() == "") {
					label.addClass("active btn-primary");
				} else if (input.val() == 0) {
					label.addClass("active btn-danger");
				} else {
					label.addClass("active btn-success");
				}
				input.prop("checked", true);
			}
		});
		$(".btn-group input[checked=checked]").each(function() {
			if ($(this).val() == "") {
				$("label[for=" + $(this).attr(\'id\') + "]").addClass(\'active btn-primary\');
			} else if ($(this).val() == 0) {
				$("label[for=" + $(this).attr(\'id\') + "]").addClass(\'active btn-danger\');
			} else {
				$("label[for=" + $(this).attr(\'id\') + "]").addClass(\'active btn-success\');
			}
		});
})(jQuery);
</script>';
				}
				echo '<br/>';
			}
		}else if(!isset($this->ids)){
		}

		break;

	case 'date':
		if(isset($this->ids)){
			$column = $this->column;
			$column_id = $this->table.'_id';

			foreach($this->rows as $row){
				$name = 'hikashop[values]['.$row->$column_id.']';
				$params = array('name'=>$name,'id'=>$row->$column_id,'value'=>$row->$column);
				echo $this->displayField('date',$params);
			}
		}else if(!isset($this->ids)){
			$name = 'hikashop[values][column]';
			$params = array('name'=>$name,'id'=>0,'value'=>'');
			echo $this->displayField('date',$params);
		}

		break;

	case 'parent':
		if(isset($this->ids)){
			$column_id = $this->table.'_id';
			$column_name = $this->table.'_name';

			foreach($this->ids as $id){
				$name = 'hikashop[values]['.$id.']';
				$params = array('name'=>$name,'type'=>$this->type,'rows'=>$this->rows,'id'=>$id,'column_id'=>$column_id,'columns'=>array($column_id,$column_name));
				echo $this->displayField('dropdawn',$params);
			}
		}else if(!isset($this->ids)){
		}

		break;

	case 'usergroups':
		if(isset($this->ids)){
			$column_id = $this->table.'_id';
			$column_name = 'title';

			foreach($this->ids as $id){
				$name = 'hikashop[values]['.$id.']';
				$params = array('name'=>$name,'type'=>$this->type,'rows'=>$this->rows,'id'=>$id,'column_id'=>$column_id,'columns'=>array($column_id,$column_name));
				echo $this->displayField('dropdawn',$params);
			}
		}else if(!isset($this->ids)){
		}
		break;

	case 'related':
		if(isset($this->ids)){
			$column_id = 'related_id';
			$column_name = 'product_name';

			foreach($this->ids as $id){
				$name = 'hikashop[values]['.$id.']';
				$params = array('name'=>$name,'type'=>$this->type,'rows'=>$this->rows,'id'=>$id,'column_id'=>$column_id,'columns'=>array($column_id,$column_name));
				echo $this->displayField('dropdawn',$params);
			}
		}else if(!isset($this->ids)){
		}
		break;

	case 'options':
		if(isset($this->ids)){
			$column_id = 'options_id';
			$column_name = 'product_name';

			foreach($this->ids as $id){
				$name = 'hikashop[values]['.$id.']';
				$params = array('name'=>$name,'type'=>$this->type,'rows'=>$this->rows,'id'=>$id,'column_id'=>$column_id,'columns'=>array($column_id,$column_name));
				echo $this->displayField('dropdawn',$params);
			}
		}else if(!isset($this->ids)){
		}
		break;

	case 'method_name':
		if(isset($this->ids)){
			$column_id = $this->table.'_id';
			$column_name = $this->table.'_name';
			$column_type = $this->table.'_type';

			foreach($this->ids as $id){
				$name = 'hikashop[values]['.$id.']';
				$params = array('name'=>$name,'type'=>$this->type,'rows'=>$this->rows,'id'=>$id,'column_id'=>$column_id,'columns'=>array($column_id,$column_name,$column_type));
				echo $this->displayField('dropdawn',$params);
			}
		}else if(!isset($this->ids)){
		}
		break;

	case 'characteristic':
		if(isset($this->ids)){
			$column = 'characteristic_value';
			foreach($this->ids as $id){
				foreach($this->rows as $row){
					if($id == $row->characteristic_id){
						$name = 'hikashop[values]['.$id.']';
						$params = array('type'=>$this->type,'rows'=>$this->rows,'id'=>$id,'column_id'=>'characteristic_id','columns'=>array('characteristic_id',$column),'name'=>$name);
						echo $this->displayField('dropdawn',$params);
					}
				}
			}
		}else if(!isset($this->ids)){
		}
		break;

	case 'sub_id':
	case 'id':
		if(isset($this->ids)){
			$column = $this->column;
			$column_name = $this->table.'_name';
			foreach($this->ids as $id){
				$name = 'hikashop[values]['.$id.']';
				$params = array('type'=>$this->type,'rows'=>$this->rows,'id'=>$id,'name'=>$name,'column_id'=>$column,'columns'=>array($column,$column_name));
				echo $this->displayField('dropdawn',$params);
			}
		}else if(!isset($this->ids)){
		}
		break;

	default:
		if(!isset($this->dispatcher)){
			JPluginHelper::importPlugin('hikashop');
			$this->dispatcher = JDispatcher::getInstance();
		}
		$this->dispatcher->trigger('onDisplayEditionSquaredMassAction'.$this->type,array(&$this));
		break;
}
?>

<?php echo JHTML::_( 'form.token' );
