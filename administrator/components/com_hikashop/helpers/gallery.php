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
class hikashopGalleryHelper {

	var $extensions = array('jpg', 'jpeg', 'png', 'gif', 'svg');
	var $root = '';
	var $urlRoot = '';
	var $dirs = array();
	var $dirlistdepth = 3;
	var $hideFolders = array('safe', 'thumbnail', 'thumbnails', 'thumbnail_*');

	function __construct() {
		$config = hikashop_config();
		$uploadFolderConfig = rtrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))), DS) . DS;
		$this->setRoot($uploadFolderConfig);
	}

	function setRoot($dir) {
		if(strpos($dir,JPATH_ROOT)!==false){
			$dir = str_replace(JPATH_ROOT,'',$dir);
		}
		$dir = ltrim($dir,'/');
		if(strpos($dir, '..') !== false)
			return false;
		$this->root = JPath::clean(JPATH_ROOT.DS.$dir);
		$app = JFactory::getApplication();
		$dir = str_replace(DS, '/', $dir);
		if($app->isAdmin())
			$this->urlRoot = '../' . $dir;
		else
			$this->urlRoot = rtrim(JURI::base(true), '/') . '/' . $dir;
	}

	function validatePath($path) {
		if(empty($path))
			return true;

		jimport('joomla.filesystem.folder');
		if(!JFolder::exists($this->root . $path))
			return false;
		return true;
	}

	function getTreeList($folder = '', $openTo = '') {
		$id = 'hikashopGallery';

		if(strpos($folder, '..') !== false)
			return false;

		jimport('joomla.filesystem.folder');
		if(!JFolder::exists($this->root . $folder))
			return false;

		$oToScript = '';
		if(!empty($openTo)) {
			$oToScript = '
var otoNode = '.$id.'.find("/'. trim($openTo, '/').'");
if(otoNode) { '.$id.'.oTo(otoNode); '.$id.'.sel(otoNode); }';
		}

		$ret = '<div id="'.$id.'_otree" class="oTree"></div>
<script type="text/javascript">
var data_'.$id.' = ['.$this->_getTreeChildList(null, '/').'];
'.$id.' = new oTree("'.$id.'",{rootImg:"'.HIKASHOP_IMAGES.'otree/",showLoading:true,useSelection:true},null,data_'.$id.',true);
'.$id.'.render(true);'.$oToScript.'
</script>';

		return $ret;
	}

	function _getTreeChildList($parent, $folder, $depth = 0) {
		$ret = '';
		if(empty($parent))
			$parent = '';
		if(strpos($folder, '..') !== false)
			return false;
		if(!JFolder::exists($this->root . $parent . DS . $folder))
			return false;

		if($depth > $this->dirlistdepth)
			return $ret;

		$status = 1;
		$jsName = str_replace('"','\"', $folder);
		$jsValue = str_replace('"','\"', $parent . '/' . $folder);
		if(empty($parent))
			$jsValue = str_replace('"','\"', $folder);
		if($parent == '/')
			$jsValue = str_replace('"','\"', '/' . $folder);

		if(empty($parent) && $folder == '/') {
			$jsName = str_replace('"', '\"', JText::_('HIKASHOP_IMAGE_ROOTDIR'));
			$jsValue = '/';
			$status = 2;
		}

		if($depth == $this->dirlistdepth) {
			$status = 3;
			$ret = '{status:'.$status.',name:"'.$jsName.'",value:"'.$jsValue.'"}';
			return $ret;
		}

		$data = array();
		$folders = JFolder::folders($this->root . $parent . DS . $folder);
		if(!empty($folders)) {
			$newParent = $parent . '/' . $folder;
			if(empty($parent))
				$newParent = $folder;
			if($parent == '/')
				$newParent = '/' . $folder;
			foreach($folders as $f) {
				$hide = false;
				foreach($this->hideFolders as $h) {
					if($f == $h) $hide = true;
					if(substr($h, -1) == '*' && substr($f, 0, strlen($h) - 1) == substr($h, 0, -1)) $hide = true;
					if($hide) break;
				}
				if($hide)
					continue;

				$r = $this->_getTreeChildList($newParent, $f);
				if(!empty($r))
					$data[]	= $r;
			}
		}

		if(empty($data)) {
			$status = 4;
			$data = '';
		} else {
			$data = ',data:['.implode(',',$data).']';
		}

		$ret = '{status:'.$status.',name:"'.$jsName.'",value:"'.$jsValue.'"'.$data.'}';
		return $ret;
	}

	function getDirContent($folder = '', $options = array()) {
		$ret = array();
		$this->filecount = 0;
		jimport('joomla.filesystem.folder');
		if(strpos($folder, '..') !== false)
			return false;
		if(!JFolder::exists($this->root . $folder))
			return false;

		$workingFolder = $this->root . $folder;
		$externFolder = $this->urlRoot . $folder;

		$workingFolder = rtrim(JPath::clean($workingFolder), DS) . DS;

		if(!empty($options['filter']))
			$files = JFolder::files($workingFolder, $options['filter']);
		else
			$files = JFolder::files($workingFolder);
		if(empty($files))
			return $ret;

		$u = array('B','KB','MB','GB','TB','PB');
		$sizeOptions = array(100, 100);
		$thumbnailsOptions = array(
			'forcesize' => true,
			'grayscale' => false,
			'scale' => 'outside',
		);
		$imageHelper = hikashop_get('helper.image');
		$imageHelper->thumbnail = 1;

		natcasesort($files);
		$images = array();
		foreach($files as $file) {
			if(strrpos($file, '.') === false)
				continue;

			$ext = strtolower(substr($file, strrpos($file, '.') + 1));
			if(!in_array($ext, $this->extensions))
				continue;

			$images[] = $file;
		}
		unset($files);

		$this->filecount = count($images);
		$offset = 0;
		$length = 30;

		if(isset($options['length']) && (int)$options['length'] > 0)
			$length = (int)$options['length'];
		if(isset($options['offset']))
			$offset = (int)$options['offset'];
		if($offset == 0 && isset($options['page']))
			$offset = (int)$options['page'] * $length;
		if($offset >= $this->filecount)
			$offset = 0;

		$images = array_slice($images, $offset, $length);

		foreach($images as $file) {
			$image = new stdClass();
			$image->filename = $file;
			$image->path = $folder . $file;
			$image->fullpath = $workingFolder . $file;
			$image->baseurl = $externFolder;
			$image->folder = $folder;
			$image->rawsize = @filesize($workingFolder . $file);
			$image->size = sprintf('%01.2f', @round($image->rawsize/pow(1024,($i=floor(log($image->rawsize,1024)))),2)).' '.$u[$i];
			list($image->width, $image->height) = getimagesize($image->fullpath);

			$image->thumbnail = $imageHelper->getThumbnail(ltrim($image->path, '/\\'), $sizeOptions, $thumbnailsOptions, $this->root);
			$image->thumbnail->url = $this->urlRoot . str_replace('\\', '/', $image->thumbnail->path);

			$ret[] = $image;
		}

		return $ret;
	}
}
