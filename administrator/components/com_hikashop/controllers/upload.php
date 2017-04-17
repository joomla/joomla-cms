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

class uploadController extends hikashopController {

	var $display = array('upload','image','galleryimage','');
	var $modify_views = array('addimage','galleryselect');
	var $add = array();
	var $modify = array('upload');
	var $delete = array();

	protected $controller = null;

	public function __construct($config = array())	{
		parent::__construct($config);
		$this->registerDefaultTask('galleryimage');

		$controllerName = JRequest::getCmd('uploader', '');
		if(!empty($controllerName)) {
			$this->controller = hikashop_get('controller.'.$controllerName, array(), true);
			if(!method_exists($this->controller, 'getUploadSetting'))
				$this->controller = null;
		}
	}

	public function image() {
		$upload_key = JRequest::getVar('field', '');
		if(empty($this->controller))
			return false;

		$uploadConfig = $this->controller->getUploadSetting($upload_key, 'image');
		if($uploadConfig === false)
			return false;

		if(!empty($uploadConfig['type']) && $uploadConfig['type'] != 'image')
			return false;

		JRequest::setVar('layout', 'sendfile');
		JRequest::setVar('uploadConfig', $uploadConfig);
		return parent::display();
	}

	public function galleryimage() {
		$upload_key = JRequest::getVar('field', '');
		if(empty($this->controller))
			return false;

		$uploadConfig = $this->controller->getUploadSetting($upload_key, 'galleryimage');
		if($uploadConfig === false)
			return false;

		if(!empty($uploadConfig['type']) && $uploadConfig['type'] != 'image')
			return false;

		JRequest::setVar('layout', 'galleryimage');
		JRequest::setVar('uploadConfig', $uploadConfig);
		return parent::display();
	}

	public function addImage() {
		$upload_key = JRequest::getVar('field', '');
		if(empty($this->controller))
			return false;

		$uploadConfig = $this->controller->getUploadSetting($upload_key, 'addimage');
		if($uploadConfig === false)
			return false;

		if(!empty($uploadConfig['type']) && $uploadConfig['type'] != 'image')
			return false;

		$layout = 'upload';
		if(!empty($uploadConfig['layout']))
			$layout = $uploadConfig['layout'];
		$viewName = '';
		if(!empty($uploadConfig['view']))
			$viewName = $uploadConfig['view'];
		$type = 'image';
		if(!empty($uploadConfig['type']))
			$type = $uploadConfig['type'];
		if(empty($viewName))
			$viewName = ($type == 'image') ? 'image_entry' : 'file_entry';

		$extra_data = array();
		if(!empty($uploadConfig['extra']))
			$extra_data = $uploadConfig['extra'];

		if(empty($extra_data['field']))
			$extra_data['field'] = $upload_key;

		$options = array();
		if(!empty($uploadConfig['options']))
			$options = $uploadConfig['options'];

		$this->processUploadOption($options, $type);
		if(empty($options) || empty($options['upload_dir']))
			return false;

		$uploadHelper = hikashop_get('helper.upload');
		$ret = $uploadHelper->processFallback($options);

		$output = '[]';
		if($ret !== false && empty($ret->error)) {
			$helperImage = null;
			if($type == 'image') {
				$helperImage = hikashop_get('helper.image');
			}

			foreach($ret as &$r) {
				if(!empty($r->error))
					continue;

				$file = new stdClass();
				$file->file_description = '';
				$file->file_name = $r->name;
				$file->file_type = $type;
				$file->file_path = $r->name;
				$file->file_url = $options['upload_url'];

				foreach($extra_data as $k => $v) {
					$file->$k = $v;
				}

				if(strpos($file->file_name, '.') !== false) {
					$file->file_name = substr($file->file_name, 0, strrpos($file->file_name, '.'));
				}

				$r->html = '';
				$js = '';

				if($type == 'image') {
					if(!empty($options['processing']) && $options['processing'] == 'resize')
						$helperImage->resizeImage($file->file_path);

					$img = $helperImage->getThumbnail($file->file_path, array(100, 100), array('default' => true));
					$r->thumbnail_url = $img->url;

					$params = new stdClass();
					$params->file_path = $file->file_path;
					$params->file_name = $file->file_name;
					$params->file_url = $file->file_url;
				} else {
					$params = new stdClass();
					$params->file_name = $file->file_name;
					$params->file_path = $file->file_path;
					$params->file_url = $file->file_url;
					$params->file_limit = -1;
					$params->file_size = @filesize($options['upload_dir'] . $file->file_name);
				}

				foreach($extra_data as $k => $v) {
					$params->$k = $v;
				}

				$r->params = $params;
				$this->controller->manageUpload($upload_key, $r, $uploadConfig, 'addimage');

				if(empty($r->html))
					$r->html = hikashop_getLayout($layout, $viewName, $params, $js);

				$out[] = $r->html;

				unset($r->path);
				unset($r->params);
				unset($r);
			}

			if(!empty($out))
				$output = json_encode($out);
			unset($out);
			unset($ret);
		}

		$js = 'window.hikashop.ready(function(){window.parent.hikashop.submitBox({images:'.$output.'});});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		return true;
	}

