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

class MassactionViewMassaction extends hikashopView{
	var $ctrl= 'massaction';
	var $nameListing = 'HIKA_MASSACTION';
	var $nameForm = 'HIKA_MASSACTION';
	var $icon = 'massaction';

	function display($tpl=null,$params=null){
		$this->params = $params;
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();

		$enabled = JPluginHelper::isEnabled('system', 'hikashopmassaction');
		if(!$enabled){
			if(HIKASHOP_J25)
				$query = 'UPDATE '.hikashop_table('extensions',false).' SET enabled = 1 WHERE type = "plugin" AND element = "hikashopmassaction" AND folder = "system";';
			else
				$query = 'UPDATE '.hikashop_table('plugins',false).' SET published = 1 WHERE element = "hikashopmassaction" AND folder = "system";';

			$db = JFactory::getDBO();
			$db->setQuery($query);
			$success = $db->query();
			if($success)
				$app->enqueueMessage(JText::_('HIKA_MASSACTION_SYSTEM_PLUGIN_PUBLISHED'));
		}

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.massaction_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$selectedType = $app->getUserStateFromRequest( $this->paramBase.".massaction_table",'massaction_table','','string');
		$database	= JFactory::getDBO();

		$filters = array();
		if(!empty($selectedType)){
			$filters[] = 'a.massaction_table='.$database->Quote($selectedType);
		}
		$searchMap = array('a.massaction_id','a.massaction_name','a.massaction_description','a.massaction_table');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower(trim($pageInfo->search)),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('massaction').' AS a '.$filters.$order;
		$database->setQuery('SELECT a.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'massaction_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$table = hikashop_get('type.masstable');
		$this->assignRef('tabletype',$table);
		$this->assignRef('selectedType',$selectedType);

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_massaction_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name' => 'custom', 'icon'=>'copy','alt'=>JText::_('HIKA_COPY'), 'task' => 'copy','display'=>$manage),
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_massaction_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);


	}
	function form(){
		$massaction_id = hikashop_getCID('massaction_id');
		$class = hikashop_get('class.massaction');
		if(!empty($massaction_id)){
			$element = $class->get($massaction_id,true);
			$task='edit';
		}else{
			$element = new stdClass();
			$element->massaction_published = 1;
			$element->massaction_table = 'product';
			$task='add';
		}

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&massaction_id='.$massaction_id);

		$this->toolbar = array(
			array('name' => 'confirm','check'=>false, 'msg'=> JText::_('PROCESS_WARNING'),'icon'=>'upload','alt'=>JText::_('PROCESS'), 'task' => 'process'),
			'|',
			'save',
			array('name' => 'save2new', 'display' => version_compare(JVERSION,'1.7','>=')),
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$this->assignRef('element',$element);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_massaction',@$element->massaction_id,$element);
			jimport('joomla.html.pane');
			$config =& hikashop_config();
			$multilang_display=$config->get('multilang_display','tabs');
			if($multilang_display=='popups') $multilang_display = 'tabs';
			$tabs = hikashop_get('helper.tabs');
			$this->assignRef('tabs',$tabs);
			$this->assignRef('transHelper',$transHelper);

		}
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('translation',$translation);

		$tables = array();
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onMassactionTableLoad', array( &$tables ) );
		$loadedData = $element;
		foreach($tables as $k => $table){
			$tables[$k]->triggers = array();
			$tables[$k]->triggers_html = array();
			$dispatcher->trigger('onMassactionTableTriggersLoad', array( &$tables[$k], &$tables[$k]->triggers,&$tables[$k]->triggers_html, &$loadedData) );
			$tables[$k]->filters = array();
			$tables[$k]->filters_html = array();
			$dispatcher->trigger('onMassactionTableFiltersLoad', array( &$tables[$k], &$tables[$k]->filters,&$tables[$k]->filters_html, &$loadedData) );
			$tables[$k]->actions = array();
			$tables[$k]->actions_html = array();
			$dispatcher->trigger('onMassactionTableActionsLoad', array( &$tables[$k], &$tables[$k]->actions,&$tables[$k]->actions_html, &$loadedData) );
			$table->typevaluesTriggers = array();
			$table->typevaluesFilters = array();
			$table->typevaluesActions = array();
			$table->typevaluesTriggers[] = JHTML::_('select.option', '',JText::_('TRIGGER_SELECT'));
			$table->typevaluesFilters[] = JHTML::_('select.option', '',JText::_('FILTER_SELECT'));
			$table->typevaluesActions[] = JHTML::_('select.option', '',JText::_('ACTION_SELECT'));

			foreach($tables[$k]->triggers as $oneType => $oneName){
				$table->typevaluesTriggers[] = JHTML::_('select.option', $oneType,$oneName);
			}
			foreach($tables[$k]->filters as $oneType => $oneName){
				$table->typevaluesFilters[] = JHTML::_('select.option', $oneType,$oneName);
			}
			foreach($tables[$k]->actions as $oneType => $oneName){
				$table->typevaluesActions[] = JHTML::_('select.option', $oneType,$oneName);
			}
		}
		$this->assignRef('tables',$tables);
		$this->assignRef('loadedData',$loadedData);


		$js = "

			function updateMassAction(type,table,filterNum){
				var w = window, d = w.document, currentFilterType = d.getElementById(type+table+'type'+filterNum).value;
				if(!currentFilterType){
					d.getElementById(table+type+'area_'+filterNum).innerHTML = '';
					if(type=='filter') d.getElementById(table+'countresult_'+filterNum).innerHTML = '';
					return;
				}
				var filterArea = table+type+'__num__'+currentFilterType;
				if(d.getElementById(filterArea))
						w.Oby.updateElem( d.getElementById(table+type+'area_'+filterNum), d.getElementById(filterArea).innerHTML.replace(/__num__/g,filterNum));
				else d.getElementById(table+type+'area_'+filterNum).innerHTML = '';
			}

			";
		 $js .="
			var numTriggers = {};
			var numFilters = {};
			var numActions = {};
			var triggerId = {};
			var filterId = {};
			var actionId = {};
		";

		foreach($tables as $k => $table){
			if(empty($loadedData->massaction_triggers) || $table->table != $loadedData->massaction_table){
				$js .="numTriggers['".$table->table."'] = 1;";
				$js .="triggerId['".$table->table."'] = 1;";
			}else{
				$triggerId = max(array_keys($loadedData->massaction_triggers));
				if(!is_int($triggerId)) $triggerId = 1;
				else $triggerId++;

				$countTrigger=1;
				foreach($loadedData->massaction_triggers as $trigger){
					if(is_int($k)){
						$countTrigger++;
					}
				}

				$js .="numTriggers['".$table->table."'] = ".$countTrigger.";";
				$js .="triggerId['".$table->table."'] = ".$triggerId.";";
			}
			if(empty($loadedData->massaction_filters) || $table->table != $loadedData->massaction_table){
				$js .="numFilters['".$table->table."'] = 1;";
				$js .="filterId['".$table->table."'] = 1;";
			}else{
				$filterId = max(array_keys($loadedData->massaction_filters));
				if(!is_int($filterId)) $filterId = 1;
				else $filterId++;

				$countFilter = 1;
				foreach($loadedData->massaction_filters as $k => $filter){
					if(is_int($k)){
						$countFilter++;
					}
				}

				$js .="numFilters['".$table->table."'] = ".$countFilter.";";
				$js .="filterId['".$table->table."'] = ".$filterId.";";
			}
			if(empty($loadedData->massaction_actions) || $table->table != $loadedData->massaction_table){
				$js .="numActions['".$table->table."'] = 0;";
				$js .="actionId['".$table->table."'] = 0;";
			}else{
				$actionId = max(array_keys($loadedData->massaction_actions));
				if(!is_int($actionId)) $actionId = 0;
				else $actionId++;

				$countAction = 0;
				foreach($loadedData->massaction_actions as $k => $action){
					if(is_int($k)){
						$countAction++;
					}
				}

				$js .="numActions['".$table->table."'] = ".$countAction.";";
				$js .="actionId['".$table->table."'] = ".$actionId.";";
			}
		}

		$js .= "
				function addHikaMassAction(table,type){
					var newdiv = document.createElement('div');
					if(type=='filter'){
						var count=numFilters[table]-1;
						var theId=filterId[table];
					}else if(type=='trigger'){
						var count=numTriggers[table];
						var theId=triggerId[table];
					}else if(type=='action'){
						var count=numActions[table];
						var theId=actionId[table];
					}
					newdiv.id = table+type+theId;
					newdiv.className = 'plugarea';
					newdiv.innerHTML = '';
					if(count > 0) newdiv.innerHTML += '".JText::_('HIKA_AND')."';
					newdiv.innerHTML += document.getElementById(table+'_'+type+'s_original').innerHTML.replace(/__num__/g, theId);
					if(document.getElementById('all'+table+type+'s')){
						document.getElementById('all'+table+type+'s').appendChild(newdiv);
						updateMassAction(type,table,theId);
						if(type=='filter'){
							numFilters[table]++;
							filterId[table]++;
						}else if(type=='trigger'){
							numTriggers[table]++;
							triggerId[table]++;
						}else if(type=='action'){
							numActions[table]++;
							actionId[table]++;
						}
					}
				}
		";

		if(HIKASHOP_J30){
			$js .= '
				function refreshSelect(table,type, id){
					if(type=="filter"){
						var count=filterId[table];
					}else if(type=="trigger"){
						var count=triggerId[table];
					}else if(type=="action"){
						var count=actionId[table];
					}
					if(id!=-1){
						var count = id;
					}else{
						count=count-1;
					}
				}
			';
		}else{
			$js .= 'function refreshSelect(table,type, id){}';
		}



		if(!HIKASHOP_J16){
			$js .= 	'function submitbutton(pressbutton){
						if (pressbutton != \'save\') {
							submitform( pressbutton );
							return;
						}';
		}else{
			$js .= 	'Joomla.submitbutton = function(pressbutton) {
						if (pressbutton != \'save\') {
							Joomla.submitform(pressbutton,document.adminForm);
							return;
						}';
		}
		if(!HIKASHOP_J16){
			$js .= 	"submitform( pressbutton );";
		}else{
			$js .= 	"Joomla.submitform(pressbutton,document.adminForm);";
		}
		$js .="}";

		$js .= "
				function countresults(table,num){
					document.getElementById(table+'countresult_'+num).innerHTML = '<span class=\"onload\"></span>';
					var form = document.id('adminForm');
					var data = form.toQueryString();
					data += '&task=countresults&ctrl=massaction';
					try{
						new Ajax('index.php?option=com_hikashop&tmpl=component&ctrl=massaction&task=countresults&table='+table+'&num='+num,{
							method: 'post',
							data: data,
							update: document.getElementById(table+'countresult_'+num)
						}).request();
					}catch(err){
						new Request({
							method: 'post',
							data: data,
							url: 'index.php?option=com_hikashop&tmpl=component&ctrl=massaction&task=countresults&table='+table+'&num='+num,
							onSuccess: function(responseText, responseXML) {
								document.getElementById(table+'countresult_'+num).innerHTML = responseText;
							}
						}).send();
					}
				}";
		if(!isset($loadedData->massaction_table)) $currentTable = 'product'; else $currentTable = $loadedData->massaction_table;
		$js .= '
		var currentoption = \''.$currentTable.'\';
		function updateData(newoption){
			document.getElementById(currentoption).style.display = "none";
			document.getElementById(newoption).style.display = \'block\';
			currentoption = newoption;
		}';

		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}



