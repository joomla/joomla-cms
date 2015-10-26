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
class HikashopUploaderType {

	protected static $init = false;

	public function __construct() {
		$this->popup = hikashop_get('helper.popup');
	}

	protected function initialize() {
		if(self::$init === true)
			return;
		hikashop_loadJslib('opload');
		hikashop_loadJslib('jquery');
		self::$init = true;
	}

	private function processOptions(&$options, $mode = 'image') {
		$t = hikashop_getFormToken();

		if(!empty($options['uploader'])) {
			$params = '';
			if(!empty($options['vars'])) {
				$options['formData'] = $options['vars'];
				$options['formData'][$t] = 1;
				foreach($options['vars'] as $k => $v) {
					$params .= '&' . urlencode($k) . '=' . urlencode($v);
				}
			}

			if(!empty($options['upload'])) {
				if(empty($options['upload_base_url'])) {
					$options['uploadUrls'] = array(
						0 => hikashop_completeLink('upload&task='.$mode.'&uploader='.$options['uploader'][0].'&field='.$options['uploader'][1].$params.'&'.$t.'=1',true),
						1 => hikashop_completeLink('upload&task=upload&upload='.$mode.'&uploader='.$options['uploader'][0].'&field='.$options['uploader'][1], true, false, true)
					);
				} else {
					$options['uploadUrls'] = array(
						0 => JRoute::_($options['upload_base_url'].'&task='.$mode.'&uploader='.$options['uploader'][0].'&field='.$options['uploader'][1].$params.'&'.$t.'=1&tmpl=component'),
						1 => str_replace('&amp;', '&', JRoute::_($options['upload_base_url'].'&task=upload&upload='.$mode.'&uploader='.$options['uploader'][0].'&field='.$options['uploader'][1].'&tmpl=component'))
					);
				}
			}

			if(!empty($options['gallery']) && $mode == 'image') {
				if(empty($options['upload_base_url'])) {
					$options['browseUrl'] = hikashop_completeLink('upload&task=galleryimage&uploader='.$options['uploader'][0].'&field='.$options['uploader'][1].$params,true);
				} else {
					$options['browseUrl'] = JRoute::_($options['upload_base_url'].'&task=galleryimage&uploader='.$options['uploader'][0].'&field='.$options['uploader'][1].$params.'&tmpl=component');
				}
			}
		}
		if(empty($options['classes'])) {
			$options['classes'] = array(
				'mainDiv' => 'hikashop_main_'.$mode.'_div'
			);
		}
		if(!isset($options['classes']['mainDiv']))
			$options['classes']['mainDiv'] = '';
		if(!isset($options['classes']['firstImg']))
			$options['classes']['firstImg'] = 'hikashop_upload_imagethumb_main';
		if(!isset($options['classes']['otherImg']))
			$options['classes']['otherImg'] = 'hikashop_upload_imagethumb_small';
		if(!isset($options['classes']['contentClass']))
			$options['classes']['contentClass'] = '';
		if(empty($options['classes']['btn_upload']))
			$options['classes']['btn_upload'] = 'hika_upload_btn';
		if(empty($options['classes']['btn_add']))
			$options['classes']['btn_add'] = 'hika_add_btn';

		$maxSize = min(hikashop_bytes(ini_get('upload_max_filesize')), hikashop_bytes(ini_get('post_max_size')));
		if(empty($options['maxSize'])) {
			$options['maxSize'] = $maxSize;
		} else {
			$size = (int)$options['maxSize'];
			if((''.$size) != $options['maxSize'])
				$size = hikashop_bytes($options['maxSize']);
			$options['maxSize'] = min($size, $maxSize);
		}

		if(empty($options['uploadUrls']))
			$options['uploadUrls'] = null;
		if(empty($options['browseUrl']))
			$options['browseUrl'] = null;
		if(empty($options['text']))
			$options['text'] = '';
		if(!empty($options['formData']) && !is_string($options['formData'])) {
			$options['formData'] = json_encode($options['formData']);
		} else {
			$options['formData'] = '{\''.$t.'\':1}';
		}
	}

