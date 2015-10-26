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
class hikashopFileClass extends hikashopClass {
	var $tables = array('file');
	var $pkeys = array('file_id');
	var $namekeys = array();
	var $deleteToggle = array('file'=>array('file_type', 'file_ref_id'));
	var $error_type = '';

	function saveFile($var_name = 'files', $type = 'image', $allowed = null) {
		$file = JRequest::getVar($var_name, array(), 'files', 'array');

		if(empty($file['name']))
			return false;

		$app = JFactory::getApplication();
		$config =& hikashop_config();
		if(empty($allowed)) {
			if($type == 'file')
				$allowed = $config->get('allowedfiles');
			else
				$allowed = $config->get('allowedimages');
		}

		$uploadPath = $this->getPath($type);
		$tempData = array();

		if(empty($file['name']))
			return false;
		$file_path = strtolower(JFile::makeSafe($file['name']));

		if(!preg_match('#\.('.str_replace(array(',','.'),array('|','\.'),$allowed).')$#Ui',$file_path,$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui',$file_path)){
			$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',substr($file_path,strrpos($file_path,'.')+1),$allowed), 'notice');
			return false;
		}

		$file_path = str_replace(array('.',' '),'-',substr($file_path,0,strpos($file_path,$extension[0]))).$extension[0];

		if(JFile::exists($uploadPath . $file_path)) {
			if(filesize($uploadPath . $file_path) == filesize($file['tmp_name']) && md5_file($uploadPath . $file_path) == md5_file($file['tmp_name']))
				return $file_path;

			$pos = strrpos($file_path,'.');
			$file_path = substr($file_path,0,$pos).'-'.rand().'.'.substr($file_path,$pos+1);
		}

		if(!JFile::upload($file['tmp_name'], $uploadPath . $file_path)){
			if ( !move_uploaded_file($file['tmp_name'], $uploadPath . $file_path)) {
				$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$file['tmp_name'],$uploadPath . $file_path), 'error');
				return false;
			}
		}

