<?php
/**
 * Main Plugin File
 * Does all the magic!
 *
 * @package			Cache Cleaner
 * @version			2.2.0
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2013 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Plugin that cleans cache
 */
class plgSystemCacheCleaner extends JPlugin
{
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onAfterRoute()
	{
		jimport('joomla.filesystem.file');
		if (JFile::exists(JPATH_PLUGINS.'/system/nnframework/helpers/protect.php')) {
			require_once JPATH_PLUGINS.'/system/nnframework/helpers/protect.php';
			// return if page should be protected
			if (nnProtect::isProtectedPage('cachecleaner')) {
				return;
			}
		}

		$document = JFactory::getDocument();
		$docType = $document->getType();

		// only in html
		if ($docType != 'html') {
			return;
		}

		// load the admin language file
		$lang = JFactory::getLanguage();
		if ($lang->getTag() != 'en-GB') {
			// Loads English language file as fallback (for undefined stuff in other language file)
			$lang->load('plg_'.$this->_type.'_'.$this->_name, JPATH_ADMINISTRATOR, 'en-GB');
		}
		$lang->load('plg_'.$this->_type.'_'.$this->_name, JPATH_ADMINISTRATOR, null, 1);

		$app = JFactory::getApplication();

		// return if NoNumber Framework plugin is not installed
		if (!JFile::exists(JPATH_PLUGINS.'/system/nnframework/nnframework.php')) {
			if ($app->isAdmin() && JRequest::getCmd('option') != 'com_login') {
				$msg = JText::_('CC_NONUMBER_FRAMEWORK_NOT_INSTALLED');
				$msg .= ' '.JText::sprintf('CC_EXTENSION_CAN_NOT_FUNCTION', JText::_('CACHE_CLEANER'));
				$mq = $app->getMessageQueue();
				foreach ($mq as $m) {
					if ($m['message'] == $msg) {
						$msg = '';
						break;
					}
				}
				if ($msg) {
					$app->enqueueMessage($msg, 'error');
				}
			}
			return;
		}

		// return if NoNumber Framework plugin is not enabled
		$nnep = JPluginHelper::getPlugin('system', 'nnframework');
		if (!isset($nnep->name)) {
			if ($app->isAdmin() && JRequest::getCmd('option') != 'com_login') {
				$msg = JText::_('CC_NONUMBER_FRAMEWORK_NOT_ENABLED');
				$msg .= ' '.JText::sprintf('CC_EXTENSION_MAY_NOT_FUNCTION', JText::_('CACHE_CLEANER'));
				$mq = $app->getMessageQueue();
				foreach ($mq as $m) {
					if ($m['message'] == $msg) {
						$msg = '';
						break;
					}
				}
				if ($msg) {
					$app->enqueueMessage($msg, 'notice');
				}
			}
			return;
		}

		// Load plugin parameters
		require_once JPATH_PLUGINS.'/system/nnframework/helpers/parameters.php';
		$parameters = NNParameters::getInstance();
		$params = $parameters->getPluginParams($this->_name, $this->_type, $this->params);

		$clean = 0;
		$show_msg = 0;

		if (!$clean) {
			$cleancache = JRequest::getVar('cleancache');
			if ($cleancache != '') {
				if ($app->isSite() && $cleancache != $params->frontend_secret) {
					return;
				}
				$clean = 'clean';
				$show_msg = 1;
			}
		}

		if (!$clean) {
			$task = JRequest::getVar('task');
			if ($task) {
				$task = explode('.', $task, 2);
				$task = isset($task['1']) ? $task['1'] : $task['0'];
				if (strpos($task, 'save') === 0) {
					$task = 'save';
				}
				$tasks = array_diff(array_map('trim', explode(',', $params->auto_save_tasks)), array(''));
				if (!empty($tasks) && in_array($task, $tasks)) {
					if ($app->isAdmin() && $params->auto_save_admin) {
						$clean = 'save';
						$show_msg = $params->auto_save_admin_msg;
					} else if ($app->isSite() && $params->auto_save_front) {
						$clean = 'save';
						$show_msg = $params->auto_save_front_msg;
					}
				}
			}
		}


		if (!$clean) {
			return;
		}

		// Include the Helper
		require_once JPATH_PLUGINS.'/'.$this->_type.'/'.$this->_name.'/helper.php';
		$class = get_class($this).'Helper';
		$this->helper = new $class ($params, $clean, $show_msg, $params->show_size);
	}
}