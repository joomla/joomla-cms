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
jimport('joomla.plugin.plugin');
class plgSystemHikashoppayment extends JPlugin {

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		if(isset($this->params))
			return;

		$plugin = JPluginHelper::getPlugin('system', 'hikashoppayment');
		if(version_compare(JVERSION,'2.5','<')) {
			jimport('joomla.html.parameter');
			$this->params = new JParameter(@$plugin->params);
		} else {
			$this->params = new JRegistry(@$plugin->params);
		}
	}

	public function afterInitialise() {
		return $this->onAfterInitialise();
	}

	public function afterRoute() {
		return $this->onAfterRoute();
	}

	public function onAfterInitialise() {
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return;

		if(!$this->params->get('after_init', 1))
			return;

		if(@$_REQUEST['option'] == 'com_hikashop' && @$_REQUEST['ctrl'] == 'checkout' && @$_REQUEST['task'] == 'notify')
			$this->processPaymentNotification();

		if(@$_REQUEST['option'] == 'com_hikashop' && @$_REQUEST['ctrl'] == 'cron')
			$this->processCronNotification();

		return;
	}

	public function onAfterRoute() {
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return;

		if($this->params->get('after_init', 1))
			return;

		if(@$_REQUEST['option'] == 'com_hikashop' && @$_REQUEST['ctrl'] == 'checkout' && @$_REQUEST['task'] == 'notify')
			$this->processPaymentNotification();

		if(@$_REQUEST['option'] == 'com_hikashop' && @$_REQUEST['ctrl'] == 'cron')
			$this->processCronNotification();

		return;
	}

	protected function processPaymentNotification() {
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hikashop'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'))
			return;

		JRequest::setVar('hikashop_payment_notification_plugin', true);

		ob_start();
		$payment = JRequest::getCmd('notif_payment', @$_REQUEST['notif_payment']);
		$data = hikashop_import('hikashoppayment', $payment);

		if(!empty($data)) {
			$trans = hikashop_get('helper.translation');
			$cleaned_statuses = $trans->getStatusTrans();
			$data = $data->onPaymentNotification($cleaned_statuses);
		}
		$dbg = ob_get_clean();

		if(!empty($dbg)) {
			$config =& hikashop_config();
			jimport('joomla.filesystem.file');
			$file = $config->get('payment_log_file','');

			$file = rtrim(JPath::clean(html_entity_decode($file)), DIRECTORY_SEPARATOR . ' ');
			if(!preg_match('#^([A-Z]:)?/.*#', $file) && (!$file[0] == '/' || !file_exists($file))) {
				$file = JPath::clean(HIKASHOP_ROOT . DIRECTORY_SEPARATOR . trim($file, DIRECTORY_SEPARATOR . ' '));
			}

			if(!empty($file) && defined('FILE_APPEND')) {
				if(!file_exists(dirname($file))) {
					jimport('joomla.filesystem.folder');
					JFolder::create(dirname($file));
				}
				file_put_contents($file,$dbg,FILE_APPEND);
			}
		}

		if(is_string($data) && !empty($data))
			echo $data;
		exit;
	}

	protected function processCronNotification() {
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hikashop'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'))
			return;

		$config =& hikashop_config();
		if($config->get('cron') == 'no') {
			hikashop_display(JText::_('CRON_DISABLED'), 'info');
			return false;
		}
		$cronHelper = hikashop_get('helper.cron');
		$cronHelper->report = true;
		$launched = $cronHelper->cron();
		if($launched)
			$cronHelper->report();
		exit;
	}
}
