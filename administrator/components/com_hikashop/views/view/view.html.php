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
class ViewViewView extends hikashopView{
	var $type = '';
	var $ctrl= 'view';
	var $nameListing = 'VIEWS';
	var $nameForm = 'VIEWS';
	var $icon = 'view';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function getName(){
		return 'view';
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->client_id = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.client_id', 'client_id', 2 , 'int');
		$pageInfo->filter->template = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.template', 'template', '' , 'string');
		$pageInfo->filter->component = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.component', 'component', '' , 'string');
		$pageInfo->filter->viewType = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.viewType', 'viewType', '' , 'string');
		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.limit', 'limit', $app->getCfg('list_limit'), 'int');
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		if(JRequest::getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.'.search', 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.'.filter_order', 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.'.filter_order_Dir', 'filter_order_Dir',	'desc',	'word' );

		$views = array();
		switch($pageInfo->filter->client_id){
			case 0:
				$views[0] = HIKASHOP_FRONT.'views'.DS;
				break;
			case 1:
				$views[1] = HIKASHOP_BACK.'views'.DS;
				break;
			default:
				$views[0] = HIKASHOP_FRONT.'views'.DS;
				$views[1] = HIKASHOP_BACK.'views'.DS;
				break;
		}

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$pluginViews = array();
		$dispatcher->trigger('onViewsListingFilter', array(&$pluginViews, $pageInfo->filter->client_id));
		if(!empty($pluginViews)) {
			$i = 2;
			foreach($pluginViews as $pluginView) {
				$views[$i++] = $pluginView;
			}
		}
		$this->assignRef('pluginViews', $pluginViews);

		jimport('joomla.filesystem.folder');
		if(version_compare(JVERSION,'1.6','<')){
			require_once (rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_templates'.DS.'helpers'.DS.'template.php');
		}
		$templates = array();
		$templateValues = array();

		foreach($views as $client_id => $view){
			$component_name = '';
			$component = HIKASHOP_COMPONENT;
			if(is_array($view)) {
				$client_id = $view['client_id'];
				$component_name = $view['name'];
				$component = $view['component'];
				$view = $view['view'];
			}

			if(!empty($pageInfo->filter->component) && $pageInfo->filter->component != $component)
				continue;

			$folders = JFolder::folders($view);
			if(empty($folders))
				continue;

			$clientTemplates = array();
			foreach($folders as $folder){
				if(JFolder::exists($view.$folder.DS.'tmpl')){
					$files = JFolder::files($view.$folder.DS.'tmpl');
					if(!empty($files)){
						foreach($files as $file){
							if(substr($file,-4)=='.php'){
								$obj = new stdClass();
								$obj->path = $view.$folder.DS.'tmpl'.DS.$file;
								$obj->filename = $file;
								$obj->folder = $view.$folder.DS.'tmpl'.DS;
								$obj->client_id = $client_id;
								$obj->view = $folder;
								$obj->type = 'component';
								$obj->type_name = $component;
								$obj->file = substr($file,0,strlen($file)-4);
								$clientTemplates[]=$obj;
							}
						}
					}
				}
			}

			if($client_id==0 && $component == HIKASHOP_COMPONENT){
				$plugins_folder = rtrim(JPATH_PLUGINS,DS).DS.'hikashoppayment';
				if(Jfolder::exists($plugins_folder)){
					$files = Jfolder::files($plugins_folder);
					foreach($files as $file){
						if(preg_match('#^.*_(?!configuration).*\.php$#',$file)){
							$obj = new stdClass();
							$obj->path = $plugins_folder.DS.$file;
							$obj->filename = $file;
							$obj->folder = $plugins_folder;
							$obj->client_id = $client_id;
							$obj->type = 'plugin';
							$obj->view = '';
							$obj->type_name = 'hikashoppayment';
							$obj->file = substr($file,0,strlen($file)-4);
							$clientTemplates[]=$obj;
						}
					}
				}
			}

			if(!empty($clientTemplates)){
				$client	= JApplicationHelper::getClientInfo($client_id);
				$tBaseDir = $client->path.DS.'templates';
				if(version_compare(JVERSION,'1.6','<')){
					$joomlaTemplates = TemplatesHelper::parseXMLTemplateFiles($tBaseDir);
				}else{
					$query = 'SELECT * FROM '.hikashop_table('extensions',false).' WHERE type=\'template\' AND client_id='.(int)$client_id;
					$db = JFactory::getDBO();
					$db->setQuery($query);
					$joomlaTemplates = $db->loadObjectList();
					foreach($joomlaTemplates as $k => $v){
						$joomlaTemplates[$k]->assigned = $joomlaTemplates[$k]->protected;
						$joomlaTemplates[$k]->published = $joomlaTemplates[$k]->enabled;
						$joomlaTemplates[$k]->directory = $joomlaTemplates[$k]->element;
					}

				}
				for($i = 0; $i < count($joomlaTemplates); $i++)  {
					if(version_compare(JVERSION,'1.6','<')){
						$joomlaTemplates[$i]->assigned = TemplatesHelper::isTemplateAssigned($joomlaTemplates[$i]->directory);
						$joomlaTemplates[$i]->published = TemplatesHelper::isTemplateDefault($joomlaTemplates[$i]->directory, $client->id);
					}
					if($joomlaTemplates[$i]->published || $joomlaTemplates[$i]->assigned){
						if(!empty($pageInfo->filter->template) && $joomlaTemplates[$i]->directory!=$pageInfo->filter->template){
							continue;
						}

						$templateValues[$joomlaTemplates[$i]->directory]=$joomlaTemplates[$i]->directory;

						$templateFolder = $tBaseDir.DS.$joomlaTemplates[$i]->directory.DS;
						foreach($clientTemplates as $template){
							$templatePerJoomlaTemplate = clone($template);
							$templatePerJoomlaTemplate->template = $joomlaTemplates[$i]->directory;
							$templatePerJoomlaTemplate->component = $component_name;
							$templatePerJoomlaTemplate->override = $templateFolder.'html'.DS.$template->type_name.DS;
							if($template->type=='component'){
								$templatePerJoomlaTemplate->override .= $template->view.DS;
							}
							$templatePerJoomlaTemplate->override .= $template->filename;
							$templatePerJoomlaTemplate->overriden=false;

							if(file_exists($templatePerJoomlaTemplate->override)){
								$templatePerJoomlaTemplate->overriden=true;
							}
							$templatePerJoomlaTemplate->id = $templatePerJoomlaTemplate->client_id.'|'.$templatePerJoomlaTemplate->template .'|'. $templatePerJoomlaTemplate->type.'|'. $templatePerJoomlaTemplate->type_name.'|'. $templatePerJoomlaTemplate->view.'|'.$templatePerJoomlaTemplate->filename;
							$key = $templatePerJoomlaTemplate->client_id.'|'.$templatePerJoomlaTemplate->template .'|'.$templatePerJoomlaTemplate->type_name.'|'. $templatePerJoomlaTemplate->view.'|'.$templatePerJoomlaTemplate->filename;

							if(!empty($pageInfo->filter->viewType) && $templatePerJoomlaTemplate->view!=$pageInfo->filter->viewType){
								continue;
							}

							$templates[$key]=$templatePerJoomlaTemplate;
						}

						if(JFolder::exists($templateFolder.'html'.DS.$component.DS)){
							$folders = JFolder::folders($templateFolder.'html'.DS.$component.DS);
							if(!empty($folders)){
								foreach($folders as $folder){

									$files = JFolder::files($templateFolder.'html'.DS.$component.DS.$folder);
									if(empty($files))
										continue;
									foreach($files as $file) {
										if(substr($file,-4)!='.php')
											continue;

										$filename = $templateFolder.'html'.DS.$component.DS.$folder.DS.$file;
										$found = false;
										foreach($templates as $tpl) {
											if($tpl->override == $filename) {
												$found = true;
												break;
											}
										}
										if(!$found) {
											$obj = new stdClass();
											$obj->path = $view.$folder.DS.'tmpl'.DS.$file;
											$obj->filename = $file;
											$obj->folder = $view.$folder.DS.'tmpl'.DS;
											$obj->client_id = $client_id;
											$obj->view = $folder;
											$obj->template = $joomlaTemplates[$i]->directory;
											$obj->type = 'component';
											$obj->type_name = $component;
											$obj->file = substr($file,0,strlen($file)-4);
											$obj->override = $filename;
											$obj->overriden = true;
											$obj->id = $obj->client_id.'|'.$obj->template.'|'.$obj->type.'|'.$obj->type_name.'|'.$obj->view.'|'.$obj->filename;
											$key = $obj->client_id.'|'.$obj->template.'|'.$obj->view.'|'.$obj->filename;
											$templates[$key]=$obj;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		ksort($templates);
		$searchMap = array('filename','view','template');
		if(!empty($pageInfo->search)){

			$unset = array();
			foreach($templates as $k => $template){
				$found = false;
				foreach($searchMap as $field){
					if(strpos($template->$field,$pageInfo->search)!==false){
						$found=true;
					}
				}
				if(!$found){
					$unset[]=$k;
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($templates[$u]);
				}
			}
			$templates = hikashop_search($pageInfo->search,$templates,'id');
		}

		$viewTypes= array('0' => JHTML::_('select.option', 0, JText::_('ALL_VIEWS')));
		foreach($templates as $temp){
			if(!isset($viewTypes[strip_tags($temp->view)]) && !empty($temp->view)){
				$viewTypes[strip_tags($temp->view)] = JHTML::_('select.option', strip_tags($temp->view), strip_tags($temp->view));
			}
		}

		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = count($templates);
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();

		$templates = array_slice($templates, $this->pagination->limitstart, $this->pagination->limit);
		$pageInfo->elements->page = count($templates);

		$this->assignRef('viewTypes',$viewTypes);
		$this->assignRef('rows',$templates);
		$this->assignRef('templateValues',$templateValues);
		$viewType = hikashop_get('type.view');
		$this->assignRef('viewType',$viewType);
		$templateType = hikashop_get('type.template');
		$this->assignRef('templateType',$templateType);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_view_manage','all'));
		$this->assignRef('manage',$manage);
		$delete = hikashop_isAllowed($config->get('acl_view_delete','all'));
		$this->assignRef('delete',$delete);
		$this->toolbar = array(
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		jimport('joomla.client.helper');
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');
		$this->assignRef('ftp',$ftp);
	}

	function form(){
		$id = JRequest::getString('id','');
		$viewClass = hikashop_get('class.view');
		$obj = $viewClass->get($id);

		if($obj){
			jimport('joomla.filesystem.file');
			$obj->content = htmlspecialchars(JFile::read($obj->edit), ENT_COMPAT, 'UTF-8');
		}

		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task=edit&id='.$id);

		jimport('joomla.client.helper');
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');
		$this->assignRef('ftp',$ftp);
		$this->assignRef('element',$obj);
		$editor = hikashop_get('helper.editor');
		$this->assignRef('editor',$editor);

	}
}
