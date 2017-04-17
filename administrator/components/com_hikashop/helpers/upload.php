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
class hikashopUploadHelper {

	protected $options;
	protected $imagesExt = array('jpg', 'jpeg', 'gif', 'png');

	public function __construct() {
		$this->setOptions();
	}

	public function setOptions($options = null) {
		$this->options = array(
			'upload_dir' => HIKASHOP_MEDIA.'upload'.DS,
			'upload_url' => JURI::base(true).'/media/'.HIKASHOP_COMPONENT.'/upload/',
			'param_name' => 'files',

			'max_file_size' => null,
			'min_file_size' => 1,
			'accept_file_types' => '/.+$/i',

			'max_width' => null,
			'max_height' => null,
			'min_width' => 1,
			'min_height' => 1,

			'send_header' => false,

			'orient_image' => false,
			'image_versions' => array()
		);

		if(empty($options))
			return;

		foreach($options as $k => $v) {
			if(!is_array($v) || empty($this->options[$k]))
				$this->options[$k] = $v;
			else
				$this->options[$k] = array_merge($this->options[$k], $v);
		}
	}

	public function process($options = null) {
		JRequest::checkToken() || die('Invalid Token');

		if(!empty($options))
			$this->setOptions($options);

		if(!empty($this->options['send_header']) && !headers_sent()) {
			header('Pragma: no-cache');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('X-Content-Type-Options: nosniff');
		}

		$upload = reset($_FILES);
		if(isset($_FILES[$this->options['param_name']]))
			$upload = $_FILES[$this->options['param_name']];

		if(empty($upload))
			return null;

		$uploaded_file = isset($upload['tmp_name']) ? $upload['tmp_name'] : null;
		$name = isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ? $upload['name'] : null);
		$size = isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ? $upload['size'] : null);
		$type = isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ? $upload['type'] : null);
		$error = isset($upload['error']) ? $upload['error'] : null;

		$slice = null;
		if(isset($_POST['slices']) && (int)$_POST['slices'] > 1) {
			$slice = array(
				'index' => (int)@$_POST['slice'],
				'total' => $_POST['slices'],
				'size' => (int)@$_POST['slice_size'],
				'total_size' => (int)@$_POST['slices_size'],
			);
		}

		$file = new stdClass();
		$file->name = $this->trim_file_name($name, $type);
		$file->size = intval($size);
		$file->type = $type;

		if(empty($this->options['sub_folder']))
			$this->options['sub_folder'] = '';

		$shopConfig = hikashop_config();
		if($options['type'] == 'file')
			$allowed_extensions = $shopConfig->get('allowedfiles');
		else
			$allowed_extensions = $shopConfig->get('allowedimages');

		if(!empty($error)) {
			$file->error = $error;
		} else if(empty($file->name)) {
			$file->error = 'missingFileName';
		} else if(!is_uploaded_file($uploaded_file)) {
			$file->error = 'missingData';
		} else if(!empty($this->options['max_file_size']) && $file->size > $this->options['max_file_size']) {
			$file->error = 'maxFileSize';
		} else if(!empty($this->options['min_file_size']) && $file->size < $this->options['min_file_size']) {
			$file->error = 'minFileSize';
		}
		if(!empty($file->error))
			return $file;

		$file_path = strtolower(JFile::makeSafe($name));

		if(!empty($slice) && $slice['index'] > 0 && substr($file_path, -5) == '.part') {
			$pos = strrpos($file_path, '_');
			$file_path = substr($file_path, 0, $pos);
		}

		if(!preg_match('#\.('.str_replace(array(',','.'), array('|','\.'), $allowed_extensions).')$#Ui', $file_path, $extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui', $file_path)) {
			$file->error = JText::sprintf('ACCEPTED_TYPE', substr($file_path,strrpos($file_path, '.') + 1), $allowed_extensions);
			return $file;
		}

		$file_path = str_replace(array('.',' '), '_', substr($file_path, 0, strpos($file_path,$extension[0]))) . $extension[0];

		if(empty($slice) || $slice['index'] == 0) {
			$file_path_origin = $file_path;

			if(JFile::exists($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
				$pos = strrpos($file_path, '.');
				$file_path = substr($file_path, 0, $pos) . '_' . rand() . '.' . substr($file_path, $pos + 1);
			}

			if(!empty($slice)) {
				$pos = strrpos($file_path, '.');
				$file_path .= '_' . $slice['total_size'] . '-' . $slice['size'] . '.part';
				$file->partial = true;

				if(JFile::exists($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
					clearstatcache();
					$current_filesize = filesize($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
					$current_slice = $current_filesize / $slice['size'];

					if(is_int($current_slice)) {
						$file->name = $file_path;
						$file->resume = true;
						$file->slice = $current_slice;
						return $file;
					}

					$pos = strrpos($file_path_origin, '.');
					$file_path = substr($file_path_origin, 0, $pos) . '_' . rand() . '.' . substr($file_path_origin, $pos + 1) . '_' . $slice['total_size'] . '-' . $slice['size'] . '.part';
				}
			}

			if(!JFile::upload($uploaded_file, $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
				if(!move_uploaded_file($uploaded_file, $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
					$file->error = JText::sprintf('FAIL_UPLOAD', $uploaded_file, $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
					return $file;
				}
			}
		} else {
			$file_path = strtolower(JFile::makeSafe($name));

			if(substr($file_path, -5) != '.part' || !JFile::exists($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
				$file->error = 'partialUploadNotFound';
				return $file;
			}

			clearstatcache();
			$current_filesize = filesize($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
			if($current_filesize != ((int)$slice['index'] * (int)$slice['size'])) {
				$file->error = 'partialUploadInvalid';
				return $file;
			}

			$destFile = fopen($this->options['upload_dir'] . $this->options['sub_folder'] .$file_path, 'ab');
			$sourceFile = fopen($uploaded_file, 'rb');
			while(!feof($sourceFile)) {
				$c = fread($sourceFile, 8192);
				fwrite($destFile, $c);
			}
			fclose($destFile);
			fclose($sourceFile);

			if(((int)$slice['index'] + 1) == (int)$slice['total']) {
				clearstatcache();
				$current_filesize = filesize($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
				if((int)$current_filesize != (int)$slice['total_size']) {
					$file->error = 'partialUploadSizeError';
					return $file;
				}

				$pos = strrpos($file_path, '_');
				$orgin_path = substr($file_path, 0, $pos);

				rename($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path, $this->options['upload_dir'] . $this->options['sub_folder'] . $orgin_path);
				$file_path = $orgin_path;
			} else
				$file->partial = true;
		}

		if($options['type'] != 'file' && strpos($file->name, '.') !== false && !empty($this->options['orient_image'])) {
			$ext = strtolower(substr($file->name, strrpos($file->name, '.') + 1));
			if(!in_array($ext, $this->imagesExt))
				$this->orient_image($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
		}

		$file->name = $file_path;
		$file->path = $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path;
		$file->url = $this->options['upload_url'] . $this->options['sub_folder'] . rawurlencode($file->name);
		$file->size = filesize($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);

		return $file;
	}

	public function processFallback($options = null) {
		JRequest::checkToken() || die('Invalid Token');

		if(!empty($options)) {
			$this->setOptions($options);
		}
		$upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : reset($_FILES);
		$info = array();
		if($upload && is_array($upload['tmp_name'])) {
			foreach ($upload['tmp_name'] as $index => $value) {
				$info[] = $this->handle_file_upload(
					$upload['tmp_name'][$index],
					isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
					isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
					isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
					$upload['error'][$index],
					$options,
					$index
				);
			}
		} else if($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
			$info[] = $this->handle_file_upload(
				isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
				isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ? $upload['name'] : null),
				isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ? $upload['size'] : null),
				isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ? $upload['type'] : null),
				isset($upload['error']) ? $upload['error'] : null,
				$options
			);
		}
		return $info;
	}

	protected function validate($uploaded_file, $file, $error) {
		if($error) {
			$file->error = $error;
			return false;
		}
		if(!$file->name) {
			$file->error = 'missingFileName';
			return false;
		}
		if(!preg_match($this->options['accept_file_types'], $file->name)) {
			$file->error = 'acceptFileTypes';
			return false;
		}
		if($uploaded_file && is_uploaded_file($uploaded_file)) {
			$file_size = filesize($uploaded_file);
		} else {
			$file_size = $_SERVER['CONTENT_LENGTH'];
		}
		if($this->options['max_file_size'] && ( $file_size > $this->options['max_file_size'] || $file->size > $this->options['max_file_size']) ) {
			$file->error = 'maxFileSize';
			return false;
		}
		if($this->options['min_file_size'] && $file_size < $this->options['min_file_size']) {
			$file->error = 'minFileSize';
			return false;
		}

		list($img_width, $img_height) = @getimagesize($uploaded_file);
		if(is_int($img_width)) {
			if($this->options['max_width'] && $img_width > $this->options['max_width'] || $this->options['max_height'] && $img_height > $this->options['max_height']) {
				$file->error = 'maxResolution';
				return false;
			}
			if($this->options['min_width'] && $img_width < $this->options['min_width'] || $this->options['min_height'] && $img_height < $this->options['min_height']) {
				$file->error = 'minResolution';
				return false;
			}
		}
		return true;
	}

	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $options) {
		$file = new stdClass();
		$file->name = $this->trim_file_name($name, $type);
		$file->size = intval($size);
		$file->type = $type;

		if(empty($this->options['sub_folder']))
			$this->options['sub_folder'] = '';

		if(!$this->validate($uploaded_file, $file, $error))
			return $file;

		$shopConfig = hikashop_config();
		if($options['type'] == 'file') {
			$allowed = $shopConfig->get('allowedfiles');
		} else {
			$allowed = $shopConfig->get('allowedimages');
		}

		$file_path = strtolower(JFile::makeSafe($name));
		if(!preg_match('#\.('.str_replace(array(',','.'), array('|','\.'), $allowed).')$#Ui', $file_path,$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui', $file_path)) {
			$file->error = JText::sprintf('ACCEPTED_TYPE', substr($file_path,strrpos($file_path, '.') + 1), $allowed);
			return $file;
		}

		$file_path = str_replace(array('.',' '), '_', substr($file_path, 0, strpos($file_path,$extension[0]))) . $extension[0];

		if(JFile::exists($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
			$pos = strrpos($file_path, '.');
			$file_path = substr($file_path, 0, $pos) . '_' . rand() . '.' . substr($file_path, $pos + 1);
		}

		if(!JFile::upload($uploaded_file, $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
			if(!move_uploaded_file($uploaded_file, $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path)) {
				$file->error = JText::sprintf('FAIL_UPLOAD', $uploaded_file, $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
				return $file;
			}
		}

		$file_size = filesize($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
		$file->name = $file_path;
		$file->path = $this->options['upload_dir'] . $this->options['sub_folder'] . $file_path;
		$file->url = $this->options['upload_url'] . $this->options['sub_folder'] . rawurlencode($file->name);
		if(strpos($file->name, '.') !== false) {
			$ext = strtolower(substr($file->name, strrpos($file->name, '.') + 1));
			if(!in_array($ext, $this->imagesExt) && $this->options['orient_image']) {
				$this->orient_image($this->options['upload_dir'] . $this->options['sub_folder'] . $file_path);
			}
		}
		return $file;
	}

	protected function trim_file_name($name, $type) {
		$file_name = trim(basename(stripslashes($name)), ".\x00..\x20");

		if(strpos($file_name, '.') === false && preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches))
			$file_name .= '.'.$matches[1];

		return $file_name;
	}

	protected function orient_image($file_path) {
		$exif = @exif_read_data($file_path);
		if($exif === false)
			return false;

		$orientation = intval(@$exif['Orientation']);
		if(!in_array($orientation, array(3, 6, 8)))
			return false;

		$image = @imagecreatefromjpeg($file_path);
		switch ($orientation) {
			case 3:
				$image = @imagerotate($image, 180, 0);
				break;
			case 6:
				$image = @imagerotate($image, 270, 0);
				break;
			case 8:
				$image = @imagerotate($image, 90, 0);
				break;
			default:
				return false;
		}
		$success = imagejpeg($image, $file_path);
		@imagedestroy($image);
		return $success;
	}
}
