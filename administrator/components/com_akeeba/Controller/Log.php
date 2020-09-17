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
use Akeeba\Backup\Admin\Model\Log as LogModel;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\Controller\Controller;

class Log extends Controller
{
	use CustomACL {
		CustomACL::onBeforeExecute as onCustomACLBeforeExecute;
	}

	protected function onBeforeExecute(&$task)
	{
		$this->onCustomACLBeforeExecute($task);

		$profile_id = $this->input->getInt('profileid', null);

		if (!empty($profile_id) && is_numeric($profile_id) && ($profile_id > 0))
		{
			$this->container->platform->setSessionVar('profile', $profile_id, 'akeeba');
		}
	}

	/**
	 * Display the log page
	 *
	 * @return  void
	 */
	public function onBeforeDefault()
	{
		$tag = $this->input->get('tag', null, 'cmd');
		$latest = $this->input->get('latest', false, 'int');

		if (empty($tag))
		{
			$tag = null;
		}

		/** @var LogModel $model */
		$model = $this->getModel();

		if ($latest)
		{
			$logFiles = $model->getLogFiles();
			$tag = array_shift($logFiles);
		}

		$model->setState('tag', $tag);

		Platform::getInstance()->load_configuration(Platform::getInstance()->get_active_profile());
	}

	/**
	 * Renders the contents of the log, used inside the IFRAME of the log page
	 *
	 * @return  void
	 */
	public function iframe()
	{
		$tag = $this->input->get('tag', null, 'cmd');

		if (empty($tag))
		{
			$tag = null;
		}

		/** @var LogModel $model */
		$model = $this->getModel();
		$model->setState('tag', $tag);

		Platform::getInstance()->load_configuration(Platform::getInstance()->get_active_profile());

		$this->display();
	}

	/**
	 * Download the log file as a text file
	 *
	 * @return  void
	 */
	public function download()
	{
		Platform::getInstance()->load_configuration(Platform::getInstance()->get_active_profile());

		$tag = $this->input->get('tag', null, 'cmd');

		if (empty($tag))
		{
			$tag = null;
		}

		$asAttachment = $this->input->getBool('attachment', true);

		@ob_end_clean(); // In case some braindead plugin spits its own HTML
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Content-Description: File Transfer");
		header('Content-Type: text/plain');

		if ($asAttachment)
		{
			header('Content-Disposition: attachment; filename="Akeeba Backup Debug Log.txt"');
		}

		/** @var LogModel $model */
		$model = $this->getModel();
		$model->setState('tag', $tag);
		$model->echoRawLog();

		flush();
		$this->container->platform->closeApplication();
	}

	public function inlineRaw()
	{
		Platform::getInstance()->load_configuration(Platform::getInstance()->get_active_profile());

		$tag = $this->input->get('tag', null, 'cmd');

		if (empty($tag))
		{
			$tag = null;
		}

		/** @var LogModel $model */
		$model = $this->getModel();
		$model->setState('tag', $tag);
		echo "<pre>";
		$model->echoRawLog();
		echo "</pre>";
	}
}
