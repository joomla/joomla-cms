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
use FOF30\Container\Container;
use FOF30\Controller\Controller;

/**
 * Database Filters controller
 */
class DatabaseFilters extends Controller
{
	use CustomACL, PredefinedTaskList;

	/**
	 * Should I decode the "action" JSON data as an associative array? Default is false (meaning we're decoding as an
	 * stdClass object).
	 *
	 * @var bool
	 */
	protected $decodeJsonAsArray = false;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->setPredefinedTaskList(['main', 'ajax']);
	}

	/**
	 * Handles the "main" task, which displays a folder and file list
	 *
	 */
	public function main()
	{
		$task = $this->input->get('task', 'normal', 'cmd');

		/** @var \Akeeba\Backup\Admin\Model\DatabaseFilters $model */
		$model = $this->getModel();
		$model->setState('browse_task', $task);

		$this->display(false, false);
	}

	/**
	 * AJAX proxy
	 */
	public function ajax()
	{
		// Parse the JSON data and reset the action query param to the resulting array
		$action_json = $this->input->get('action', '', 'none', 2);
		$action      = json_decode($action_json, $this->decodeJsonAsArray);

		/** @var \Akeeba\Backup\Admin\Model\DatabaseFilters $model */
		$model = $this->getModel();

		$model->setState('action', $action);

		$ret = $model->doAjax();

		@ob_end_clean();
		echo '###' . json_encode($ret) . '###';
		flush();

		$this->container->platform->closeApplication();
	}
}