		return $file_path;
	}

	function storeFiles($type, $pkey, $var_name = 'files', $subPath = '') {
		$ids = array();
		$files = JRequest::getVar( $var_name, array(), 'files', 'array' );
		if(!empty($files['name'][0]) || !empty($files['name'][1])) {

			$app = JFactory::getApplication();
			$config =& hikashop_config();
			if($type=='file'){
				$allowed = $config->get('allowedfiles');
			}else{
				$allowed = $config->get('allowedimages');
				$imageHelper = hikashop_get('helper.image');
			}

			$uploadPath = $this->getPath($type, $subPath);

			$tempData = array();
			foreach($files['name'] as $id => $filename) {
				if(empty($filename)) continue;
				$file_path = strtolower(JFile::makeSafe($filename));
				if(!preg_match('#\.('.str_replace(array(',','.'),array('|','\.'),$allowed).')$#Ui',$file_path,$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui',$file_path)){
					$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',substr($file_path,strrpos($file_path,'.')+1),$allowed), 'notice');
					continue;
				}
				$file_path= str_replace(array('.',' '),'-',substr($file_path,0,strpos($file_path,$extension[0]))).$extension[0];
				$tempData[$id]= $file_path;
			}

			if(!empty($tempData)) {
				switch($type){
					case 'category':
						$query = 'SELECT file_path FROM '.hikashop_table(end($this->tables)).' WHERE file_ref_id = '.$pkey.' AND file_type=\'category\'';
						$this->database->setQuery($query);
						if(!HIKASHOP_J25){
							$oldEntries = $this->database->loadResultArray();
						} else {
							$oldEntries = $this->database->loadColumn();
						}

						if(!empty($oldEntries)) {
							$oldEntriesQuoted = array();
							foreach($oldEntries as $old) {
								$oldEntriesQuoted[] = $this->database->Quote($old);
							}
							$query = 'SELECT file_path FROM '.hikashop_table('file').' WHERE file_path IN ('.implode(',',$oldEntriesQuoted).') AND file_ref_id != '.$pkey;
							$this->database->setQuery($query);
							if(!HIKASHOP_J25){
								$keepEntries = $this->database->loadResultArray();
							} else {
								$keepEntries = $this->database->loadColumn();
							}
							foreach($oldEntries as $old) {
								if((empty($keepFiles) || !in_array($old,$keepFiles)) && JFile::exists($uploadPath . $old))
									JFile::delete($uploadPath . $old);
							}
						}
						break;
				}

				foreach($tempData as $id => $file_path) {
					$process = true;
					if(JFile::exists($uploadPath . $file_path)) {
						if(filesize($uploadPath . $file_path) == filesize($files['tmp_name'][$id])){
							$process = false;
						}else{
							$pos = strrpos($file_path,'.');
							$file_path = substr($file_path,0,$pos).'-'.rand().'.'.substr($file_path,$pos+1);
						}
					}
					if($process){
						if(!JFile::upload($files['tmp_name'][$id], $uploadPath . $file_path)) {
							if ( !move_uploaded_file($files['tmp_name'][$id], $uploadPath . $file_path)) {
								$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$files['tmp_name'][$id],$uploadPath . $file_path), 'error');
								continue;
							}
						}
						if(!in_array($type,array('file','watermark'))) {
							if($type == 'category') {
								$imageHelper->resizeImage($file_path,'category');
							} else {
								$imageHelper->resizeImage($file_path);
							}

							$imageHelper->generateThumbnail($file_path);
						}
					}
					$element = new stdClass();
					$element->file_path = $file_path;
					if(!empty($subPath)) {
						$element->file_path = trim($subPath, DS.' ').DS.$file_path;
					}
					$element->file_type = $type;
					$element->file_ref_id = $pkey;
					$status = $this->save($element);
					if($status) {
						$ids[$id] = $status;
					}
				}
			}
		}elseif(JRequest::getVar('ctrl')=='product'){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_( 'ADD_FILE_VIA_BROWSE_BUTTON'),'error');
		}

		if(!empty($ids)){
			switch($type){
				case 'category':
					$query = 'DELETE FROM '.hikashop_table(end($this->tables)).' WHERE file_id NOT IN ('.implode(',',$ids).') AND file_ref_id = '.$pkey.' AND file_type=\'category\'';
					$this->database->setQuery($query);
					$this->database->query();
					break;
			}
		}
		return $ids;
	}

	function deleteFiles($type,$pkeys, $ignoreFile=false){
		if(!is_array($pkeys)) $pkeys = array($pkeys);
		$uploadPath = $this->getPath($type);
		$query = 'SELECT * FROM '.hikashop_table(end($this->tables)).' WHERE file_ref_id IN ('.implode(',',$pkeys).') AND file_type=\''.$type.'\'';
		$this->database->setQuery($query);
		$oldEntries = $this->database->loadObjectList();

		if(!empty($oldEntries)){
			$paths = array();
			$ids = array();
			foreach($oldEntries as $old){
				$paths[] = $this->database->Quote($old->file_path);
				$ids[] = $old->file_id;
			}
			$query = 'SELECT file_path FROM '.hikashop_table(end($this->tables)).' WHERE file_path IN ('.implode(',',$paths).') AND file_id NOT IN ('.implode(',',$ids).')';
			$this->database->setQuery($query);
			if(!HIKASHOP_J25){
				$stillUsed = $this->database->loadResultArray();
			} else {
				$stillUsed = $this->database->loadColumn();
			}
			if(!$ignoreFile){
				jimport('joomla.filesystem.folder');
				$thumbnail_folders = JFolder::folders($uploadPath);
				if(JFolder::exists($uploadPath.'thumbnails')) {
					$other_thumbnail_folders = JFolder::folders($uploadPath.'thumbnails');
					foreach($other_thumbnail_folders as $other_thumbnail_folder) {
						$thumbnail_folders[] = 'thumbnails'.DS.$other_thumbnail_folder;
					}
				}
				foreach($oldEntries as $old){
					if((empty($stillUsed) || !in_array($old->file_path, $stillUsed)) && JFile::exists($uploadPath . $old->file_path)) {
						JFile::delete($uploadPath . $old->file_path);
						foreach($thumbnail_folders as $thumbnail_folder) {
							if($thumbnail_folder != 'thumbnail' && substr($thumbnail_folder, 0, 9) != 'thumbnail' && substr($thumbnail_folder, 0, 11) != ('thumbnails'.DS))
								continue;
							if(!in_array($type,array('file','watermark')) && JFile::exists($uploadPath.$thumbnail_folder.DS.$old->file_path)) {
								JFile::delete( $uploadPath .$thumbnail_folder.DS. $old->file_path );
							}
						}
					}
				}
			}
			$query = 'DELETE FROM '.hikashop_table(end($this->tables)).' WHERE file_ref_id IN ('.implode(',',$pkeys).') AND file_type=\''.$type.'\'';
			$this->database->setQuery($query);
			$this->database->query();
			$elements = array();
			foreach($oldEntries as $old){
				$elements[]=$old->file_id;
			}
			$class = hikashop_get('helper.translation');
			$class->deleteTranslations('file',$elements);
		}
	}

	function resetdownload($file_id,$order_id=0,$file_pos=0){
		$query = 'UPDATE '.hikashop_table('download').' SET download_number=0 WHERE file_id='.(int)$file_id;
		if(!empty($order_id)){
			$query .= ' AND order_id='.(int)$order_id;
		}
		if(!empty($file_pos)){
			$query .= ' AND file_pos='.(int)$file_pos;
		}
		$this->database->setQuery($query);
		return $this->database->query();
	}

	function download($file_id, $order_id = 0, $file_pos = 1, $email = '') {
		$app = JFactory::getApplication();

		$file = $this->get($file_id);
		$file_pos = (int)$file_pos;
		if($file_pos <= 0)
			$file_pos = 1;

		if(!$app->isAdmin() && empty($file->file_free_download)) {
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->get($order_id);
			$user_id = hikashop_loadUser();

			if(empty($user_id) && !empty($email)) {
				$userClass = hikashop_get('class.user');
				$user = $userClass->get($order->order_user_id);
				if(!empty($user) && empty($user->user_cms_id) && $user->user_email == $email) {
					$user_id = $order->order_user_id;
				}
			}

			if(empty($user_id)) {
				$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
				$this->error_type = 'login';
				return false;
			}

			$file->order = $order;
			if(empty($order) || $order->order_user_id != $user_id) {
				$app->enqueueMessage(JText::_('ORDER_NOT_FOUND'));
				$this->error_type = 'no_order';
				return false;
			}
			if($order->order_type != 'sale') {
				$app->enqueueMessage(JText::_('WRONG_ORDER'));
				$this->error_type = 'wrong_order';
				return false;
			}

			$config =& hikashop_config();
			$order_status_for_download = $config->get('order_status_for_download','confirmed,shipped');
			if(!in_array($order->order_status,explode(',',$order_status_for_download))){
				$app->enqueueMessage(JText::_('BECAUSE_STATUS_NO_DOWNLOAD'));
				$this->error_type = 'status';
				return false;
			}

			$download_time_limit = $config->get('download_time_limit',0);
			if(!empty($download_time_limit) && ($download_time_limit+(!empty($order->order_invoice_created)?$order->order_invoice_created:$order->order_created))<time()){
				$app->enqueueMessage(JText::_('TOO_LATE_NO_DOWNLOAD'));
				$this->error_type = 'date';
				return false;
			}

			$query = 'SELECT a.* FROM '.hikashop_table('order_product').' AS a WHERE a.order_id = '.$order_id;
			$this->database->setQuery($query);
			$order->products = $this->database->loadObjectList();

			$product_ids = array();
			foreach($order->products as $product){
				if((int)$product->order_product_quantity >= $file_pos || $file_pos == 1)
					$product_ids[] = $product->product_id;
			}
			if(empty($product_ids)) {
				$app->enqueueMessage(JText::_('INVALID_FILE_NUMBER'));
				$this->error_type = 'status';
				return false;
			}
			$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$product_ids).') AND product_type=\'variant\'';
			$this->database->setQuery($query);
			$products = $this->database->loadObjectList();
			if(!empty($products)){
				foreach($products as $product){
					foreach($order->products as $item){
						if($product->product_id == $item->product_id && !empty($product->product_parent_id)){
							$item->product_parent_id = $product->product_parent_id;
							$product_ids[] = $product->product_parent_id;
						}
					}
				}
			}

			$filters = array(
				'a.file_ref_id IN ('.implode(',',$product_ids).')',
				'a.file_type=\'file\'',
				'a.file_id='.$file_id
			);

			if(substr($file->file_path,0,1) == '@' || substr($file->file_path,0,1) == '#') {
				$query = 'SELECT a.*,b.* FROM '.hikashop_table('file').' AS a '.
					' LEFT JOIN '.hikashop_table('download').' AS b ON b.order_id='.$order->order_id.' AND a.file_id = b.file_id AND b.file_pos = '.$file_pos.
					' WHERE '.implode(' AND ',$filters);
			} else {
				$query = 'SELECT a.*, b.*, c.order_product_quantity FROM '.hikashop_table('file').' AS a '.
					' LEFT JOIN '.hikashop_table('download').' AS b ON b.order_id='.$order->order_id.' AND a.file_id = b.file_id '.
					' LEFT JOIN '.hikashop_table('order_product').' AS c ON c.order_id='.$order->order_id.' AND c.product_id = a.file_ref_id '.
					' WHERE '.implode(' AND ',$filters);
			}

			$this->database->setQuery($query);
			$fileData = $this->database->loadObject();
			if(!empty($fileData)){
				if(!empty($file->file_limit) && (int)$file->file_limit != 0)
					$download_number_limit = (int)$file->file_limit;
				else
					$download_number_limit = $config->get('download_number_limit',0);

				if($download_number_limit < 0)
					$download_number_limit = 0;

				if(isset($fileData->order_product_quantity) && (int)$fileData->order_product_quantity > 0)
					$download_number_limit *= (int)$fileData->order_product_quantity;

				if(!empty($download_number_limit) && $download_number_limit <= $fileData->download_number) {
					$app->enqueueMessage(JText::_('MAX_REACHED_NO_DOWNLOAD'));
					$this->error_type = 'limit';
					return false;
				}
			}else{
				$app->enqueueMessage(JText::_('FILE_NOT_FOUND'));
				$this->error_type = 'no_file';
				return false;
			}
		}

		if(!empty($file)){
			$path = $this->getPath('file');
			if(substr($file->file_path,0,7) == 'http://' || substr($file->file_path,0,8) == 'https://' || substr($file->file_path,0,1) == '@' || substr($file->file_path,0,1) == '#' || file_exists($path.$file->file_path) || file_exists($file->file_path) ){
				if(!$app->isAdmin()){
					if(!empty($file->file_free_download)){
						$order_id = 0;
					}
					$key = 'hikashop_download_'.$order_id.'.'.$file_pos;
					if(empty($_SESSION[$key]) || empty($_SERVER['HTTP_RANGE'])){
						$_SESSION[$key] = true;
						$query = 'SELECT * FROM '.hikashop_table('download').' WHERE file_id='.$file->file_id.' AND order_id='.$order_id.' AND file_pos='.$file_pos;
						$this->database->setQuery($query);
						$download = $this->database->loadObject();
						if(empty($download)){
							$query = 'INSERT INTO '.hikashop_table('download').'(file_id,order_id,download_number,file_pos) VALUES('.$file->file_id.','.$order_id.',1,'.$file_pos.');';
						}else{
							$query = 'UPDATE '.hikashop_table('download').' SET download_number=download_number+1 WHERE file_id='.$file->file_id.' AND order_id='.$order_id.' AND file_pos='.$file_pos;
						}
						$this->database->setQuery($query);
						$this->database->query();
					}
				}
				$file->order_id = (int)$order_id;
				$file->file_pos = $file_pos;
				$this->sendFile($file, true, $path);
			}
		}
		$app->enqueueMessage(JText::_('FILE_NOT_FOUND'));
		return true;
	}

	function sendFile(&$file, $is_resume=true, $path=null, $options=array()){
		if(empty($path)) {
			$path = $this->getPath('file');
		}

		$file->file_path = trim($file->file_path);

		$filename = $path.$file->file_path;
		if(substr($file->file_path,0,7) == 'http://' || substr($file->file_path,0,8) == 'https://' || substr($file->file_path,0,1) == '@' || substr($file->file_path,0,1) == '#' || file_exists($file->file_path) ){
			$filename = $file->file_path;
		}
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		$dispatcher->trigger( 'onBeforeDownloadFile', array( &$filename, &$do, &$file, $options) );
		if(!$do) return false;

		if(substr($filename,0,7) == 'http://' || substr($filename,0,8) == 'https://') {
			header('location: '.$filename);
			exit;
		}
		if(substr($filename,0,1) == '@' || substr($filename,0,1) == '#') {
			exit;
		}

		if(strpos($filename, '..') !== false)
			return false;

		$clean_filename = JPath::clean($filename);
		$secure_path = $this->getPath('file');
		if((JPATH_ROOT != '') && strpos($path, JPath::clean(JPATH_ROOT)) !== 0 && strpos($clean_filename, JPath::clean($secure_path)) !== 0)
			return false;

		clearstatcache();
		$size = filesize($filename);
		$fileinfo = pathinfo($filename);

		ob_end_clean();
		ob_start();

		$name = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ?
						preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'], '.') - 1) :
						$fileinfo['basename'];

		if(function_exists('apache_get_modules')){
			$modules = apache_get_modules();
			if (is_array($modules) && count($modules) && in_array('mod_xsendfile', $modules)) {
				header("Expires: 0");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . $name . '"');
				header("Cache-Control: maxage=1");
				header("Pragma: public");
				header("Content-Transfer-Encoding: binary");
				header('X-Sendfile: ' . $filename);
				$dispatcher->trigger( 'onAfterDownloadFile', array( &$filename, &$file) );
				exit;
			}
		}

		$range = '';
		if($is_resume && isset($_SERVER['HTTP_RANGE'])) {
			list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

			if ($size_unit == 'bytes') 	{
				list($range, $extra_ranges) = explode(',', $range_orig, 2);
			}
		}

		$seek = explode('-', $range, 2);

		$seek_end = (empty($seek[1])) ? ($size - 1) : min(abs(intval($seek[1])),($size - 1));
		$seek_start = (empty($seek[0]) || $seek_end < abs(intval($seek[0]))) ? 0 : max(abs(intval($seek[0])),0);

		if(!empty($options['thumbnail_x']) || !empty($options['thumbnail_y'])) {
			$extension = strtolower(substr($filename, strrpos($filename, '.') + 1));
			if(in_array($extension, array('jpg','jpeg','png','gif'))) {
				if(!ini_get('safe_mode')){
					set_time_limit(0);
				}

				$imageHelper = hikashop_get('helper.image');
				$img = $imageHelper->getThumbnail($filename, array(100,100), array(), false, false);
				if($img->success && !empty($img->data)) {

					$format = $extension;
					if($format == 'jpg') $format = 'jpeg';

					header("Expires: 0");
					header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
					header('Content-Type: image/'.$format);
					header('Content-Disposition: attachment; filename="' . $name . '"');
					header('Content-Length: '.strlen($img->data));
					header("Cache-Control: maxage=1");
					header("Pragma: public");
					header("Content-Transfer-Encoding: binary");

					echo $img->data;

					flush();
					ob_flush();
					unset($img->data);
					unset($img);
					$dispatcher->trigger( 'onAfterDownloadFile', array( &$filename, &$file) );
					exit;
				}
			}
		}

		if($is_resume) {
			header('Accept-Ranges: bytes');

			if($seek_start > 0 || $seek_end < ($size - 1)) {
				header('HTTP/1.1 206 Partial Content');
				header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);
			}
		}

		header("Expires: 0");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $name . '"');
		header('Content-Length: '.($seek_end - $seek_start + 1));
		header("Cache-Control: maxage=1");
		header("Pragma: public");
		header("Content-Transfer-Encoding: binary");

		$config = hikashop_config();
		if($config->get('deactivate_buffering_and_compression',0)){
			ini_set('output_buffering', 0);
			ini_set('zlib.output_compression', 0);
			while(ob_get_level())
				@ob_end_clean();
		}

		$fp = fopen($filename, 'rb');
		fseek($fp, $seek_start);
		if(!ini_get('safe_mode')){
			set_time_limit(0);
		}

		while(!feof($fp)) {
			print(fread($fp, 8192));
			@ob_flush();
			flush();
		}

		fclose($fp);
		$dispatcher->trigger('onAfterDownloadFile', array( &$filename, &$file));
		exit;
	}

	function downloadFieldFile($name,$field_table,$field_namekey,$options=array()){
		$app = JFactory::getApplication();
		if(!$app->isAdmin()) {
			$found = false;
			switch($field_table){
				case 'entry':
					$entriesData = $app->getUserState(HIKASHOP_COMPONENT.'.entries_fields');
					if(!empty($entriesData)){
						foreach($entriesData as $entryData){
							if(@$entryData->$field_namekey==$name){
								$found = true;
							}
						}
					}
					break;
				case 'order':
					$orderData = $app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields');
					if(@$orderData->$field_namekey==$name){
						$found = true;
					}
					break;
				case 'item':
					$class = hikashop_get('class.cart');
					$products = $class->get();
					if(!empty($products)){
						foreach( $products as $product ){
							if(@$product->$field_namekey==$name){
								$found = true;
							}
						}
					}
					$itemsData = $app->getUserState(HIKASHOP_COMPONENT.'.items_fields');
					if(!empty($itemsData)){
						foreach($itemsData as $itemData) {
							if(@$itemData->$field_namekey == $name) {
								$found = true;
							}
						}
					}
					break;
				default:
					if(substr($field_table, 0, 4) == 'plg.') {
						$externalValues = array();
						JPluginHelper::importPlugin('hikashop');
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger('onTableFieldsLoad', array( &$externalValues ) );
						$found = false;
						foreach($externalValues as $external) {
							if($external->value == $field_table) {
								$found = true;
								break;
							}
						}
						if($found) {
							$elemsData = $app->getUserState(HIKASHOP_COMPONENT.'.plg_fields.' . substr($field_table, 4));
							if(!empty($elemsData)){
								foreach($elemsData as $elemData) {
									if(@$elemData->$field_namekey == $name) {
										$found = true;
									}
								}
							}
						}
					}
					break;
			}

			if(!$found) {
				JPluginHelper::importPlugin('hikashop');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onFieldFileDownload', array( &$found, $name, $field_table, $field_namekey, $options ) );
			}

			if(!$found) {
				if(!HIKASHOP_J25)
					$escaped_field_namekey = $this->database->nameQuote($field_namekey);
				else
					$escaped_field_namekey = $this->database->quoteName($field_namekey);

				switch($field_table){
					case 'order':
						$this->database->setQuery('SELECT order_id FROM '.hikashop_table('order').' WHERE order_user_id='.(int)hikashop_loadUser().' AND '.$escaped_field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'item':
						$this->database->setQuery('SELECT b.order_product_id FROM '.hikashop_table('order').' AS a LEFT JOIN '.hikashop_table('order_product').' AS b ON a.order_id=b.order_id WHERE a.order_user_id='.(int)hikashop_loadUser(). ' AND b.'.$escaped_field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'entry':
						$this->database->setQuery('SELECT b.entry_id FROM '.hikashop_table('order').' AS a LEFT JOIN '.hikashop_table('entry').' AS b ON a.order_id=b.order_id WHERE a.order_user_id='.(int)hikashop_loadUser().' AND b.'.$escaped_field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'user':
						$this->database->setQuery('SELECT user_id FROM '.hikashop_table('user').' WHERE user_id='.(int)hikashop_loadUser().' AND '.$escaped_field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'address':
						$this->database->setQuery('SELECT address_id FROM '.hikashop_table('address').' WHERE address_user_id='.(int)hikashop_loadUser().' AND '.$escaped_field_namekey.' = '.$this->database->Quote($name));
						break;
					case 'product':
						$filters = array($escaped_field_namekey.' = '.$this->database->Quote($name),'product_published=1');
						hikashop_addACLFilters($filters,'product_access');
						$this->database->setQuery('SELECT product_id FROM '.hikashop_table('product').' WHERE '.implode(' AND ',$filters));
						break;
					case 'category':
						$filters = array($escaped_field_namekey.' = '.$this->database->Quote($name),'category_published=1');
						hikashop_addACLFilters($filters,'category_access');
						$this->database->setQuery('SELECT category_id FROM '.hikashop_table('category').' WHERE '.implode(' AND ',$filters));
						break;
					default:
						return false;
				}
				$result = $this->database->loadResult();
				if($result){
					$found = true;
				}
			}

			if(!$found) {
				$query = 'SELECT field_default FROM ' . hikashop_table('field') .
					' WHERE field_table = ' . $this->database->Quote($field_table) . ' AND field_namekey = ' . $this->database->Quote($field_namekey) .
					' AND field_published = 1 AND field_type IN (\'image\',\'ajaximage\')';
				$this->database->setQuery($query);
				$default_value = $this->database->loadResult();
				if($default_value == $name)
					$found = true;
			}

			if(!$found)
				return false;
		}
		$path = $this->getPath('file');

		if(file_exists($path . $name)) {
			$file = new stdClass();
			$file->file_path = $name;
			$this->sendFile($file, true, $path, $options);
		}
		return false;
	}

	function getPath($type, $subPath = '') {
		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		$config =& hikashop_config();
		if($type=='file') {
			$uploadFolder = $config->get('uploadsecurefolder');
		} else {
			$uploadFolder = $config->get('uploadfolder');
		}

		$uploadFolder = rtrim(JPath::clean(html_entity_decode($uploadFolder)), DS.' ').DS;
		if((!preg_match('#^([A-Z]:)?/.*#', $uploadFolder)) && ($uploadFolder[0] != '/' || !is_dir($uploadFolder))) {
			$uploadFolder = rtrim(JPath::clean(HIKASHOP_ROOT.DS.trim($uploadFolder, DS.' ').DS), DS.' ') . DS;
		}

		if($type == 'file') {
			$realpath = realpath($uploadFolder);
			if(!empty($realpath))
				$uploadFolder = rtrim($realpath, DS.' ').DS;
		}

		if(!empty($subPath)) {
			$subPath = trim($subPath, DS.' ').DS;
		}

		$this->checkFolder($uploadFolder.$subPath);
		if($type != 'file') {
			$this->checkFolder($uploadFolder.$subPath.'thumbnails'.DS);
		}
		return $uploadFolder;
	}

	function checkFolder($uploadPath) {
		if(strpos($uploadPath,'..') !== false) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('The folder path "'.strip_tags($uploadPath).'" contains &quot;..&quot; in it and this is not allowed');
			return false;
		}
		if(!is_dir($uploadPath)) {
			jimport('joomla.filesystem.folder');
			JFolder::create($uploadPath);
		}
		if(!is_writable($uploadPath)) {
			@chmod($uploadPath,'0755');
			if(!is_writable($uploadPath)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('WRITABLE_FOLDER',$uploadPath), 'notice');
				return false;
			}
		}
		return true;
	}
}
