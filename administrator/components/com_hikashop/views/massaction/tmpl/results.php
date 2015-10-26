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
	$edit_url = 'massaction&task=editcell';
	$save_url = 'massaction&task=savecell';
	$cancel_url = 'massaction&task=cancel_edit';

	$columnsData = array();
	$linesData = array();

	$massaction = hikashop_get('class.massaction');

	foreach($this->params->action as $keyTable => $table){
		foreach($table as $action){
			$url = '"'.$keyTable.'_'.$action.'":"';
			$url .= urlencode('hikashop[data]').'='.urlencode($this->params->table);
			$url .= '&'.urlencode('hikashop[column]').'='.urlencode($action);
			$url .= '&'.urlencode('hikashop[table]').'='.urlencode($keyTable);
			foreach($this->params->types[$action] as $keyObject=>$object){
				$url .= '&'.urlencode('hikashop['.$keyObject.']').'='.urlencode($object);
			}
			$url .= '"';
			$columnsData[]= $url;
		}
	}

	foreach($this->params->elements as $element){
		$table_id = $this->params->table.'_id';
		$line = $element->$table_id.':{';
		$association = array();
		foreach($this->params->action as $keyTable => $table){
			$table_id = $keyTable.'_id';
			if(isset($element->$table_id)){
				if(!isset($association[$keyTable]))
					$association[$keyTable] = $element->$table_id;
				else {
					if(!is_array($association[$keyTable]))
						$association[$keyTable] = array($association[$keyTable]);
					$association[$keyTable][] = $element->$table_id;
				}
			}else{
				foreach($element as $elem){
					if(empty($elem) || !is_array($elem))
						continue;
					foreach($elem as $data){
						if(isset($data->$table_id)){
							if(!isset($association[$keyTable])){
								$association[$keyTable] = $data->$table_id;
							} else {
								if(!is_array($association[$keyTable])){
									$association[$keyTable] = array($association[$keyTable]);
								}
								$association[$keyTable][] = $data->$table_id;
							}
						}
					}
				}
			}
		}
		$ret = array();
		foreach($association as $key => $value) {
			if(is_array($value)){
				$value = '['.implode(',',$value).']';
			}
			$ret[] = '"'.$key.'":'.$value;
		}
		$line .= implode(',',$ret).'}';
		$linesData[] = $line;
	}


	echo
	'<script type="text/javascript">
		function modifyLinesData(id_replaced,dataid,table,id,tab_id){
			var t = typeOf(linesData[tab_id][dataid][table]);
			if(t == "array"){
				for(var i in linesData[tab_id][dataid][table]){
					if(inArray(id_replaced,linesData[tab_id][dataid][table]) && linesData[tab_id][dataid][table][i] == id_replaced){
						linesData[tab_id][dataid][table][i]=id;
						break;
					}
				}
			}else{
				linesData[tab_id][dataid][table] = id;
			}
		}
	</script>';

	echo
	'<script type="text/javascript">
		if(!this.columnsData){
			columnsData = new Array();
			columnsData['.$this->params->action_id.'] = {
				'.implode(",\r\n",$columnsData).'
			};
		}else{
			columnsData['.$this->params->action_id.'] = {
				'.implode(",\r\n",$columnsData).'
			};
		}
		if(!this.linesData){
			linesData = new Array();
			linesData['.$this->params->action_id.'] = {
				'.implode(",\r\n",$linesData).'
			};
		}else{
			linesData['.$this->params->action_id.'] = {
				'.implode(",\r\n",$linesData).'
			};
		}

		function sendForm(target,columnname,dataid,tablename,tab_id){
			var url = \''.hikashop_completeLink($edit_url, true, true).'\';
			var data = window.Oby.getFormData(target);
			if(data != "") data += "&";
			data += columnsData[tab_id][columnname];
			if(dataid != ""){
				var t = typeOf(linesData[tab_id][dataid][tablename]);
				if(t == "Array" || t == "array"){
					for(var i = 0; i < linesData[tab_id][dataid][tablename].length; i++){
						data += "&" + encodeURI("hikashop[ids][]") + "=" + encodeURIComponent(linesData[tab_id][dataid][tablename][i]);
					}
				}else{
					data += "&" + encodeURI("hikashop[ids]") + "=" + encodeURIComponent(linesData[tab_id][dataid][tablename]);
				}
				data += "&" + encodeURI("hikashop[dataid]") + "=" + encodeURIComponent(dataid);
				data += "&" + encodeURI("hikashop[tabid]") + "=" + encodeURIComponent(tab_id);
			}
			window.Oby.xRequest(url, {update: target, mode: \'POST\', data: data});
		}
		function onClick(target,columnname,dataid,tablename,tab_id){
			if(target.rel == \'no edit\'){
				target.rel = \'\';
			}else if(target.rel != \'edit\'){
				target.rel = \'edit\';
				sendForm(target,columnname,dataid,tablename,tab_id);
			}
		}


		function onMouseOver(table,column,over,tab_id){
			for(var i in linesData[tab_id]){
				var id = i + \'_\' + table + \'_\' + column;
				var target = document.getElementById(id);
				if(over){
					window.Oby.addClass(target, "hover");
				}else{
					window.Oby.removeClass(target, "hover");
				}
			}
		}

		function onEditSquare(task,target,columnname,dataid,tablename,tab_id){
			if(target.rel ==\'edit\'){
				var url = "";
				if(task=="cancel"){
					url = \''.hikashop_completeLink($cancel_url, true, true).'\';
				}else if(task=="save"){
					url = \''.hikashop_completeLink($save_url, true, true).'\';
				}
				target.rel = \'no edit\';
				var data = window.Oby.getFormData(target);
				if(data != "") data += "&";
				data += columnsData[tab_id][columnname];
				if(dataid != ""){
					var t = typeOf(linesData[tab_id][dataid][tablename]);
					if(t == "Array" || t == "array"){
						for(var i = 0; i < linesData[tab_id][dataid][tablename].length; i++){
							data += "&" + encodeURI("hikashop[ids][]") + "=" + encodeURIComponent(linesData[tab_id][dataid][tablename][i]);
						}
					}else{
						data += "&" + encodeURI("hikashop[ids]") + "=" + encodeURIComponent(linesData[tab_id][dataid][tablename]);
					}
					data += "&" + encodeURI("hikashop[dataid]") + "=" + encodeURIComponent(dataid);
					data += "&" + encodeURI("hikashop[tabid]") + "=" + encodeURIComponent(tab_id);
				}
				window.Oby.xRequest(url, {update: target, mode: \'POST\', data: data});
			}
		}
		function inArray(needle, haystack) {
			var length = haystack.length;
			for(var i = 0; i < length; i++) {
					if(haystack[i] == needle) return true;
			}
			return false;
		}



		function onEditColumn(task,target,table,column,tab_id){
			var ids = new Array();
			for(var i in linesData[tab_id]){
				ids[i] = new Array();
				var t = typeOf(linesData[tab_id][i][table]);
				if(t == "Array" || t == "array"){
					for(var j = 0; j < linesData[tab_id][i][table].length; j++){
						if(typeof linesData[tab_id][i][table][j] !== "undefined"){
							ids[i].push(linesData[tab_id][i][table][j]);
						}
					}
				}else{
					if(typeof linesData[tab_id][i][table] !== "undefined"){
						ids[i].push(linesData[tab_id][i][table]);
					}
				}
			}
			recursive(task,target,table,column,ids);
		}
		function recursive(task,target,table,column,ids){
			var cpt = 0;
			var tab = new Array();
			for(var i in ids){
				if(cpt<5){
					tab.push(ids[i]);
					cpt++;
				}else{
					break;
				}
			}
			var url = "";
			if(task=="cancel"){
				url = \''.hikashop_completeLink($cancel_url, true, true).'\';
			}else if(task=="save"){
				url = \''.hikashop_completeLink($save_url, true, true).'\';
			}
			target.rel = \'no edit\';
			var data = window.Oby.getFormData(target);
			if(data != "") data += "&";
			data += columnsData[tab_id][table+"_"+column];
			for(var i = 0; i < ids.length; i++){
				if(typeof ids[i] !== "undefined"){
					for(var j = 0; j < ids[i].length ; j++){
						data += "&" + encodeURI("hikashop[ids]["+i+"][]") + "=" + encodeURIComponent(ids[i][j]);
					}
				}
			}
			window.Oby.xRequest(url, {update: target, mode: \'POST\', data: data});
		}
	</script>';
?>
<div style="overflow:auto">
	<table id="hikashop_massaction_results" class="adminlist table-hover table table-bordered" cellpadding="1">
		<thead>
			<?php

				if(!empty($this->params->action)){
					$cpt = 0;
					echo '<tr class="title">';
					foreach($this->params->action as $keyTable=>$table){
						foreach($table as $column){
							echo '<td class="massaction-td-column-no-editable" id="'.$this->params->action_id.'_'.$keyTable.'_'.$column.'">';
							echo $column;
							echo '</td>';
						}
					}
					echo '</tr>';
				}
			?>
		</thead>
		<tfoot>
		</tfoot>
		<tbody>
			<?php
				if(!empty($this->params->action)){
					foreach($this->params->elements as $k1=>$element){
						echo '<tr>';
						foreach($this->params->action as $key=>$table){
							foreach($table as $column){
								$column_id = $key.'_id';
								$data_id = $this->params->table.'_id';
								$type = $this->params->types[$column]->type;
								$sub_type = @$this->params->types[$column]->sub_type;
								$id_td = $this->params->action_id.'_'.$element->$data_id.'_'.$key.'_'.$column;
								$columnName = $key.'_'.$column;
								if(isset($element->$column) && ($key===$k1 || $key===$this->params->table)){
									if(!isset($this->params->lock[$key][$column])){
										echo '<td class="massaction-td-square-editable" id="'.$id_td.'" onclick = "onClick(this,\''.$columnName.'\',\''.$element->$data_id.'\',\''.$key.'\',\''.$this->params->action_id.'\')">';
									}else{
										if(isset($this->params->lock[$key][$column]) && $this->params->lock[$key][$column]->square === true){
											$td = '';
											if(is_array($this->params->lock[$key][$column]->ids)){
												foreach($this->params->lock[$key][$column]->ids as $id){
													if($element->$data_id !== $id){continue;}
													$td = '<td class="massaction-td-square-no-editable" id="'.$id_td.'">';
												}
											}else{
												if($this->params->lock[$key][$column]->ids !== 'all'){continue;}
												$td = '<td class="massaction-td-square-no-editable" id="'.$id_td.'">';
											}
											if(empty($td)){
												$id = $element->$data_id.'_'.$this->params->table.'_'.$column;
												echo '<td class="massaction-td-square-editable" id="'.$id_td.'" onclick = "onClick(this,\''.$columnName.'\',\''.$element->$data_id.'\',\''.$key.'\',\''.$this->params->action_id.'\')">';
											}else{
												echo $td;
											}
										}else{
											echo '<td class="massaction-td-square-editable" id="'.$id_td.'" onclick = "onClick(this,\''.$columnName.'\',\''.$element->$data_id.'\',\''.$key.'\',\''.$this->params->action_id.'\')">';
										}
									}
									$square = '';
									$square = $massaction->displayByType($this->params->types,$element,$column);
								}else{
									$square = '';
									$columns_ids = '';
									echo '<td id="'.$id_td.'"';
									foreach($element as $k=>$elem){
										if(!is_array($elem)){continue;}
										foreach($elem as $data){
											if(!isset($data->$column)){
												if(isset($data->exportData->value) && $data->exportData->name == $column){
													if(isset($data->$column_id)){
														$columns_ids .= $data->$column_id.'_';
													}
													$square .= $data->exportData->value;
													$square .= '<br/>';
												}
												continue;
											}
											if($k != $key){continue;}
											if(isset($data->$column_id)){
												$columns_ids .= $data->$column_id.'_';
											}
											$square .= $massaction->displayByType($this->params->types,$data,$column);
											$square .= '<br/>';
										}
									}
									$columns_ids = substr($columns_ids, 0, -1);
									if(!isset($this->params->lock[$key][$column]) && !empty($columns_ids)){
										echo '" onclick = "onClick(this,\''.$columnName.'\',\''.$element->$data_id.'\',\''.$key.'\',\''.$this->params->action_id.'\')" class="massaction-td-square-editable">';
									}else{
										if((isset($this->params->lock[$key][$column]) && isset($this->params->lock[$key][$column]->ids) && $this->params->lock[$key][$column]->square == true) && !empty($columns_ids)){
											$td = '';
											if(is_array($this->params->lock[$key][$column]->ids)){
												foreach($this->params->lock[$key][$column]->ids as $id){
													if($element->$data_id == $id){
														$td = '"rel="3"  class="massaction-td-square-no-editable">';
													}
												}
											}else{
												if($this->params->lock[$key][$column]->ids == 'all'){
													$td = '"rel="1" class="massaction-td-square-no-editable">';
												}
											}
											if(empty($td) && !empty($columns_ids)){
												echo '" onclick = "onClick(this,\''.$columnName.'\',\''.$element->$data_id.'\',\''.$key.'\',\''.$this->params->action_id.'\')" class="massaction-td-square-editable">';
											}else{
												echo $td;
											}
										}else if(!empty($columns_ids)){
											echo '" onclick = "onClick(this,\''.$columnName.'\',\''.$element->$data_id.'\',\''.$key.'\',\''.$this->params->action_id.'\')" class="massaction-td-square-editable">';
										}else{
											echo '"rel="2" class="massaction-td-square-no-editable">';
										}
									}
								}
								echo $square;
								echo '</td>';
							}
						}
						echo '</tr>';
					}
				}
			?>
		</tbody>
	</table>
</div>
