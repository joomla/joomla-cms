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
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Factory;
use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * View controller for the Log Viewer page
 */
class Html extends BaseView
{
	use ProfileIdAndName;

	/**
	 * Big log file threshold: 2Mb
	 */
	const bigLogSize = 2097152;
	/**
	 * JHtml list of available log files
	 *
	 * @var  array
	 */
	public $logs = [];
	/**
	 * Currently selected log file tag
	 *
	 * @var  string
	 */
	public $tag;
	/**
	 * Is the select log too big for being
	 *
	 * @var bool
	 */
	public $logTooBig = false;
	/**
	 * Size of the log file
	 *
	 * @var int
	 */
	public $logSize = 0;

	/**
	 * The main page of the log viewer. It allows you to select a profile to display. When you do it displays the IFRAME
	 * with the actual log content and the button to download the raw log file.
	 *
	 * @return  void
	 */
	public function onBeforeMain()
	{
		// Load the view-specific Javascript
		$this->container->template->addJS('media://com_akeeba/js/Log.min.js', true, false, $this->container->mediaVersion);

		if (version_compare(JVERSION, '3.999.999', 'lt'))
		{
			HTMLHelper::_('formbehavior.chosen');
		}

		// Get a list of log names
		/** @var Log $model */
		$model      = $this->getModel();
		$this->logs = $model->getLogList();

		$tag = $model->getState('tag', '', 'string');

		if (empty($tag))
		{
			$tag = null;
		}

		$this->tag = $tag;

		// Let's check if the file is too big to display
		if ($this->tag)
		{
			$logFile = Factory::getLog()->getLogFilename($this->tag);

			if (!@is_file($logFile) && @file_exists(substr($logFile, 0, -4)))
			{
				/**
				 * Transitional period: the log file akeeba.tag.log.php may not exist but the akeeba.tag.log does. This
				 * addresses this transition.
				 */
				$logFile = substr($logFile, 0, -4);
			}

			if (@file_exists($logFile))
			{
				$this->logSize   = filesize($logFile);
				$this->logTooBig = ($this->logSize >= self::bigLogSize);
			}
		}

		if ($this->logTooBig)
		{
			$src = 'index.php?option=com_akeeba&view=Log&task=inlineRaw&&tag=' . urlencode($this->tag) . '&tmpl=component';
			$this->container->platform->addScriptOptions('akeeba.Log.iFrameSrc', $src);
		}

		$this->getProfileIdAndName();
	}
}