	public function displayImageSingle($id, $content = '', $options = array()) {
		$this->initialize();
		$this->processOptions($options, 'image');
		$js = '';

		$ret = '
<div id="'.$id.'_main" class="hikashop_dropzone">
	<div class="'.$options['classes']['mainDiv'].'">
		<div class="hikashop_uploader_image_add '.$id.'_add">';

		if(!empty($options['tooltip']))
			hikashop_loadJsLib('tooltip');

		if(!empty($options['uploadUrls'])) {
			$opt = '';
			if(!empty($options['tooltip']))
				$opt = ' data-toggle="hk-tooltip" data-title="'.JText::_('HIKA_UPLOAD_IMAGE').'"';

			$ret .= '<span id="'.$id.'-btn" class="opload-btn">'.
				$this->popup->display(
					'<span class="'.$options['classes']['btn_upload'].'"></span>',
					'HIKA_UPLOAD_IMAGE',
					$options['uploadUrls'][0],
					$id.'_uploadpopup',
					750, 460, 'onclick="return window.hkUploaderList[\''.$id.'\'].uploadFile(this);"'.$opt, '', 'link'
				).
				'<input id="'.$id.'" type="file"/></span>';

			$js .= "\r\n" . 'var hkUploader_'.$id.' = new hkUploaderMgr("'.$id.'", {mode: \'single\', url:\''.$options['uploadUrls'][1].'\',formData:'.$options['formData'].', options:{maxSize:'.(int)$options['maxSize'].'}})';
		}

		if(!empty($options['browseUrl'])) {
			$opt = '';
			if(!empty($options['tooltip']))
				$opt = ' data-toggle="hk-tooltip" data-title="'.JText::_('HIKA_ADD_IMAGE').'"';

			$ret .= $this->popup->display(
				'<span class="'.$options['classes']['btn_add'].'"></span>',
				'HIKA_ADD_IMAGE',
				$options['browseUrl'],
				$id.'_addpopup',
				750, 460, 'onclick="return window.hkUploaderList[\''.$id.'\'].browseImage(this);"'.$opt, '', 'link'
			);
		}

		$ret .= '
		</div>
		<div id="'.$id.'_content" class="hikashop_uploader_singleimage_content uploader_data_container">' . $content . '</div>
		<div id="'.$id.'_empty" class="hikashop_uploader_image_empty" style="'.((!empty($content) && empty($options['empty'])) ? 'display:none;' : '') . '">
			<span>'.$options['text'].'</span>
		</div>
	</div>
</div>';
		if(!empty($options['uploadUrls'])) {
			$ret .= '
<div id="'.$id.'_list"></div>
';
		}

		if(!empty($js)) {
			if(empty($options['ajax'])) {
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration("\r\n".'window.hikashop.ready(function(){'.$js."\r\n".'});');
			} else {
				$ret .= "\r\n".'<script type="text/javascript">'."\r\n".'window.hikashop.ready(function(){'.$js."\r\n".'});'."\r\n".'</script>';
			}
		}

		return $ret;
	}

