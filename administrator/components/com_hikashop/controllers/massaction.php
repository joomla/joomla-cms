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
class MassactionController extends hikashopController{
	var $type='massaction';
	var $pkey = 'massaction_id';
	var $table = 'massaction';

	function __construct(){
		parent::__construct();
		$this->display[]='countresults';
		$this->modify[]='process';
		$this->modify_views[]='displayassociate';
		$this->modify_views[]='results';
		$this->modify_views[]='editcell';
		$this->modify[]='savecell';
		$this->modify[]='copy';
		$this->modify_views[]='cancel_edit';
		$this->display[]='export';
	}

	function editcell(){
		JRequest::setVar( 'layout', 'editcell' );
		return parent::display();
	}

	function export(){
		JRequest::setVar( 'layout', 'export' );
		return parent::display();
	}

	function cancel_edit(){
		JRequest::setVar( 'layout', 'cell' );
		return parent::display();
	}

	function savecell(){
		$massactionClass = hikashop_get('class.massaction');

		if(isset($_POST['hikashop'])){
			$hikashop = JRequest::getVar( 'hikashop', '' );

			$data = $hikashop['data'];
			$table = $hikashop['table'];
			$column = $hikashop['column'];
			$type = $hikashop['type'];
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

			if(isset($hikashop['dataid'])){
				$data_id = $hikashop['dataid'];
				$ids = array();
				if(is_array($hikashop['ids'])){
					$ids = $hikashop['ids'];
				}else{
					$ids[] = $hikashop['ids'];
				}
				foreach($ids as $id){
					if(isset($values[$id])){
						$massactionClass->editionSquare($data,$data_id,$table,$column,$values[$id],$id,$type);
					}
				}

			}else{
				foreach($hikashop['ids'] as $data_id=>$ids){
					foreach($ids as $id){
						$massactionClass->editionSquare($data,$data_id,$table,$column,$values['column'],$id,$type);
					}
				}
			}

		}
		JRequest::setVar( 'layout', 'cell' );
		return parent::display();
	}

	function process(){
		if(!empty($_POST)){
			$this->store();
		}

		$massactionClass = hikashop_get('class.massaction');
		$massaction = $massactionClass->get(JRequest::getInt('cid'));
		$elements = array();
		ob_start();
		$massactionClass->process($massaction,$elements);
		$html = ob_get_clean();
		$_POST['html_results']=$html;

		if(!empty($massactionClass->report)){
			if(JRequest::getCmd('tmpl') == 'component'){
				echo hikashop_display($massactionClass->report,'info');
				$js = "setTimeout('redirect()',2000); function redirect(){window.top.location.href = 'index.php?option=com_hikashop&ctrl=massaction'; }";
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
				return;
			}else{
				$app = JFactory::getApplication();
				foreach($massactionClass->report as $oneReport){
					$app->enqueueMessage($oneReport);
				}
			}
		}
		return $this->edit();
	}

	function copy(){
		$actions = JRequest::getVar( 'cid', array(), '', 'array' );
		$result = true;
		if(!empty($actions)){
			$actionsClass = hikashop_get('class.massaction');
			foreach($actions as $action){
				$data = $actionsClass->get($action);
				if($data){
					unset($data->massaction_id);
					if(!$actionsClass->save($data)){
						$result=false;
					}
				}
			}
		}
		if($result){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
		}
		return $this->listing();
	}

	function countresults(){
		$massActionClass = hikashop_get('class.massaction'); //load the hikaQuery class
		$num = JRequest::getInt('num');
		$table = JRequest::getWord('table');
		$filters = JRequest::getVar('filter');
		$query = new HikaShopQuery();
		$query->select = 'hk_'.$table.'.*';
		$query->from = '#__hikashop_'.$table.' as hk_'.$table;
		if(empty($filters[$table]['type'][$num])) exit;
		$currentType = $filters[$table]['type'][$num];
		if(empty($filters[$table][$num][$currentType])) exit;
		$currentFilterData = $filters[$table][$num][$currentType];
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$messages = $dispatcher->trigger('onCount'.ucfirst($table).'MassFilter'.$currentType,array(&$query,$currentFilterData,$num));

		echo implode(' | ',$messages);
		exit;
	}

	function results(){
		JRequest::setVar( 'layout', 'results' );
		return parent::display();
	}

	function displayassociate(){
		$path = JRequest::getVar('csv_path');
		$num = JRequest::getVar('current_filter');
		$cid = JRequest::getVar('cid','');

		if(!JPath::check($path)) {
			echo JText::_('FILE_NOT_FOUND');
			return false;
		}

		if(!empty($cid)){

			$massactionClass = hikashop_get('class.massaction');
			$params = $massactionClass->get($cid);
		}

		if(!empty($params->massaction_filters)){
			if(!is_array($params->massaction_filters))
				$filters = unserialize($params->massaction_filters);
			else
				$filters = $params->massaction_filters;
		}else{
			$filters = array();
		}


		$element = array();
		$element['path'] = $path;
		if(isset($filters[0]->data['change'])){
			$changes = $filters[0]->data['change'];
			$element['change'] = $changes;
		}

		$massactionClass = hikashop_get('class.massaction');
		$data = $massactionClass->getFromFile($element, true);

		switch($data->error){
			case 'not_found':
				echo JText::_('FILE_NOT_FOUND');
				break;
			case 'fail_open':
				echo JText::_('HIKA_CANNOT_OPEN');
				break;
			case 'empty':
				echo JText::_('HIKA_EMPTY_FILE');
				break;
			case 'wrong_columns':
				if(isset($data->wrongColumns)){
					echo '<fieldset><legend>'.JText::_( 'SELECT_CORRESPONDING_COLUMNS' ).'</legend>';
					foreach($data->wrongColumns as $wrongColumn){
						$changeColumn = $wrongColumn.': ';
						$changeColumn .= '<select class="chzn-done" id="productfilter'.$num.'csvImport_pathType" name="filter[product]['.$num.'][csvImport][change]['.$wrongColumn.']">';
						$changeColumn .= '<option value="delete">'.JText::_('REMOVE').'</option>';
						foreach($data->validColumns as $validColumn){
							if(isset($changes[$wrongColumn]) && $changes[$wrongColumn] == $validColumn){
								$selected = ' selected="selected" ';
							}else{
								$selected = '';
							}
							$changeColumn .= '<option value="'.$validColumn.'" '.$selected.'>'.$validColumn.'</option>';
						}
						$changeColumn .= '</select><br/>';
						echo $changeColumn;
					}
					echo '</fieldset>';
				}
				break;
			default:
				echo JText::_('HIKA_VALID_FILE');
				break;
		}
	}
}
