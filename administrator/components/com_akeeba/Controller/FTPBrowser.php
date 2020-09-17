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
use FOF30\Controller\Controller;

/**
 * Controller for the FTP folder browser
 */
class FTPBrowser extends Controller
{
	use CustomACL;

	protected function onBeforeMain()
	{
		/** @var \Akeeba\Backup\Admin\Model\FTPBrowser $model */
		$model = $this->getModel();

		// Grab the data and push them to the model
		$model->host      = $this->input->get('host', '', 'string');
		$model->port      = $this->input->get('port', 21, 'int');
		$model->passive   = $this->input->get('passive', 1, 'int');
		$model->ssl       = $this->input->get('ssl', 0, 'int');
		$model->username  = $this->input->get('username', '', 'none', 2);
		$model->password  = $this->input->get('password', '', 'none', 2);
		$model->directory = $this->input->get('directory', '', 'none', 2);

		if (empty($model->port))
		{
			$model->port = $model->ssl ? 990 : 21;
		}

		$ret = $model->doBrowse();

		@ob_end_clean();
		echo '###' . json_encode($ret) . '###';
		flush();
		$this->container->platform->closeApplication();
	}
}