	public function displayImageMultiple($id, $content = array(), $options = array()) {
		$this->initialize();
		$this->processOptions($options, 'image');

		$ret = '
<div id="'.$id.'_main" class="hikashop_dropzone">
	<div class="'.$options['classes']['mainDiv'].'">
		<div class="hikashop_uploader_image_add '.$id.'_add">';

		if(!empty($options['tooltip']))
			hikashop_loadJsLib('tooltip');

		if(!empty($options['uploadUrls'])) {
			$opt = '';
			if(!empty($options['tooltip']))
				$opt = ' data-toggle="hk-tooltip" data-title="'.JText::_('HIKA_UPLOAD_IMAGE').'"';

			$ret .= $this->popup->display(
				'<span class="'.$options['classes']['btn_upload'].'"></span>',
				'HIKA_UPLOAD_IMAGE',
				$options['uploadUrls'][0],
				$id.'_uploadpopup',
				750, 460, 'onclick="return window.hkUploaderList[\''.$id.'\'].uploadFile(this);"'.$opt, '', 'link'
			);
		}

		if(!empty($options['browseUrl'])) {
			$opt = '';
			if(!empty($options['tooltip']))
				$opt = ' data-toggle="hk-tooltip" data-title="'.JText::_('HIKA_ADD_IMAGE').'"';

			$ret .= $this->popup->display(
				'<span class="'.$options['classes']['btn_add'].'"></span>',
				'HIKA_ADD_IMAGE',
				$options['browseUrl'],
				$id.'_addpopup',
				750, 460, 'onclick="return window.hkUploaderList[\''.$id.'\'].browseImage(this);"'.$opt, '', 'link'
			);
		}

		$contentHtml = '';
		if(!empty($content)) {
			if(is_string($content)) {
				$contentHtml = $content;
			} else {
				foreach($content as $k => $c) {
					$liClass = ($k == 0) ? $options['classes']['firstImg'] : $options['classes']['otherImg'];
					$contentHtml .= '<li class="'.$liClass.'">'.$c.'</li>';
				}
			}
		}

		$ret .= '
		</div>
		<ul id="'.$id.'_content" class="hikashop_uploader_multiimage_content uploader_data_container hkContent '.$options['classes']['contentClass'].'">' . $contentHtml . '</ul>
		<div id="'.$id.'_empty" class="hikashop_uploader_image_empty" style="'.(!empty($content) ? 'display:none;' : '') . '">
			<span>'.$options['text'].'</span>
		</div>
	</div>
</div>';
		if(!empty($options['uploadUrls'])) {
			$ret .= '
<input id="'.$id.'" type="file" multiple/>
<div id="'.$id.'_list"></div>
';
		}

		$js = '';
		if(!empty($options['uploadUrls'])) {
			$js .= "\r\n" . 'var hkUploader_'.$id.' = new hkUploaderMgr("'.$id.'", {mode: \'listImg\', url:\''.$options['uploadUrls'][1].'\', formData:'.$options['formData'].', options: {maxSize:'.(int)$options['maxSize'].', imgClasses:[\''.$options['classes']['firstImg'].'\',\''.$options['classes']['otherImg'].'\']} })';
		}

		if(!empty($js)) {
			if(empty($options['ajax'])) {
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration("\r\n".'window.hikashop.ready(function(){'.$js."\r\n".'});');
			} else {
				$ret .= "\r\n".'<script type="text/javascript">'."\r\n".'window.hikashop.ready(function(){'.$js."\r\n".'});'."\r\n".'</script>';
			}
		}

		return $ret;
	}

	public function displayFileSingle($id, $content = '', $options = array()) {
		$this->initialize();
		$this->processOptions($options, 'file');
		$js = '';

		$ret = '
<div id="'.$id.'_main" class="hikashop_dropzone">
	<div class="'.$options['classes']['mainDiv'].'">
		<div class="hikashop_uploader_file_add '.$id.'_add">';

		if(!empty($options['tooltip']))
			hikashop_loadJsLib('tooltip');

		if(!empty($options['uploadUrls'])) {
			$opt = '';
			if(!empty($options['tooltip']))
				$opt = ' data-toggle="hk-tooltip" data-title="'.JText::_('HIKA_UPLOAD_FILE').'"';

			$ret .= '<span id="'.$id.'-btn" class="opload-btn">'.
				$this->popup->display(
					'<span class="'.$options['classes']['btn_upload'].'"></span>',
					'HIKA_UPLOAD_FILE',
					$options['uploadUrls'][0],
					$id.'_uploadpopup',
					750, 460, 'onclick="return window.hkUploaderList[\''.$id.'\'].uploadFile(this);"'.$opt, '', 'link'
				).
				'<input id="'.$id.'" type="file"/></span>';

			$js .= "\r\n" . 'var hkUploader_'.$id.' = new hkUploaderMgr("'.$id.'", {mode: \'single\', url:\''.$options['uploadUrls'][1].'\',formData:'.$options['formData'].',options:{maxSize:'.(int)$options['maxSize'].'}})';
		}
		$ret .= '
		</div>
		<div id="'.$id.'_content" class="hikashop_uploader_singlefile_content uploader_data_container">' . $content . '</div>
		<div id="'.$id.'_empty" class="hikashop_uploader_file_empty" style="'.((!empty($content) && empty($options['empty'])) ? 'display:none;' : '') . '">
			<span>'.$options['text'].'</span>
		</div>
	</div>
</div>';
		if(!empty($options['uploadUrls'])) {
			$ret .= '
<div id="'.$id.'_list"></div>
';
		}

		if(!empty($js)) {
			if(empty($options['ajax'])) {
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration("\r\n".'window.hikashop.ready(function(){'.$js."\r\n".'});');
			} else {
				$ret .= "\r\n".'<script type="text/javascript">'."\r\n".'window.hikashop.ready(function(){'.$js."\r\n".'});'."\r\n".'</script>';
			}
		}

		return $ret;
	}


