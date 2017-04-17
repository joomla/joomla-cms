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
class SofortLib_Http {

	public $headers;

	public $connectionMethod;

	public $compression;

	public $proxy;

	public $url;

	public $info;

	public $error;

	public $httpStatus = 200;

	protected $_response = '';


	public function __construct($url, $headers, $compression = false, $proxy = '') {
		$this->url = $url;
		$this->headers = $headers;
		$this->compression = $compression;
		$this->proxy = $proxy;
	}


	public function getinfo($opt = '') {
		if (!empty($opt)) {
			return $this->info[$opt];
		} else {
			return $this->info;
		}
	}


	public function post($data, $url = false, $headers = false) {
		if (function_exists('curl_init')) {
			return $this->postCurl($data, $url, $headers);
		} else {
			return $this->postSocket($data, $url, $headers);
		}
	}


	public function postCurl($data, $url = false, $headers = false) {
		$this->connectionMethod = 'cURL';

		if ($url === false) {
			$url = $this->url;
		}

		if ($headers === false) {
			$headers = $this->headers;
		}

		$headers[] = 'User-Agent: SofortLib-php/'.SOFORTLIB_VERSION.'-'.$this->connectionMethod;
		$process = curl_init($url);
		curl_setopt($process, CURLOPT_HTTPHEADER, array_merge($headers, array('Expect:')));
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_HEADER, false);

		if ($this->compression !== false) {
			curl_setopt($process, CURLOPT_ENCODING, $this->compression);
		}

		curl_setopt($process, CURLOPT_TIMEOUT, 15);

		if ($this->proxy) {
			curl_setopt($process, CURLOPT_PROXY, $this->proxy);
		}

		curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
		$return = curl_exec($process);
		$this->info = curl_getinfo($process);
		$this->error = curl_error($process);
		$this->httpStatus = $this->info['http_code'];
		$this->_response = $return;
		curl_close($process);

		if ($this->error) {
			return $this->_xmlError('00'.$this->error, $this->_response);
		}

		return $return;
	}


	public function getHttpCode() {
		switch ($this->httpStatus) {
			case(200):
				return array('code' => 200, 'message' => $this->_xmlError($this->httpStatus, 'OK'), 'response' => $this->_response);
				break;
			case(301):
			case(302):
				return array('code' => $this->httpStatus, 'message' => $this->_xmlError($this->httpStatus, 'Redirected Request'), 'response' => $this->_response);
				break;
			case(401):
				$this->error = 'Unauthorized';
				return array('code' => 401, 'message' => $this->_xmlError($this->httpStatus, 'Unauthorized'), 'response' => $this->_response);
				break;
			case(0):
			case(404):
				$this->httpStatus = 404;
				$this->error = 'URL not found '.$this->url;
				return array('code' => 404, 'message' => $this->_xmlError($this->httpStatus, 'URL not found '.$this->url), 'response' => '');
				break;
			case(500):
				$this->error = 'An error occurred';
				return array('code' => 500, 'message' => $this->_xmlError($this->httpStatus, 'An error occurred'), 'response' => $this->_response);
				break;
			default:
				return array('code' => $this->httpStatus, 'message' => $this->_xmlError($this->httpStatus, 'Something went wrong, not handled httpStatus'), 'response' => $this->_response);
				break;
		}
	}


	public function getHttpStatusCode() {
		$status = $this->getHttpCode();
		return $status['code'];
	}


	public function getHttpStatusMessage() {
		$status = $this->getHttpCode();
		return $status['message'];
	}


	public function postSocket($data, $url = false, $headers = false) {
		$this->connectionMethod = 'Socket';

		if ($url === false) {
			$url = $this->url;
		}

		if ($headers === false) {
			$headers = $this->headers;
		}

		$headers[] = 'User-Agent: SofortLib-php/'.SOFORTLIB_VERSION.'-'.$this->connectionMethod;
		$uri = parse_url($url);
		$out = 'POST '.$uri['path'].' HTTP/1.1'."\r\n";
		$out .= 'HOST: '.$uri['host']."\r\n";
		$out .= 'Connection: close'."\r\n";
		$out .= 'Content-Length: '.strlen($data)."\r\n";

		foreach ($headers as $header) {
			$out .= $header."\r\n";
		}

		$out .= "\r\n".$data;

		if (!$fp = fsockopen('ssl://'.$uri['host'], 443, $errno, $errstr, 15)) {
			$this->error = 'fsockopen() failed, enable ssl and curl: '.$errno.' '.$errstr;
			return false;
		}

		stream_set_timeout($fp, 15);
		fwrite($fp, $out);
		$return = '';

		while (!feof($fp)) {
			$return .= fgets($fp, 512);
		}

		fclose($fp);
		preg_match('#^(.+?)\r\n\r\n(.*)#ms', $return, $body);
		preg_match('#HTTP/1.*([0-9]{3}).*#i', $body[1], $status);
		$this->info['http_code'] = $status[1];
		$this->httpStatus = $status[1];
		return $body[2];
	}


	public function error($error) {
		echo '<center><div style="width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px"><b>cURL Error</b><br>'.$error.'</div></center>';
		die;
	}


	private function _xmlError($code, $message) {
		return '<errors><error><code>0'.$code.'</code><message>'.$message.'</message></error></errors>';
	}
}
?>
