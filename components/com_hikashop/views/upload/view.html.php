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
class uploadViewupload extends hikashopView {
	const ctrl = 'upload';
	const name = 'HIKA_UPLOAD';
	const icon = 'upload';

	public function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		parent::display($tpl);
	}

	public function sendfile() {
		$uploadConfig = JRequest::getVar('uploadConfig', null);
		if(empty($uploadConfig) || !is_array($uploadConfig))
			return false;

		$this->assignRef('uploadConfig', $uploadConfig);
		$uploader = JRequest::getCmd('uploader', '');
		$this->assignRef('uploader', $uploader);
		$field = JRequest::getCmd('field', '');
		$this->assignRef('field', $field);
	}

	public function galleryimage() {
		hikashop_loadJslib('otree');

		$app = JFactory::getApplication();
		$config = hikashop_config();
		$this->assignRef('config', $config);

		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName().'.gallery';

		$uploadConfig = JRequest::getVar('uploadConfig', null);
		if(empty($uploadConfig) || !is_array($uploadConfig))
			return false;

		$this->assignRef('uploadConfig', $uploadConfig);
		$uploader = JRequest::getCmd('uploader', '');
		$this->assignRef('uploader', $uploader);
		$field = JRequest::getCmd('field', '');
		$this->assignRef('field', $field);

		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$basePath = JPATH_ROOT.DS.$uploadFolder.DS;

		if(!empty($uploadConfig['options']['upload_dir']))
			$basePath = rtrim(JPATH_ROOT,DS).DS.str_replace(array('\\','/'), DS, $uploadConfig['options']['upload_dir']);

		$pageInfo = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', 20, 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.'.search', 'search', '', 'string');

		$this->assignRef('pageInfo', $pageInfo);

		jimport('joomla.filesystem.folder');
		if(!JFolder::exists($basePath))
			JFolder::create($basePath);

		$subFolder = $basePath;
		if(!empty($uploadConfig['options']['sub_folder']))
			$subFolder .= rtrim(str_replace(array('\\','/'), DS, $uploadConfig['options']['sub_folder']), DS).DS;

		$galleryHelper = hikashop_get('helper.gallery');
		$galleryHelper->setRoot($subFolder);
		$this->assignRef('galleryHelper', $galleryHelper);

		$folder = str_replace(array('|', '\/'), array(DS, DS), JRequest::getString('folder', ''));
		if(!empty($uploadConfig['options']['sub_folder']) && substr($folder, 0, strlen($uploadConfig['options']['sub_folder'])) == $uploadConfig['options']['sub_folder']) {
			$folder = substr($folder, strlen($uploadConfig['options']['sub_folder']));
			if($folder === false)
				$folder = '';
		}

		$destFolder = rtrim($folder, '/\\');
		if(!$galleryHelper->validatePath($destFolder))
			$destFolder = '';
		if(!empty($destFolder)) $destFolder .= '/';

		$treeContent = $galleryHelper->getTreeList(null, $destFolder);
		$this->assignRef('treeContent', $treeContent);

		if($subFolder != $basePath)
			$galleryHelper->setRoot($basePath);

		$destFolder = '';
		if(!empty($uploadConfig['options']['sub_folder']))
			$destFolder .= rtrim(str_replace(array('\\','/'),DS,$uploadConfig['options']['sub_folder']), DS).DS;
		$destFolder .= rtrim($folder, '/\\');
		if(!$galleryHelper->validatePath($destFolder))
			$destFolder = '';
		if(!empty($destFolder)) $destFolder .= '/';
		$this->assignRef('destFolder', $destFolder);

		$galleryOptions = array(
			'filter' => '.*' . str_replace(array('.','?','*','$','^'), array('\.','\?','\*','$','\^'), $pageInfo->search) . '.*',
			'offset' => $pageInfo->limit->start,
			'length' => $pageInfo->limit->value
		);
		$this->assignRef('galleryOptions', $galleryOptions);

		$dirContent = $galleryHelper->getDirContent($destFolder, $galleryOptions);
		$this->assignRef('dirContent', $dirContent);

		$subFolder = '';
		if(!empty($uploadConfig['options']['sub_folder']))
			$subFolder = rtrim(str_replace(array('\\','/'),DS,$uploadConfig['options']['sub_folder']), DS).DS;
		$this->assignRef('subFolder', $subFolder);

		jimport('joomla.html.pagination');
		$pagination = new JPagination( $galleryHelper->filecount, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->assignRef('pagination', $pagination);
	}

	public function image_entry() {
		$imageHelper = hikashop_get('helper.image');
		$this->assignRef('imageHelper', $imageHelper);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);
	}
}