	public function galleryselect() {
		$upload_key = JRequest::getVar('field', '');
		if(empty($this->controller))
			return false;

		$uploadConfig = $this->controller->getUploadSetting($upload_key, 'galleryselect');
		if($uploadConfig === false)
			return false;

		if(!empty($uploadConfig['type']) && $uploadConfig['type'] != 'image')
			return false;

		$layout = 'upload';
		if(!empty($uploadConfig['layout']))
			$layout = $uploadConfig['layout'];
		$viewName = '';
		if(!empty($uploadConfig['view']))
			$viewName = $uploadConfig['view'];
		$type = 'image';
		if(!empty($uploadConfig['type']))
			$type = $uploadConfig['type'];
		if(empty($viewName))
			$viewName = ($type == 'image') ? 'image_entry' : 'file_entry';

		$extra_data = array();
		if(!empty($uploadConfig['extra']))
			$extra_data = $uploadConfig['extra'];

		if(empty($extra_data['field']))
			$extra_data['field'] = $upload_key;

		$options = array();
		if(!empty($uploadConfig['options']))
			$options = $uploadConfig['options'];

		$this->processUploadOption($options, $type);
		if(empty($options) || empty($options['upload_dir']))
			return false;

		$filesData = JRequest::getVar('files', array(), '', 'array');

		$output = '[]';
		if(!empty($filesData)) {
			$helperImage = hikashop_get('helper.image');
			$ret = array();
			foreach($filesData as $filename) {
				$r = new stdClass();
				$r->name = $filename;
				$r->url = $options['upload_url'].rawurlencode($filename);
				$r->path = $options['upload_dir'].$filename;
				$r->type = $type;
				$r->size = filesize($r->path);

				$params = new stdClass();
				$params->file_path = $filename;
				$params->file_name = $filename;
				$params->file_url = $r->url;

				foreach($extra_data as $k => $v) {
					$params->$k = $v;
				}

				$r->params = $params;
				$this->controller->manageUpload($upload_key, $r, $uploadConfig, 'galleryselect');

				if(empty($r->html))
					$r->html = hikashop_getLayout($layout, $viewName, $r->params, $js);

				unset($r->params);
				$ret[] = $r;
				$out[] = $r->html;
				unset($r);
			}
			if(!empty($out))
				$output = json_encode($out);
			unset($out);
			unset($ret);
		}

		$js = 'window.hikashop.ready(function(){window.parent.hikashop.submitBox({images:'.$output.'});});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		return true;
	}

