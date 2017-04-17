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
<?php echo $this->html_results; ?>
<?php foreach($this->tables as $table){ ?>
<div id="hikabase_<?php echo $table->table; ?>_massactions" style="display:none">
	<div id="<?php echo $table->table; ?>_triggers_original">
		<?php echo JHTML::_('select.genericlist', $table->typevaluesTriggers, "trigger[".$table->table."][type][__num__]", 'class="inputbox chzn-done not-processed" size="1" onchange="updateMassAction(\'trigger\',\''.$table->table.'\',__num__); refreshSelect(\''.$table->table.'\',\'trigger\', __num__) "', 'value', 'text','trigger'.$table->table.'__num__');
				echo '<span><a style="cursor:  pointer;" onClick="selectTrigger=document.getElementById(\'trigger'.$table->table.'type__num__\') ;selectTrigger.options[0].selected=\'selected\'; document.getElementById(\''.$table->table.'trigger__num__\').style.display=\'none\'; numTriggers[\''.$table->table.'\']--;" ><img src="'.HIKASHOP_IMAGES.'delete2.png" style="margin:0px 0px 0px 3px;"/></a></span>';?>
		<div class="hikamassactionarea" id="<?php echo $table->table; ?>triggerarea___num__"></div>
	</div>
	<?php echo implode('',$table->triggers_html); ?>
	<div id="<?php echo $table->table; ?>_filters_original">
		<?php echo JHTML::_('select.genericlist', $table->typevaluesFilters, "filter[".$table->table."][type][__num__]", 'class="inputbox chzn-done not-processed" size="1" onchange="updateMassAction(\'filter\',\''.$table->table.'\',__num__);countresults(\''.$table->table.'\',__num__); refreshSelect(\''.$table->table.'\',\'filter\', __num__) "', 'value', 'text','filter'.$table->table.'__num__');
				echo '<span><a style="cursor:  pointer;" onClick="selectFilter=document.getElementById(\'filter'.$table->table.'type__num__\') ;selectFilter.options[0].selected=\'selected\';updateMassAction(\'filter\',\''.$table->table.'\',__num__); document.getElementById(\''.$table->table.'filter__num__\').style.display=\'none\'; numFilters[\''.$table->table.'\']--;" ><img src="'.HIKASHOP_IMAGES.'delete2.png" style="margin:0px 0px 0px 3px;"/></a></span>'; ?>
		<span id="<?php echo $table->table; ?>countresult___num__"></span>
		<div class="hikamassactionarea" id="<?php echo $table->table; ?>filterarea___num__"></div>
	</div>
	<?php echo implode('',$table->filters_html); ?>
	<div id="<?php echo $table->table; ?>_actions_original">
		<?php echo JHTML::_('select.genericlist', $table->typevaluesActions, "action[".$table->table."][type][__num__]", 'class="inputbox chzn-done not-processed" size="1" onchange="updateMassAction(\'action\',\''.$table->table.'\',__num__); refreshSelect(\''.$table->table.'\',\'action\', __num__) "', 'value', 'text','action'.$table->table.'__num__');
				echo '<span><a style="cursor:  pointer;" onClick="selectAction=document.getElementById(\'action'.$table->table.'type__num__\') ;selectAction.options[0].selected=\'selected\';updateMassAction(\'action\',\''.$table->table.'\',__num__); document.getElementById(\''.$table->table.'action__num__\').style.display=\'none\'; numActions[\''.$table->table.'\']--;" ><img src="'.HIKASHOP_IMAGES.'delete2.png" style="margin:0px 0px 0px 3px;"/></a></span>';?>
		<div class="hikamassactionarea" id="<?php echo $table->table; ?>actionarea___num__"></div>
	</div>
	<?php echo implode('',$table->actions_html); ?>
</div>
<?php } ?>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&ctrl=massaction" method="post" autocomplete="off" enctype="multipart/form-data" name="adminForm" id="adminForm">
<?php
	if(JRequest::getCmd('tmpl') == 'component'){
		if(empty($this->data_id)){
			hikashop_display(JText::_('PLEASE_SELECT_'.strtoupper($this->data_table)),'warning');
			return;
		}
?>
	<input type="hidden" name="data_id" value="<?php echo $this->data_id; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<fieldset>
		<div class="hikaheader icon-48-hikaaction" style="float: left;"><?php echo JText::_('ACTIONS'); ?></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<table><tr>
			<td><a onclick="javascript:if(confirm('<?php echo JText::_('PROCESS_CONFIRMATION',true);?>')){submitbutton('process');} return false;" href="#" ><span class="icon-32-process" title="<?php echo JText::_('PROCESS',true); ?>"></span><?php echo JText::_('PROCESS'); ?></a></td>
			</tr></table>
		</div>
	</fieldset>
<?php
	}else{

		$this->massaction_name_input = "data[massaction][massaction_name]";
		$this->massaction_description_input = "data[massaction][massaction_description]";
		if($this->translation){
			$this->setLayout('translation');
		}else{
			$this->setLayout('normal');
		}
		echo $this->loadTemplate();
	?>
	<table class="admintable table" width="100%">
		<tr>
			<td class="key">
				<label for="data[banner][massaction_published]">
					<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[massaction][massaction_published]" , '',@$this->element->massaction_published); ?>
			</td>
		</tr>
	</table>
	<?php } ?>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'DATA' ); ?></legend>
