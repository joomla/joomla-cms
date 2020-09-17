<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Controller;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Controller\Mixin\CustomACL;
use Akeeba\Backup\Admin\Controller\Mixin\PredefinedTaskList;
use Akeeba\Backup\Admin\Helper\Utils;
use Akeeba\Engine\Platform;
use FOF30\Container\Container;
use FOF30\Controller\Controller;
use Joomla\CMS\Uri\Uri;

/**
 * Backup page controller
 */
class Backup extends Controller
{
	use CustomACL, PredefinedTaskList;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->setPredefinedTaskList([
			'main',
			'ajax'
		]);
	}

	/**
	 * Default task; shows the initial page where the user selects a profile and enters description and comment
	 */
	protected function onBeforeMain()
	{
		// Did the user ask to switch the active profile?
		$newProfile = $this->input->get('profileid', -10, 'int');
		$autostart  = $this->input->get('autostart', 0, 'int');

		if (is_numeric($newProfile) && ($newProfile > 0))
		{
			/**
			 * We have to remove CSRF protection due to the way the Joomla administrator menu manager works. Menu item
			 * options are passed as URL parameters. However, we cannot pass dynamic parameters (like the token). This
			 * means that a user can create a menu item with a specific backup profile ID. Normally this would cause a
			 * 403 which is frustrating to the user because they might want to give their client the option to run a
			 * backup with a specific profile AND let them enter a description and comment. Therefore we have to remove
			 * the CSRF protection.
			 *
			 * NB! We do understand the potential risk involved. Between Joomla's AMATEURISH implementation of custom
			 * administrator menus and user demands for features we have to (have these very vocal users and everyone
			 * else) assume that (actually really small) risk.
			 */
			// $this->csrfProtection();
			$this->container->platform->setSessionVar('profile', $newProfile, 'akeeba');

			/**
			 * DO NOT REMOVE!
			 *
			 * The Model will only try to load the configuration after nuking the factory. This causes Profile 1 to be
			 * loaded first. Then it figures out it needs to load a different profile and it does – but the protected keys
			 * are NOT replaced, meaning that certain configuration parameters are not replaced. Most notably, the chain.
			 * This causes backups to behave weirdly. So, DON'T REMOVE THIS UNLESS WE REFACTOR THE MODEL.
			 */
			Platform::getInstance()->load_configuration($newProfile);
		}

		// Deactivate the menus
		\Joomla\CMS\Factory::getApplication()->input->set('hidemainmenu', 1);

		/** @var \Akeeba\Backup\Admin\Model\Backup $model */
		$model = $this->getModel();

		// Sanitize the return URL
		$returnUrl = $this->input->get('returnurl', '', 'raw');
		$returnUrl = Utils::safeDecodeReturnUrl($returnUrl);

		// Push data to the model
		$model->setState('profile', $this->input->get('profileid', -10, 'int'));
		$model->setState('description', $this->input->get('description', '', 'string', 2));
		$model->setState('comment', $this->input->get('comment', '', 'html', 2));
		$model->setState('ajax', $this->input->get('ajax', '', 'cmd'));
		$model->setState('autostart', $autostart);
		$model->setState('jpskey', $this->input->get('jpskey', '', 'raw', 2));
		$model->setState('angiekey', $this->input->get('angiekey', '', 'raw', 2));
		$model->setState('returnurl', $returnUrl);
		$model->setState('backupid', $this->input->get('backupid', null, 'cmd'));
	}

	/**
	 * This task handles the AJAX requests
	 */
	public function ajax()
	{
		/** @var \Akeeba\Backup\Admin\Model\Backup $model */
		$model = $this->getModel();

		// Push all necessary information to the model's state
		$model->setState('profile', $this->input->get('profileid', -10, 'int'));
		$model->setState('ajax', $this->input->get('ajax', '', 'cmd'));
		$model->setState('description', $this->input->get('description', '', 'string'));
		$model->setState('comment', $this->input->get('comment', '', 'html', 2));
		$model->setState('jpskey', $this->input->get('jpskey', '', 'raw', 2));
		$model->setState('angiekey', $this->input->get('angiekey', '', 'raw', 2));
		$model->setState('backupid', $this->input->get('backupid', null, 'cmd'));
		$model->setState('tag', $this->input->get('tag', 'backend', 'cmd'));
		$model->setState('errorMessage', $this->input->getString('errorMessage', ''));

		// System Restore Point backup state variables (obsolete)
		$model->setState('type', strtolower($this->input->get('type', '', 'cmd')));
		$model->setState('name', strtolower($this->input->get('name', '', 'cmd')));
		$model->setState('group', strtolower($this->input->get('group', '', 'cmd')));
		$model->setState('customdirs', $this->input->get('customdirs', array(), 'array', 2));
		$model->setState('customfiles', $this->input->get('customfiles', array(), 'array', 2));
		$model->setState('extraprefixes', $this->input->get('extraprefixes', array(), 'array', 2));
		$model->setState('customtables', $this->input->get('customtables', array(), 'array', 2));
		$model->setState('skiptables', $this->input->get('skiptables', array(), 'array', 2));
		$model->setState('langfiles', $this->input->get('langfiles', array(), 'array', 2));
		$model->setState('xmlname', $this->input->getString('xmlname', ''));

		// Set up the tag
		define('AKEEBA_BACKUP_ORIGIN', $this->input->get('tag', 'backend', 'cmd'));

		// Run the backup step
		$ret_array = $model->runBackup();

		// We use this nasty trick to avoid broken 3PD plugins from barfing all over our output
		@ob_end_clean();
		header('Content-type: text/plain');
		header('Connection: close');
		echo '###' . json_encode($ret_array) . '###';
		flush();
		$this->container->platform->closeApplication();
	}
}