	public function upload() {
		JRequest::checkToken() || die('Invalid Token');

		$config = hikashop_config();
		$upload_key = JRequest::getVar('field', '');
		if(empty($this->controller))
			exit;

		$uploadConfig = $this->controller->getUploadSetting($upload_key, 'upload');
		if($uploadConfig === false)
			exit;

		$layout = 'upload';
		if(!empty($uploadConfig['layout']))
			$layout = $uploadConfig['layout'];

		$viewName = '';
		if(!empty($uploadConfig['view']))
			$viewName = $uploadConfig['view'];

		$type = 'image';
		if(!empty($uploadConfig['type']))
			$type = $uploadConfig['type'];

		$options = array();
		if(!empty($uploadConfig['options']))
			$options = $uploadConfig['options'];

		$extra_data = array();
		if(!empty($uploadConfig['extra']))
			$extra_data = $uploadConfig['extra'];

		if(empty($extra_data['field']))
			$extra_data['field'] = $upload_key;

		if(empty($viewName))
			$viewName = ($type == 'image') ? 'image_entry' : 'file_entry';

		$this->processUploadOption($options, $type);
		if(empty($options) || empty($options['upload_dir']))
			return false;

		$uploadHelper = hikashop_get('helper.upload');
		$ret = $uploadHelper->process($options);
		if($ret !== false && empty($ret->error) && empty($ret->partial)) {
			$helperImage = null;
			if($type == 'image') {
				$helperImage = hikashop_get('helper.image');
			}

			$file = new stdClass();
			$file->file_description = '';
			$file->file_name = $ret->name;
			$file->file_type = $type;
			$file->file_path = $ret->name;
			$file->file_url = $options['upload_url'];

			foreach($extra_data as $k => $v) {
				$file->$k = $v;
			}

			if(strpos($file->file_name, '.') !== false) {
				$file->file_name = substr($file->file_name, 0, strrpos($file->file_name, '.'));
			}

			$ret->html = '';
			$js = '';

			if($type == 'image') {
				if(!empty($options['processing']) && $options['processing'] == 'resize')
					$helperImage->resizeImage($file->file_path);

				$img = $helperImage->getThumbnail($file->file_path, array(100, 100), array('default' => true));
				$ret->thumbnail_url = $img->url;

				$params = new stdClass();
				$params->file_path = $file->file_path;
				$params->file_name = $file->file_name;
				$params->file_url = $file->file_url;
			} else {
				$params = new stdClass();
				$params->file_name = $file->file_name;
				$params->file_path = $file->file_path;
				$params->file_url = $file->file_url;
				$params->file_limit = -1;
				$params->file_size = @filesize($options['upload_dir'] . $file->file_name);
			}

			foreach($extra_data as $k => $v) {
				$params->$k = $v;
			}

			$ret->params = $params;

			$this->controller->manageUpload($upload_key, $ret, $uploadConfig, 'upload');

			if(empty($ret->html))
				$ret->html = hikashop_getLayout($layout, $viewName, $ret->params, $js);
		}

		unset($ret->path);
		unset($ret->params);

		echo json_encode($ret);
		exit;
	}

	private function processUploadOption(&$options, $type = 'image') {
		$shopConfig = hikashop_config();

		if($type == 'image') {
			if(empty($options['upload_dir']))
				$options['upload_dir'] = $shopConfig->get('uploadfolder');
			if(empty($options['type']))
				$options['type'] = 'image';
		} else {
			if(empty($options['upload_dir']))
				$options['upload_dir'] = $shopConfig->get('uploadsecurefolder');
			if(empty($options['type']))
				$options['type'] = 'file';
		}

		if(empty($options) || empty($options['upload_dir']))
			return false;

		$options['upload_url'] = ltrim(JPath::clean(html_entity_decode($options['upload_dir'])),DS);
		$options['upload_url'] = str_replace(DS,'/',rtrim($options['upload_url'],DS).DS);
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			$options['upload_url'] = '../'.$options['upload_url'];
		} else {
			$options['upload_url'] = rtrim(JURI::base(true),'/').'/'.$options['upload_url'];
		}

		$options['upload_dir'] = rtrim(JPath::clean(html_entity_decode($options['upload_dir'])), DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$options['upload_dir']) && (substr($options['upload_dir'], 0, 1) != '/' || !is_dir($options['upload_dir']))) {
			$options['upload_dir'] = JPath::clean(HIKASHOP_ROOT.DS.trim($options['upload_dir'], DS.' ').DS);
		}
		return true;
	}
}
