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
class SofortLibLogger {

	public $fp = null;

	public $maxFilesize = 1048576;


	public function SofortLibLogger() {
	}


	public function log($message, $uri) {
		if ($this->logRotate($uri)) {
			$this->fp = fopen($uri, 'w');
			fclose($this->fp);
		}

		$this->fp = fopen($uri, 'a');
		fwrite($this->fp, '['.date('Y-m-d H:i:s').'] '.$message."\n");
		fclose($this->fp);
	}


	public function logRotate($uri) {
		$date = date('Y-m-d_h-i-s', time());

		if (file_exists($uri)) {
			if ($this->fp != null && filesize($uri) != false && filesize($uri) >= $this->maxFilesize) {
				$oldUri = $uri;
				$ending = $ext = pathinfo($oldUri, PATHINFO_EXTENSION);
				$newUri = dirname($oldUri).'/log_'.$date.'.'.$ending;
				rename($oldUri, $newUri);

				if (file_exists($oldUri)) {
					unlink($oldUri);
				}

				return true;
			}
		}

		return false;
	}
}
?>