<?php
		if(!empty($this->tables)) {
			if(HIKASHOP_J30){
				echo JHTML::_('hikaselect.radiolist', $this->tables, 'data[massaction][massaction_table]', 'class="inputbox" size="1" onclick="updateData(this.value);"', 'value', 'text',$this->element->massaction_table,false,false);
			}else{
				echo JHTML::_('hikaselect.radiolist', $this->tables, 'data[massaction][massaction_table]', 'class="inputbox" size="1" onclick="updateData(this.value);"', 'value', 'text',$this->element->massaction_table);
			}
		} else {
			echo JText::_('ERR_NO_MASSACTION_TABLES');
		}
?>
	</fieldset>
	<div>
	<?php foreach($this->tables as $table){ ?>
		<div id="<?php echo $table->table; ?>"<?php if($table->table != $this->element->massaction_table) echo ' style="display:none"'; ?>>
			<fieldset class="adminform">
				<legend><?php echo $table->text; ?></legend>
				<fieldset class="adminform" >
					<legend><?php echo JText::_('TRIGGERS'); ?></legend>
					<div id="all<?php echo $table->table; ?>triggers">
<?php
				$count = 0;
				if(!empty($this->loadedData->massaction_triggers) && count($this->loadedData->massaction_triggers) != 0 && $table->table == $this->loadedData->massaction_table){
					$count = count($this->loadedData->massaction_triggers);
					foreach($this->loadedData->massaction_triggers as $k => $triggers){
						if(!empty($triggers->name) && $this->loadedData->massaction_table == $table->table){
							echo '<div id="'.$table->table.'trigger'.$k.'" class="plugarea">';
							$min = array_keys($this->loadedData->massaction_triggers);
							if($k > $min[0]) echo JText::_('HIKA_AND').' ';
							echo JHTML::_('select.genericlist', $table->typevaluesTriggers, 'trigger['.$table->table.'][type]['.$k.']', 'class="inputbox chzn-done not-processed" size="1" onchange="updateMassAction(\'trigger\',\''.$table->table.'\','.$k.');"', 'value', 'text',$triggers->name);
							echo '<span><a style="cursor:pointer;" onClick="selectTrigger=document.getElementById(\'trigger'.$table->table.'type'.$k.'\') ;selectTrigger.options[0].selected=\'selected\'; document.getElementById(\''.$table->table.'trigger'.$k.'\').style.display=\'none\';numTriggers[\''.$table->table.'\']--;" ><img src="'.HIKASHOP_IMAGES.'delete2.png" style="margin:0px 0px 0px 3px;"/></a></span>';
							echo '</div>';
						}
					}
				}else{
					echo '<div id="'.$table->table.'trigger0" class="plugarea">';
					echo JHTML::_('select.genericlist', $table->typevaluesTriggers, "trigger[".$table->table."][type][0]", 'class="inputbox chzn-done not-processed" size="1" onchange="updateMassAction(\'trigger\',\''.$table->table.'\',0);"', 'value', 'text');
					echo '<span><a style="cursor:pointer;" onClick="selectTrigger=document.getElementById(\'trigger'.$table->table.'type0\') ;selectTrigger.options[0].selected=\'selected\'; document.getElementById(\''.$table->table.'trigger0\').style.display=\'none\';numTriggers[\''.$table->table.'\']--;" ><img src="'.HIKASHOP_IMAGES.'delete2.png" style="margin:0px 0px 0px 3px;"/></a></span>';
					echo '</div>';
				}
				if(HIKASHOP_J30 && false){
					for($i=0;$i<=$count;$i++){
						?> <script type="text/javascript">jQuery("#<?php echo $table->table; ?>trigger<?php echo $i; ?> .not-processed").removeClass("not-processed").removeClass("chzn-done").chosen(); </script><?php
					}
				}
				echo '</div><button class="btn" onclick="addHikaMassAction(\''.$table->table.'\',\'trigger\'); refreshSelect(\''.$table->table.'\',\'trigger\' ,-1) ;return false;">'.JText::_('ADD_TRIGGER').'</button>
				</fieldset>';
				$count = 0;
				$key = 0;
				if(!empty($this->loadedData->massaction_filters) && count($this->loadedData->massaction_filters) > 1 && $table->table == $this->loadedData->massaction_table){
					$count = count($this->loadedData->massaction_filters);
					if(count($this->loadedData->massaction_filters) > 2){
						echo '<fieldset class="adminform" ><legend>'.JText::_( 'FILTERS' ).'</legend><div id="all'.$table->table.'filters">';
						foreach($this->loadedData->massaction_filters as $k => $filter){
							if(!empty($filter->name) && $this->loadedData->massaction_table == $table->table){
								if($filter->name == 'limit')
									continue;
								echo '<div id="'.$table->table.'filter'.$k.'" class="plugarea">';
								$min = array_keys($this->loadedData->massaction_filters);
								if($k > $min[1]) echo JText::_('HIKA_AND').' ';
								if($filter->name == 'limit'){
									echo '<input type="hidden" name="filter['.$table->table.'][type]['.$k.']" value="limit"/>';
								}else{
									echo JHTML::_('select.genericlist', $table->typevaluesFilters, "filter[".$table->table."][type][$k]", 'class="inputbox chzn-done not-processed" size="1" onchange="updateMassAction(\'filter\',\''.$table->table.'\','.$k.');countresults(\''.$table->table.'\','.$k.');"', 'value', 'text',$filter->name);
									echo '<span id="'.$table->table.'countresult_'.$k.'"></span>';
									echo '<span><a style="cursor:  pointer;" onClick="selectFilter=document.getElementById(\'filter'.$table->table.'type'.$k.'\') ;selectFilter.options[0].selected=\'selected\';updateMassAction(\'filter\',\''.$table->table.'\','.$k.'); document.getElementById(\''.$table->table.'filter'.$k.'\').style.display=\'none\'; numFilters[\''.$table->table.'\']--;" ><img src="'.HIKASHOP_IMAGES.'delete2.png" style="margin:0px 0px 0px 3px;"/></a></span>';
								}
								echo $filter->html.'</div>';
							}
						}
						echo '</div><button class="btn" onclick="addHikaMassAction(\''.$table->table.'\',\'filter\'); refreshSelect(\''.$table->table.'\',\'filter\',-1) ;return false;">'.JText::_('ADD_FILTER').'</button></fieldset>';

					}else{
						echo '<fieldset class="adminform" ><legend>'.JText::_( 'FILTERS' ).'</legend><div id="all'.$table->table.'filters">';
						?><script type="text/javascript">addHikaMassAction('<?php echo $table->table; ?>','filter')</script><?php
						if(HIKASHOP_J30 && false){
							?><script type="text/javascript">jQuery('#<?php echo $table->table; ?>filter1 .not-processed').removeClass('not-processed').removeClass('chzn-done').chosen();</script><?php
						}
						echo '</div><button class="btn" onclick="addHikaMassAction(\''.$table->table.'\',\'filter\'); refreshSelect(\''.$table->table.'\',\'filter\',-1) ;return false;">'.JText::_('ADD_FILTER').'</button></fieldset>';
					}

					$exist = 0;
					foreach($this->loadedData->massaction_filters as $k => $filter){
						if(!empty($filter->name) && $this->loadedData->massaction_table == $table->table && $filter->name == 'limit'){
							$exist = 1;
							echo '<fieldset class="adminform" ><legend>'.JText::_( 'HIKA_LIMITATIONS' ).'</legend><div id="all'.$table->table.'limitations">';
							echo '<div id="'.$table->table.'filter'.$k.'" class="plugarea">';
							$min = array_keys($this->loadedData->massaction_filters);
							if($k > $min[1]) echo JText::_('HIKA_AND').' ';
							if($filter->name == 'limit'){
								echo '<input type="hidden" name="filter['.$table->table.'][type]['.$k.']" value="limit"/>';
							}else{
								echo JHTML::_('select.genericlist', $table->typevaluesFilters, "filter[".$table->table."][type][$k]", 'class="inputbox chzn-done not-processed" size="1" onchange="updateMassAction(\'filter\',\''.$table->table.'\','.$k.');countresults(\''.$table->table.'\','.$k.');"', 'value', 'text',$filter->name);
								echo '<span id="'.$table->table.'countresult_'.$k.'"></span>';
								echo '<span><a style="cursor:  pointer;" onClick="selectFilter=document.getElementById(\'filter'.$table->table.'type'.$k.'\') ;selectFilter.options[0].selected=\'selected\';updateMassAction(\'filter\',\''.$table->table.'\','.$k.'); document.getElementById(\''.$table->table.'filter'.$k.'\').style.display=\'none\'; numFilters[\''.$table->table.'\']--;" ><img src="'.HIKASHOP_IMAGES.'delete2.png" style="margin:0px 0px 0px 3px;"/></a></span>';
							}
							echo $filter->html.'</div>';
							echo '</div></fieldset>';
						}
					}
					if(!$exist){
						echo '<fieldset class="adminform" ><legend>'.JText::_( 'HIKA_LIMITATIONS' ).'</legend><div id="all'.$table->table.'limitations">';
						if(!isset($this->loadedData->massaction_filters[0])) $key = 0;
						else $key = ++$count;
						$count += 1;

						$value = new stdClass();
						$value->name = 'limit';
						$value->type = $table->table;
						if(!isset($value->data['start'])) $value->data['start'] = 0;
						if(!isset($value->data['value'])) $value->data['value'] = 500;
						echo '<input type="hidden" name="filter['.$table->table.'][type]['.$key.']" value="limit"/>';
						echo '<div id="'.$table->table.'filter'.$key.'" class="plugarea">';
						echo '<div id="'.$table->table.'filter'.$key.'limit">'.JText::_('HIKA_START').' : <input type="text" name="filter['.$table->table.']['.$key.'][limit][start]" value="'.$value->data['start'].'" /> '.JText::_('VALUE').' : <input type="text" name="filter['.$table->table.']['.$key.'][limit][value]" value="'.$value->data['value'].'"/>'.'</div>';
						echo '</div>';
						echo '</div></fieldset>';
					}

				}else{
					echo '<fieldset class="adminform" ><legend>'.JText::_( 'FILTERS' ).'</legend><div id="all'.$table->table.'filters">';
					?><script type="text/javascript">addHikaMassAction('<?php echo $table->table; ?>','filter')</script><?php
					if(HIKASHOP_J30 && false){
						?><script type="text/javascript">jQuery('#<?php echo $table->table; ?>filter1 .not-processed').removeClass('not-processed').removeClass('chzn-done').chosen();</script><?php
					}
					echo '</div><button class="btn" onclick="addHikaMassAction(\''.$table->table.'\',\'filter\'); refreshSelect(\''.$table->table.'\',\'filter\',-1) ;return false;">'.JText::_('ADD_FILTER').'</button></fieldset>';

					echo '<fieldset class="adminform" ><legend>'.JText::_( 'HIKA_LIMITATIONS' ).'</legend><div id="all'.$table->table.'limitations">';
					$value = new stdClass();
					$value->name = 'limit';
					$value->type = $table->table;
					if(!isset($value->data['start'])) $value->data['start'] = 0;
					if(!isset($value->data['value'])) $value->data['value'] = 500;
					echo '<input type="hidden" name="filter['.$table->table.'][type][0]" value="limit"/>';
					echo '<div id="'.$table->table.'filter0" class="plugarea">';
					echo '<div id="'.$table->table.'filter0limit">'.JText::_('HIKA_START').' : <input type="text" name="filter['.$table->table.'][0][limit][start]" value="'.$value->data['start'].'" /> '.JText::_('VALUE').' : <input type="text" name="filter['.$table->table.'][0][limit][value]" value="'.$value->data['value'].'"/>'.'</div>';
					echo '</div>';

					echo '</div></fieldset>';

				}

				if(HIKASHOP_J30 && false){
					for($i=0;$i<=$count;$i++){
						?> <script type="text/javascript">jQuery("#<?php echo $table->table; ?>filter<?php echo $i; ?> .not-processed").removeClass("not-processed").removeClass("chzn-done").chosen(); </script><?php
					}
				}


				echo '
				<fieldset class="adminform">
					<legend>'. JText::_( 'ACTIONS' ).'</legend>
					<div id="all'.$table->table.'actions">';
				$count = 0;
				if(!empty($this->loadedData->massaction_actions) && count($this->loadedData->massaction_actions) != 1 && $table->table == $this->loadedData->massaction_table){
					$count = count($this->loadedData->massaction_actions);
					foreach($this->loadedData->massaction_actions as $k => $action){
						if(!empty($action->name) && $this->loadedData->massaction_table == $table->table){
							echo '<div id="'.$table->table.'action'.$k.'" class="plugarea">';
							$min = array_keys($this->loadedData->massaction_actions);
							if($k > $min[0]) echo JText::_('HIKA_AND').' ';
							echo JHTML::_('select.genericlist', $table->typevaluesActions, "action[".$table->table."][type][$k]", 'class="inputbox chzn-done not-processed" size="1" onchange="updateMassAction(\'action\',\''.$table->table.'\','.$k.');""', 'value', 'text',$action->name);
							echo '<span><a style="cursor:  pointer;" onClick="selectAction=document.getElementById(\'action'.$table->table.'type'.$k.'\') ;selectAction.options[0].selected=\'selected\';updateMassAction(\'action\',\''.$table->table.'\','.$k.'); document.getElementById(\''.$table->table.'action'.$k.'\').style.display=\'none\'; numActions[\''.$table->table.'\']--;" ><img src="'.HIKASHOP_IMAGES.'delete2.png" style="margin:0px 0px 0px 3px;"/></a></span>';
							echo $action->html.'</div>';
						}
					}
				}else{
					?><script type="text/javascript">addHikaMassAction('<?php echo $table->table; ?>','action'); </script><?php
				}
				if(HIKASHOP_J30 && false){
					for($i=0;$i<=$count;$i++){
						?> <script type="text/javascript">jQuery("#<?php echo $table->table; ?>action<?php echo $i; ?> .not-processed").removeClass("not-processed").removeClass("chzn-done").chosen(); </script><?php
					}
				}
		echo		'</div>
					<button class="btn" onclick="addHikaMassAction(\''.$table->table.'\',\'action\'); refreshSelect(\''.$table->table.'\',\'action\',-1) ;return false;">'.JText::_('ADD_ACTION').'</button>
				</fieldset>';
		echo '</fieldset>';
		echo '</div>';
		}?>
	</div>
	<input id="cidformmassaction" type="hidden" name="cid[]" value="<?php echo @$this->element->massaction_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="massaction" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
