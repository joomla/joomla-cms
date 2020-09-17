<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Log;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Model\Log;
use Akeeba\Engine\Platform;
use FOF30\View\DataView\Html as BaseView;

/**
 * View controller for the Log Viewer page
 */
class Raw extends BaseView
{
	/**
	 * Currently selected log file tag
	 *
	 * @var  string
	 */
	public $tag;

	/**
	 * Renders the actual log content, for use in the IFRAME
	 *
	 * @return  void
	 */
	public function onBeforeIframe()
	{
		/** @var Log $model */
		$model = $this->getModel();
		$tag   = $model->getState('tag', '', 'string');

		if (empty($tag))
		{
			$tag = null;
		}

		$this->tag = $tag;

		$this->setLayout('raw');
	}
}
