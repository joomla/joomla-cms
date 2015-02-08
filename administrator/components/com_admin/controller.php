<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Admin Controller
 *
 * @since  1.6
 */
class AdminController extends JControllerLegacy
{
	/**
	 * Creating a text file with all relevant system setting
	 */
	public function download()
	{
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));
		/** @var AdminModelSysInfo $model */
		$model = $this->getModel('sysinfo');
		$settingsToLoad = array('PhpSettings', 'Config', 'Info');
		foreach($settingsToLoad as $section) {
			$settings[$section] = $this->loadSetting($section, $model);
		}
		$directories = $this->loadSetting('Directory', $model);
		if(count($directories)) {
			foreach($directories as $directory => $data) {
				$settings['Directory'][$directory] = $directory.($data['writable'] ? ' is writable' : 'is not writable');
			}
		}
		$phpInfo = $this->parsePhpInfo($model->getPHPInfo());
		// remove sensitive information from the phpinfo output
		$remove = array('HTTP_HOST','Server Administrator', 'Server Root', 'HTTP_ORIGIN', 'HTTP_REFERER', 'HTTP_COOKIE', 'SERVER_NAME', 'SERVER_ADDR', 'REMOTE_ADDR', 'DOCUMENT_ROOT', 'CONTEXT_DOCUMENT_ROOT', 'SERVER_ADMIN', 'SCRIPT_FILENAME', 'HTTP Request', 'Host', 'Referer');
		foreach($phpInfo as $section => $values) {
			foreach ( $values as $name => $setting ) {
				if(in_array($name, $remove)) {
					$setting = strlen($name) ? 'set' : 'not set';
				}
				$settings['PhpInfo'][$section][$name] = $setting;
			}
		}
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="systeminfo-' . microtime( true) . '.txt"');
		header('Cache-Control: must-revalidate');
		$this->renderFile($settings);
		JFactory::getApplication()->close();
	}


	/**
	 * Parses array to generate plain text output
	 * @param array $settings
	 * @param int $level
	 */
	protected function renderFile( $settings, $level = -1 )
	{
		if(count($settings)) {
			$margin = null;
			foreach($settings as $name => $value) {
				if( $level > 0 ) {
					$margin = str_repeat("\t", $level);
				}
				if( is_array($value)) {
					if($name=='Directive') {
						continue;
					}
					echo "\n";
					echo $margin."=============\n";
					echo $margin.$name."\n";
					echo $margin."=============\n";
					$this->renderFile($value,$level+1);
				}
				else {
					if(is_bool($value)) {
						$value = $value ? 'true' : 'false';
					}
					if(is_int($name) && ($name == 0 || $name == 1)) {
						$name = ($name==0 ? 'Local Value' : 'Master Value');
					}
					echo $margin.$name.': '.$value."\n";
				}
			}
		}
	}

	/**
	 * Parse phpinfo output into an array
	 * Source https://gist.github.com/sbmzhcn/6255314
	 * */
	protected function parsePhpInfo($string)
	{
		$string = strip_tags($string, '<h2><th><td>');
	    $string = preg_replace('/<th[^>]*>([^<]+)<\/th>/', '<info>\1</info>', $string);
	    $string = preg_replace('/<td[^>]*>([^<]+)<\/td>/', '<info>\1</info>', $string);
	    $t = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
	    $r = array(); $count = count($t);
	    $p1 = '<info>([^<]+)<\/info>';
	    $p2 = '/'.$p1.'\s*'.$p1.'\s*'.$p1.'/';
	    $p3 = '/'.$p1.'\s*'.$p1.'/';
	    for ($i = 1; $i < $count; $i++) {
	        if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $t[$i], $matchs)) {
	            $name = trim($matchs[1]);
	            $vals = explode("\n", $t[$i + 1]);
	            foreach ($vals AS $val) {
	                if (preg_match($p2, $val, $matchs)) { // 3cols
	                    $r[$name][trim($matchs[1])] = array(trim($matchs[2]), trim($matchs[3]));
	                } elseif (preg_match($p3, $val, $matchs)) { // 2cols
	                    $r[$name][trim($matchs[1])] = trim($matchs[2]);
	                }
	            }
	        }
	    }
		return $r;
	}

	/**
	 * Load particular section
	 */
	protected function loadSetting($section, $model)
	{
		/** security sensitive information to be removed from the output */
		static $remove = array('dbprefix', 'open_basedir', 'session.save_path', 'mailfrom', 'fromname', 'smtphost', 'log_path', 'tmp_path', 'proxy_host', 'proxy_user', 'proxy_pass', 'memcache_server_host', 'memcached_server_host', 'session_memcache_server_host', 'session_memcached_server_host');
		$settings = array();
		$method = 'get'.$section;
		$sectionsValues = $model->$method();
		if(count($sectionsValues)) {
			foreach($sectionsValues as $setting => $value) {
				if(in_array($setting, $remove)) {
					$value = strlen($value) ? 'set' : 'not set';
				}
				$settings[$setting] = $value;
			}
		}
		return $settings;
	}
}