		if(!empty($_POST['html_results'])){
			$html_results = $_POST['html_results'];
		}
		$this->assignRef('html_results',$html_results);
		$doc->addScriptDeclaration( $js );
		hikashop_loadJslib('mootools');

	}

	function export(){
		$massaction = hikashop_get('class.massaction');
		$this->params = $massaction->sortResult($this->params->table,$this->params);
		$this->loadFields($this->params);
		$currency = hikashop_get('class.currency');
		$this->assignRef('currency',$currency);
		$weight = hikashop_get('type.weight');
		$this->assignRef('weight',$weight);
	}

	function cell(){
		$url = 'massaction&task=process';
		$url .= '&'.hikashop_getFormToken().'=1';

		if(isset($_POST['hikashop'])){

			$hikashop = JRequest::getVar('hikashop','');
			$this->params = new stdClass();
			$this->params->data = $hikashop['data'];
			$this->params->table = $hikashop['table'];
			$this->params->tab_id = $hikashop['tabid'];
			$this->params->column = $hikashop['column'];
			if(isset($hikashop['values']) && isset($_POST['data']['values'])){
				foreach($hikashop['values'] as $key=>$value){
					$values[$key]=$value;
				}
				foreach($_POST['data']['values'] as $key=>$value){
					$values[$key]=$value;
				}
			}else if(isset($hikashop['values'])){
				$values = $hikashop['values'];
			}else if(isset($_POST['data']['values'])){
				$values = $_POST['data']['values'];
			}
			$this->params->values = $values;
			$this->params->action[$this->params->table][$this->params->column] = new stdClass();
			$this->params->action[$this->params->table][$this->params->column] = $this->params->column;
			$this->params->types[$this->params->column] = new stdClass();
			$this->params->types[$this->params->column]->type = $hikashop['type'];

			if(isset($hikashop['dataid'])){


				if(!isset($this->dispatcher)){
					JPluginHelper::importPlugin('hikashop');
					$this->dispatcher = JDispatcher::getInstance();
				}
				$reload = array();
				$this->dispatcher->trigger('onReloadPageMassActionAfterEdition',array(&$reload));
				$this->assignRef('reload',$reload);

				$this->params->data_id = $hikashop['dataid'];

				if(is_array($hikashop['ids'])){
					$this->params->ids = $hikashop['ids'];
				}else{
					$this->params->ids[] = $hikashop['ids'];
				}
				$rows = array();

				if(isset($reload[$this->params->table][$this->params->column])){
					echo
						'<script type="text/javascript">
							url = \''.hikashop_completeLink($url, false, true).'\';
							url += \'&cid=\' + document.getElementById(\'cidformmassaction\').value;
							window.location = url;
						</script>';
					return;
				}

				foreach($this->params->ids as $id){
					if(isset($this->params->values[$id])){
						$row = $this->_loadResults($this->params->data,$this->params->data_id,$this->params->table,$this->params->column,$hikashop['type'],$id,$this->params->values[$id]);
						if(is_array($row)){
							$rows = $row;
							break;
						}else{
							$rows[] = $row;
						}
					}
				}

				$table_id = $this->params->table.'_id';
				$this->params->ids = array();
				echo '<script type="text/javascript">';
				foreach($rows as $row){
					foreach($this->params->values as $id_replaced=>$id){
						if($id == $row->$table_id){
							echo 'modifyLinesData(\''.$id_replaced.'\',\''.$this->params->data_id.'\',\''.$this->params->table.'\',\''.$row->$table_id.'\',\''.$this->params->tab_id.'\');';

						}
					}

					$this->params->ids[] = $row->$table_id;
				}
				echo '</script>';
				$currency = hikashop_get('class.currency');
				$this->assignRef('currency',$currency);

				$table = $this->params->table;
				$this->params->elements = array();
				if($table == $this->params->data){
					foreach($rows as $row){
						$this->params->elements[] = $row;
					}
				}else{
					$this->params->elements[0] = new stdClass();
					$this->params->elements[0]->$table = $rows;
				}
				$massaction = hikashop_get('class.massaction');
				$this->params = $massaction->sortResult($this->params->table,$this->params);
				$this->loadFields($this->params);
			}else{

			}
		}
	}


	function _loadResults($data,$data_id,$table,$column,$type,$id,$value){
		$database	= JFactory::getDBO();
		$query = '';
		hikashop_securefield($column);
		hikashop_securefield($table);
		hikashop_securefield($data);

		if(!isset($this->dispatcher)){
			JPluginHelper::importPlugin('hikashop');
			$this->dispatcher = JDispatcher::getInstance();
		}
		$this->dispatcher->trigger('onLoadResultMassActionAfterEdition',array($data,$data_id,$table,$column,$type,$id,$value,&$query));

		if(!empty($query) && !is_array($query)){
			$database->setQuery($query);
			$row = $database->loadObject();
			return $row;
		}else if(!empty($query) && is_array($query)){
			return $query;
		}
		return false;
	}

	function results(){
		$massaction = hikashop_get('class.massaction');
		$this->params = $massaction->sortResult($this->params->table,$this->params);
		$this->loadFields($this->params);
		$currency = hikashop_get('class.currency');
		$this->assignRef('currency',$currency);
		$weight = hikashop_get('type.weight');
		$this->assignRef('weight',$weight);
	}

	function editcell(){
		$database = JFactory::getDBO();
		if(isset($_POST['hikashop'])){
			$query = '';
			$hikashop = JRequest::getVar('hikashop','');
			$this->assignRef('type', $hikashop['type']);
			$this->assignRef('column', $hikashop['column']);
			$this->assignRef('table', $hikashop['table']);
			$this->assignRef('data', $hikashop['data']);
			$this->assignRef('data_id', $hikashop['dataid']);
			$this->assignRef('tab_id', $hikashop['tabid']);

			$types = hikashop_get('type.currency');
			$this->assignRef('types',$types);
			$volume = hikashop_get('type.volume');
			$this->assignRef('volume',$volume);
			$status = hikashop_get('type.categorysub');
			$status->type = 'status';
			$this->assignRef('status',$status);

			if(isset($hikashop['sub_type'])){
				$this->assignRef('sub_type', $hikashop['sub_type']);
			}
			if(isset($hikashop['ids'])){
				if(is_array($hikashop['ids'])){
					$ids = $hikashop['ids'];
				}else{
					$ids[] = $hikashop['ids'];
				}

				$this->assignRef('ids', $ids);
				$data = $this->data;
				$data_id = $this->data_id;
				$table = $this->table;
				$column = $this->column;
				$type = $this->type;

				hikashop_securefield($table);
				hikashop_securefield($column);
				JArrayHelper::toInteger($ids);
				$this->assignRef('ids', $ids);
				if(!empty($ids)){
					if(!isset($this->dispatcher)){
						JPluginHelper::importPlugin('hikashop');
						$this->dispatcher = JDispatcher::getInstance();
					}
					$this->dispatcher->trigger('onLoadDatatMassActionBeforeEdition',array($data,$data_id,$table,$column,$type,$ids,&$query,&$this));
					if(!empty($query)){
						$database->setQuery($query);
						$rows = $database->loadObjectList();
						$this->assignRef('rows',$rows);
					}
				}
				if(isset($hikashop['sub_type'])){
					$this->type = $hikashop['sub_type'];
				}
			}
		}
	}

	function sortIds($array){
		if(is_array($array)){
			$tmp = array();
			for($i = 0;$i<count($array);$i++){
				$tmp[$i] = $array[$i];
				for($j = $i+1;$j<count($array);$j++){
					if((int)$array[$i] > (int)$array[$j]){
						$tmp[$i] = $array[$j];
						$array[$j] = $array[$i];
						$array[$i] = $tmp[$i];

					}
				}
			}
			return $tmp;
		}
		return false;
	}

	function loadFields(&$params){
		$database = JFactory::getDBO();
		$columns = array();
		$tables = array();
		foreach($params->action as $key=>$table){
			foreach($table as $action){
				if(!in_array($action,$columns)){
					$columns[] = $action;
				}
				if(!in_array($key,$table)){
					$tables[] = $key;
				}
			}
		}
		if(!empty($columns)){
			$query = 'SELECT field_table,field_type,field_namekey';
			$query .= ' FROM '.hikashop_table('field');
			$query .= ' WHERE field_namekey = '.$database->Quote($columns[0]);
			foreach($columns as $column){
				$query .= ' OR field_namekey = '.$database->Quote($column);
			}
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			foreach($rows as $row){
				foreach($params->action as $table){
					if(!isset($table[$row->field_namekey])){continue;}
					$params->types[$row->field_namekey]->type = 'custom_'.$row->field_type;
				}
			}
		}
	}

	function displayField($field,$params){
		$html = '';
		switch($field){
			case 'dropdawn':
				$html.= '<select name="'.$params['name'].'">';
				if($params['type'] == 'sub_id'){
					$html.= '<option value="0">0</option>';
				}
				foreach($params['rows'] as $row){
					$column_id = $params['column_id'];

					$square = array();
					foreach($params['columns'] as $column){
						if(isset($row->$column)){
							$square[] = $row->$column;
						}
					}
					$sel = '';
					if($params['id'] == $row->$column_id)
						$sel = ' selected="selected"';
					$html.= '<option'.$sel.' value="'.$square[0].'">' . implode(' - ',$square) . '</option>';
				}
				$html.= '</select>';
				break;
			case 'input':
				$html.= '<input name="'.$params['name'].'" value="'.$params['value'].'"/>';
				$html.= '<br/>';
				break;
			case 'textarea':
				$html.= '<textarea name="'.$params['name'].'">'.$params['value'].'</textarea><br/>';
				break;
			case 'custom':
				$ret = call_user_func_array(array($params['class'], "display"), $params['params']);
				if(!empty($ret) && is_string($ret)){
					$html.= $ret;
				}
				break;
			case 'date':
				$html.= JHTML::_('calendar', hikashop_getDate(@$params['value'],'%Y-%m-%d %H:%M'), $params['name'],$params['id'],'%Y-%m-%d %H:%M',array('size'=>'20'));
				$html.= '<br/>';
				break;
			default:
				if(!isset($this->dispatcher)){
					JPluginHelper::importPlugin('hikashop');
					$this->dispatcher = JDispatcher::getInstance();
				}
				$this->dispatcher->trigger('onDisplayFieldMassAction'.$field,array($field,$params));
				break;
		}
		$html.= '<br/>';
		return $html;
	}
}