	public function displayFileMultiple($id, $content = '', $options = array()) {
		$this->initialize();
		$this->processOptions($options, 'file');
		$js = '';

		$ret = '
<div id="'.$id.'_main" class="hikashop_dropzone">
	<div class="'.$options['classes']['mainDiv'].'">
		<div class="hikashop_uploader_file_add '.$id.'_add">';

		if(!empty($options['tooltip']))
			hikashop_loadJsLib('tooltip');

		if(!empty($options['uploadUrls'])) {
			$opt = '';
			if(!empty($options['tooltip']))
				$opt = ' data-toggle="hk-tooltip" data-title="'.JText::_('HIKA_UPLOAD_FILE').'"';

			$ret .= '<span id="'.$id.'-btn" class="opload-btn">'.
				$this->popup->display(
					'<span class="'.$options['classes']['btn_upload'].'"></span>',
					'HIKA_UPLOAD_FILE',
					$options['uploadUrls'][0],
					$id.'_uploadpopup',
					750, 460, 'onclick="return window.hkUploaderList[\''.$id.'\'].uploadFile(this);"'.$opt, '', 'link'
				).
				'<input id="'.$id.'" type="file"/></span>';

			$js .= "\r\n" . 'var hkUploader_'.$id.' = new hkUploaderMgr("'.$id.'", {mode: \'list\', url:\''.$options['uploadUrls'][1].'\',formData:'.$options['formData'].',options:{maxSize:'.(int)$options['maxSize'].'}})';
		}

		if(!empty($options['toolbar'])) {
			if(is_array($options['toolbar']))
				$ret .= implode('', $options['toolbar']);
			else
				$ret .= $options['toolbar'];
		}

		$contentHtml = '';
		if(!empty($content)) {
			if(is_array($content))
				$contentHtml .= implode('', $content);
			else
				$contentHtml .= $content;
		}

		$ret .= '
		</div>
		<div id="'.$id.'_content" class="hikashop_uploader_multifile_content uploader_data_container">' . $contentHtml . '</div>
		<div style="clear:both"></div>
		<div id="'.$id.'_empty" class="hikashop_uploader_file_empty" style="'.((!empty($content) && empty($options['empty'])) ? 'display:none;' : '') . '">
			<span>'.$options['text'].'</span>
		</div>
	</div>
</div>';
		if(!empty($options['uploadUrls'])) {
			$ret .= '
<div id="'.$id.'_list"></div>
';
		}

		if(!empty($js)) {
			if(empty($options['ajax'])) {
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration("\r\n".'window.hikashop.ready(function(){'.$js."\r\n".'});');
			} else {
				$ret .= "\r\n".'<script type="text/javascript">'."\r\n".'window.hikashop.ready(function(){'.$js."\r\n".'});'."\r\n".'</script>';
			}
		}

		return $ret;
	}
}
