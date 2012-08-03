<?php
/**
 * Plugin Helper File
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * ...
 */
class plgSystemNNFrameworkHelper
{
	function __construct()
	{
		$app = JFactory::getApplication();

		$url = JRequest::getVar('url');
		$func = new plgSystemNNFrameworkHelperFunctions;

		if ($url) {
			echo $func->getByUrl($url);
			die;
		}

		$file = JRequest::getVar('file');

		// only allow files that have .inc.php in the file name
		if (!$file || (strpos($file, '.inc.php') === false)) {
			die();
		}

		$folder = JRequest::getVar('folder');
		if ($folder) {
			$file = implode('/', explode('.', $folder)).'/'.$file;
		}

		$allowed = array(
			'administrator/components/com_dbreplacer/dbreplacer.inc.php',
			'administrator/components/com_nonumbermanager/details.inc.php',
			'administrator/components/com_rereplacer/images/image.inc.php',
			'administrator/modules/mod_addtomenu/addtomenu/addtomenu.inc.php',
			'plugins/editors-xtd/articlesanywhere/articlesanywhere.inc.php',
			'plugins/editors-xtd/contenttemplater/contenttemplater.inc.php',
			'plugins/editors-xtd/modulesanywhere/modulesanywhere.inc.php',
			'plugins/editors-xtd/snippets/snippets.inc.php',
			'plugins/editors-xtd/sourcerer/sourcerer.inc.php'
		);

		if (!$file || (in_array($file, $allowed) === false)) {
			die();
		}

		jimport('joomla.filesystem.file');

		if ($app->isSite() && !JRequest::getCmd('usetemplate')) {
			$app->setTemplate('../administrator/templates/bluestork');
		}
		$_REQUEST['tmpl'] = 'component';
		JRequest::setVar('option', '1');

		$app->set('_messageQueue', '');

		$file = JPATH_SITE.'/'.$file;

		$html = '';
		if (JFile::exists($file)) {
			ob_start();
			include $file;
			$html = ob_get_contents();
			ob_end_clean();
		}

		$document = JFactory::getDocument();
		$document->setBuffer($html, 'component');
		$document->addStyleSheet(JURI::root(true).'/administrator/templates/bluestork/css/template.css');
		$document->addScript(JURI::root(true).'/includes/js/joomla.javascript.js');

		$app->render();

		$html = JResponse::toString($app->getCfg('gzip'));
		$html = preg_replace('#\s*<'.'link [^>]*href="[^"]*templates/system/[^"]*\.css[^"]*"[^>]* />#s', '', $html);

		echo $html;

		die;
	}
}

class plgSystemNNFrameworkHelperFunctions
{
	var $_version = '12.6.4';

	function getByUrl($url, $options = array())
	{
		// only allow url calls from administrator
		$app = JFactory::getApplication();
		if (!$app->isAdmin()) {
			die();
		}

		// only allow when logged in
		$user = JFactory::getUser();
		if (!$user->id) {
			die();
		}

		if (substr($url, 0, 4) != 'http') {
			$url = 'http://'.$url;
		}

		// only allow url calls to nonumber.nl domain
		if (!(preg_match('#^https?://([^/]+\.)?nonumber\.nl/#', $url))) {
			die();
		}

		// only allow url calls to certain files
		if (
			strpos($url, 'download.nonumber.nl/extensions.php') === false
			&& strpos($url, 'www.nonumber.nl/ext/extension.php') === false
		) {
			die();
		}

		$html = '';
		if (function_exists('curl_init') && function_exists('curl_exec')) {
			$html = $this->curl($url);
		} else {
			$file = @fopen($url, 'r');
			if ($file) {
				$html = array();
				while (!feof($file)) {
					$html[] = fgets($file, 1024);
				}
				$html = implode('', $html);
			}
		}

		return $html;
	}

	function curl($url)
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'NoNumber/'.$this->_version);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);

		$config = JComponentHelper::getParams('com_nonumbermanager');
		if ($config && $config->get('use_proxy', 0) && $config->get('proxy_host')) {
			curl_setopt($ch, CURLOPT_PROXY, $config->get('proxy_host').':'.$config->get('proxy_port'));
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $config->get('proxy_login').':'.$config->get('proxy_password'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		}

		//follow on location problems
		if (ini_get('open_basedir') == '' && ini_get('safe_mode') != '1' && ini_get('safe_mode') != 'On') {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$html = curl_exec($ch);
		} else {
			$html = $this->curl_redir_exec($ch);
		}
		curl_close($ch);
		return $html;
	}

	function curl_redir_exec($ch)
	{
		static $curl_loops = 0;
		static $curl_max_loops = 20;

		if ($curl_loops++ >= $curl_max_loops) {
			$curl_loops = 0;
			return false;
		}

		curl_setopt($ch, CURLOPT_HEADER, true);
		$data = curl_exec($ch);

		list($header, $data) = explode("\n\n", str_replace("\r", '', $data), 2);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code == 301 || $http_code == 302) {
			$matches = array();
			preg_match('/Location:(.*?)\n/', $header, $matches);
			$url = @parse_url(trim(array_pop($matches)));
			if (!$url) {
				//couldn't process the url to redirect to
				$curl_loops = 0;
				return $data;
			}
			$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
			if (!$url['scheme']) {
				$url['scheme'] = $last_url['scheme'];
			}
			if (!$url['host']) {
				$url['host'] = $last_url['host'];
			}
			if (!$url['path']) {
				$url['path'] = $last_url['path'];
			}
			$new_url = $url['scheme'].'://'.$url['host'].$url['path'].($url['query'] ? '?'.$url['query'] : '');
			curl_setopt($ch, CURLOPT_URL, $new_url);
			return $this->curl_redir_exec($ch);
		} else {
			$curl_loops = 0;
			return $data;
		}
	}
}