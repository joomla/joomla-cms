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
class hikashopViewClass extends hikashopClass{

	function saveForm(){
		$id = JRequest::getString('id');
		$element = $this->get($id);

		if(!$element) return false;

		$duplicate = trim(JRequest::getString('duplicate', '', 'post'));
		if(!empty($duplicate) && substr($duplicate,0,1) != '.' && substr($duplicate,0,1) != '/' && substr($duplicate,0,1) != '\\' ) {
			$name = explode('_', $element->filename, 2);
			$override = substr($element->override, 0, -strlen($name[1])) . $duplicate.'.php';
			if($element->override != $override) {
				if(!file_exists($override)) {
					$element->override = $override;
				} else {
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('CANT_DUPLICATE_ON_EXISTING_FILE'));
					return false;
				}
			}
		}

		$element->content = JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$result = $this->save($element);
		return $result;
	}

	function save(&$element){
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		if(!JPath::check($element->override) && HIKASHOP_J25)
			return false;

		jimport('joomla.filesystem.file');
		$result = JFile::write($element->override, $element->content);

		if (!$result){
			if(!$ftp['enabled'] && !JPath::setPermissions($element->override, '0755')) {
				JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('FILE_NOT_WRITABLE',$element->override));
			}
			$result = JFile::write($element->override, $element->content);
			if (!$ftp['enabled']) {
				JPath::setPermissions($element->override, '0555');
			}
		}

		return $result;
	}

	function delete(&$id){
		$element = $this->get(reset($id));
		if(!$element){
			return false;
		}
		jimport('joomla.filesystem.file');

		if(!JFile::exists($element->override)){
			return true;
		}
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');
		if (!$ftp['enabled'] && !JPath::setPermissions($element->override, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('FILE_NOT_WRITABLE',$element->override));
		}

		$result = JFile::delete($element->override);
		return $result;
	}

	function get($id,$default=null){
		$parts = explode('|',$id);
		if(count($parts)!=6){
			return false;
		}
		$obj = new stdClass();
		$obj->id = $id;
		$obj->client_id = (int)$parts[0];
		$obj->template = $parts[1];
		$obj->type = $parts[2];
		$obj->type_name = $parts[3];
		$obj->view = $parts[4];
		$obj->filename = $parts[5];

		if($obj->type == 'plugin') {
			$obj->folder = rtrim(JPATH_PLUGINS,DS).DS.$obj->type_name.DS;
		} else {
			if($obj->type_name == HIKASHOP_COMPONENT) {
				switch($obj->client_id){
					case 0:
						$view = HIKASHOP_FRONT.'views'.DS;
						break;
					case 1:
						$view = HIKASHOP_BACK.'views'.DS;
						break;
					default:
						return false;
				}
			} else {
				$view = '';
				JPluginHelper::importPlugin('hikashop');
				$dispatcher = JDispatcher::getInstance();
				$pluginViews = array();
				$dispatcher->trigger('onViewsListingFilter', array(&$pluginViews, $obj->client_id));
				if(!empty($pluginViews)) {
					foreach($pluginViews as $pluginView) {
						if($pluginView['client_id'] == $obj->client_id && $pluginView['component'] == $obj->type_name) {
							$view = $pluginView['view'];
							$obj->type_pretty_name = $pluginView['name'];
							break;
						}
					}
				}
				if(empty($view)) {
					return false;
				}
			}
			$obj->folder = $view.$obj->view.DS.'tmpl'.DS;
		}
		$obj->path = $obj->folder.$obj->filename;

		if(!JPath::check($obj->path) && HIKASHOP_J25)
			return false;

		$obj->file = substr($obj->filename,0,strlen($obj->filename)-4);
		$client	= JApplicationHelper::getClientInfo($obj->client_id);
		$tBaseDir = $client->path.DS.'templates';
		$templateFolder = $tBaseDir.DS.$obj->template.DS;
		$obj->override = $templateFolder.'html'.DS.$obj->type_name.DS;
		if($obj->type=='component') {
			$obj->override .= $obj->view.DS;
		}
		$obj->override .= $obj->filename;
		$obj->overriden = false;
		if(file_exists($obj->override)) {
			$obj->overriden = true;
			$obj->edit = $obj->override;
		} else {
			$obj->edit = $obj->path;
		}
		return $obj;
	}
}
